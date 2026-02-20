<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\EventNight;
use App\Models\Participant;
use App\Models\Song;
use App\Models\SongRequest;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminQueueReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reorder_queued_songs_without_moving_playing_song(): void
    {
        $admin = $this->createAdminUser();
        $eventNight = $this->createEventNight();
        [$playing, $firstQueued, $secondQueued] = $this->seedQueue($eventNight);

        $response = $this->from("/admin/events/{$eventNight->id}/queue")
            ->actingAs($admin, 'admin')
            ->post("/admin/events/{$eventNight->id}/queue/reorder", [
                'ordered_song_request_ids' => [$secondQueued->id, $firstQueued->id],
            ]);

        $response->assertRedirect("/admin/events/{$eventNight->id}/queue");
        $this->assertSame(1, $playing->fresh()->position);
        $this->assertSame(SongRequest::STATUS_PLAYING, $playing->fresh()->status);
        $this->assertSame(2, $secondQueued->fresh()->position);
        $this->assertSame(3, $firstQueued->fresh()->position);
    }

    public function test_reorder_rejects_moving_the_playing_song(): void
    {
        $admin = $this->createAdminUser();
        $eventNight = $this->createEventNight();
        [$playing, $firstQueued, $secondQueued] = $this->seedQueue($eventNight);

        $response = $this->from("/admin/events/{$eventNight->id}/queue")
            ->actingAs($admin, 'admin')
            ->post("/admin/events/{$eventNight->id}/queue/reorder", [
                'ordered_song_request_ids' => [$playing->id, $firstQueued->id, $secondQueued->id],
            ]);

        $response->assertRedirect("/admin/events/{$eventNight->id}/queue");
        $response->assertSessionHasErrors('ordered_song_request_ids');
        $this->assertSame(1, $playing->fresh()->position);
        $this->assertSame(2, $firstQueued->fresh()->position);
        $this->assertSame(3, $secondQueued->fresh()->position);
    }

    public function test_reorder_returns_json_when_requested(): void
    {
        $admin = $this->createAdminUser();
        $eventNight = $this->createEventNight();
        [, $firstQueued, $secondQueued] = $this->seedQueue($eventNight);

        $response = $this->actingAs($admin, 'admin')
            ->postJson("/admin/events/{$eventNight->id}/queue/reorder", [
                'ordered_song_request_ids' => [$secondQueued->id, $firstQueued->id],
            ]);

        $response->assertOk();
        $response->assertJson([
            'message' => 'Ordine della coda aggiornato.',
        ]);
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
            'code' => 'QUEUE1',
            'break_seconds' => 40,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);
    }

    private function seedQueue(EventNight $eventNight): array
    {
        $participant = Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-queue-1',
            'join_token_hash' => hash('sha256', 'token-queue-1'),
            'display_name' => 'Cantante',
        ]);

        $playingSong = Song::create([
            'title' => 'Song Playing',
            'artist' => 'Artist',
            'duration_seconds' => 200,
        ]);

        $queuedSongA = Song::create([
            'title' => 'Song A',
            'artist' => 'Artist',
            'duration_seconds' => 190,
        ]);

        $queuedSongB = Song::create([
            'title' => 'Song B',
            'artist' => 'Artist',
            'duration_seconds' => 180,
        ]);

        $playing = SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $playingSong->id,
            'status' => SongRequest::STATUS_PLAYING,
            'position' => 1,
        ]);

        $firstQueued = SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $queuedSongA->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 2,
        ]);

        $secondQueued = SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $queuedSongB->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 3,
        ]);

        return [$playing, $firstQueued, $secondQueued];
    }
}
