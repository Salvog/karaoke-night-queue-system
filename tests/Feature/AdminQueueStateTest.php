<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\EventNight;
use App\Models\Participant;
use App\Models\PlaybackState;
use App\Models\Song;
use App\Models\SongRequest;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminQueueStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_queue_state_endpoint_requires_authentication(): void
    {
        $eventNight = $this->createEventNight();

        $response = $this->get("/admin/events/{$eventNight->id}/queue/state");

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_queue_state_endpoint_auto_advances_and_returns_snapshot(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-01-01 20:00:00'));

        try {
            $admin = $this->createAdminUser();
            $eventNight = $this->createEventNight();
            [$first, $second] = $this->seedTwoSongsQueue($eventNight);

            PlaybackState::create([
                'event_night_id' => $eventNight->id,
                'current_request_id' => $first->id,
                'state' => PlaybackState::STATE_PLAYING,
                'started_at' => now()->subSeconds(190),
                'expected_end_at' => now()->subSecond(),
            ]);

            $response = $this->actingAs($admin, 'admin')
                ->getJson("/admin/events/{$eventNight->id}/queue/state");

            $response->assertOk();
            $response->assertJsonPath('playback.state', PlaybackState::STATE_PLAYING);
            $response->assertJsonPath('playback.current_song_title', 'Song B');
            $response->assertJsonPath('queue.total', 1);
            $response->assertJsonPath('queue.upcoming.0.song_title', 'Song B');
            $response->assertJsonPath('history.0.song_title', 'Song A');

            $this->assertSame(SongRequest::STATUS_PLAYED, $first->fresh()->status);
            $this->assertSame(SongRequest::STATUS_PLAYING, $second->fresh()->status);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_admin_queue_state_endpoint_returns_frozen_progress_when_paused(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-01-01 20:00:00'));

        try {
            $admin = $this->createAdminUser();
            $eventNight = $this->createEventNight();
            [$first] = $this->seedTwoSongsQueue($eventNight);

            PlaybackState::create([
                'event_night_id' => $eventNight->id,
                'current_request_id' => $first->id,
                'state' => PlaybackState::STATE_PAUSED,
                'started_at' => now()->subSeconds(120),
                'expected_end_at' => now()->addSeconds(60),
                'paused_at' => now()->subSeconds(30),
            ]);

            $response = $this->actingAs($admin, 'admin')
                ->getJson("/admin/events/{$eventNight->id}/queue/state");

            $response->assertOk();
            $response->assertJsonPath('playback.state', PlaybackState::STATE_PAUSED);
            $response->assertJsonPath('playback.progress.duration_seconds', 180);
            $response->assertJsonPath('playback.progress.elapsed_seconds', 90);
            $response->assertJsonPath('playback.progress.remaining_seconds', 90);
            $response->assertJsonPath('playback.progress.percent', 50);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createAdminUser(): AdminUser
    {
        return AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin-state@example.com',
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
            'code' => 'QSTATE1',
            'break_seconds' => 10,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);
    }

    /**
     * @return array{0: SongRequest, 1: SongRequest}
     */
    private function seedTwoSongsQueue(EventNight $eventNight): array
    {
        $participant = Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-admin-state',
            'join_token_hash' => hash('sha256', 'token-admin-state'),
            'display_name' => 'Cantante',
        ]);

        $songA = Song::create([
            'title' => 'Song A',
            'artist' => 'Artist',
            'duration_seconds' => 180,
        ]);

        $songB = Song::create([
            'title' => 'Song B',
            'artist' => 'Artist',
            'duration_seconds' => 180,
        ]);

        $first = SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $songA->id,
            'status' => SongRequest::STATUS_PLAYING,
            'position' => 1,
        ]);

        $second = SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $songB->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 2,
        ]);

        return [$first, $second];
    }
}
