<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\EventNight;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminQueueStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_fetch_queue_state(): void
    {
        $admin = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => AdminUser::ROLE_ADMIN,
        ]);

        $venue = Venue::create([
            'name' => 'Main Stage',
            'timezone' => 'Europe/Rome',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENTSTATE',
            'starts_at' => now(),
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin, 'admin')->get("/admin/events/{$eventNight->id}/queue/state");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'timezone' => 'Europe/Rome',
        ]);
    }
}
