<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\EventNight;
use App\Models\Participant;
use App\Models\PlaybackState;
use App\Models\Song;
use App\Models\SongRequest;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminQueueStateEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_state_endpoint_returns_minimal_payload(): void
    {
        $admin = $this->createAdminUser();
        $eventNight = $this->createEventNight();
        $participant = $this->createParticipant($eventNight);

        $currentSong = Song::create([
            'title' => 'Current song',
            'artist' => 'Artist',
            'duration_seconds' => 200,
        ]);

        $nextSong = Song::create([
            'title' => 'Next song',
            'artist' => 'Artist',
            'duration_seconds' => 180,
        ]);

        $currentRequest = SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $currentSong->id,
            'status' => SongRequest::STATUS_PLAYING,
            'position' => 1,
        ]);

        SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $nextSong->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 2,
        ]);

        SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $nextSong->id,
            'status' => SongRequest::STATUS_PLAYED,
            'position' => 3,
            'played_at' => now()->subMinutes(5),
        ]);

        PlaybackState::create([
            'event_night_id' => $eventNight->id,
            'current_request_id' => $currentRequest->id,
            'state' => PlaybackState::STATE_PLAYING,
            'started_at' => now()->subSeconds(20),
            'expected_end_at' => now()->addSeconds(180),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->getJson("/admin/events/{$eventNight->id}/queue/state");

        $response->assertOk();
        $response->assertJsonPath('playback.state', PlaybackState::STATE_PLAYING);
        $response->assertJsonPath('playback.current.title', 'Current song');
        $response->assertJsonPath('playback.next.title', 'Next song');
        $response->assertJsonPath('counts.current', 1);
        $response->assertJsonPath('counts.next', 1);
        $response->assertJsonPath('counts.history', 1);
        $response->assertJsonStructure([
            'playback' => ['state', 'current', 'next'],
            'counts' => ['current', 'next', 'history'],
            'timestamps' => ['server_now', 'started_at', 'expected_end_at', 'paused_at', 'updated_at'],
        ]);
    }

    public function test_state_endpoint_auto_advances_when_current_song_is_over(): void
    {
        $admin = $this->createAdminUser();
        $eventNight = $this->createEventNight();
        $participant = $this->createParticipant($eventNight);

        $firstSong = Song::create([
            'title' => 'First',
            'artist' => 'Artist',
            'duration_seconds' => 120,
        ]);

        $nextSong = Song::create([
            'title' => 'Second',
            'artist' => 'Artist',
            'duration_seconds' => 90,
        ]);

        $playingRequest = SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $firstSong->id,
            'status' => SongRequest::STATUS_PLAYING,
            'position' => 1,
        ]);

        $queuedRequest = SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $nextSong->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 2,
        ]);

        PlaybackState::create([
            'event_night_id' => $eventNight->id,
            'current_request_id' => $playingRequest->id,
            'state' => PlaybackState::STATE_PLAYING,
            'started_at' => now()->subMinutes(5),
            'expected_end_at' => now()->subSecond(),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->getJson("/admin/events/{$eventNight->id}/queue/state");

        $response->assertOk();
        $response->assertJsonPath('playback.current.id', $queuedRequest->id);

        $this->assertSame(SongRequest::STATUS_PLAYED, $playingRequest->fresh()->status);
        $this->assertSame(SongRequest::STATUS_PLAYING, $queuedRequest->fresh()->status);
        $this->assertSame($queuedRequest->id, $eventNight->fresh()->playbackState?->current_request_id);
    }

    private function createAdminUser(): AdminUser
    {
        return AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => AdminUser::ROLE_ADMIN,
        ]);
    }

    private function createEventNight(): EventNight
    {
        $venue = Venue::create([
            'name' => 'Main Hall',
            'timezone' => 'UTC',
        ]);

        return EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'QUEUESTATE',
            'break_seconds' => 40,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);
    }

    private function createParticipant(EventNight $eventNight): Participant
    {
        return Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-queue-state',
            'join_token_hash' => hash('sha256', 'token-queue-state'),
            'display_name' => 'Cantante',
        ]);
    }
}
