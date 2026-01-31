<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\EventNight;
use App\Models\Song;
use App\Models\SongRequest;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminQueueManualAddTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_manual_queue_request(): void
    {
        $admin = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => AdminUser::ROLE_ADMIN,
        ]);

        $venue = Venue::create([
            'name' => 'Main Stage',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENTMANUAL',
            'starts_at' => now(),
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
        ]);

        $song = Song::create([
            'title' => 'Song A',
            'artist' => 'Artist',
            'duration_seconds' => 180,
        ]);

        $response = $this->actingAs($admin, 'admin')->post("/admin/events/{$eventNight->id}/queue/add", [
            'display_name' => 'Giulia',
            'song_id' => $song->id,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('song_requests', [
            'event_night_id' => $eventNight->id,
            'song_id' => $song->id,
            'status' => SongRequest::STATUS_QUEUED,
        ]);
    }
}
