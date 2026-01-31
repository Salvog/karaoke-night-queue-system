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

    public function test_admin_can_add_manual_song_request(): void
    {
        $admin = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => AdminUser::ROLE_ADMIN,
        ]);

        $venue = Venue::create([
            'name' => 'Main Hall',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'MANUAL1',
            'starts_at' => now(),
            'ends_at' => now()->addHours(4),
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
        ]);

        $song = Song::create([
            'title' => 'Hit Song',
            'artist' => 'Singer',
            'duration_seconds' => 180,
        ]);

        $response = $this->actingAs($admin, 'admin')->post("/admin/events/{$eventNight->id}/queue/manual", [
            'participant_name' => 'Alice',
            'song_id' => $song->id,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseCount('song_requests', 1);

        $songRequest = SongRequest::firstOrFail();
        $this->assertSame(SongRequest::STATUS_QUEUED, $songRequest->status);
        $this->assertSame($song->id, $songRequest->song_id);
    }
}
