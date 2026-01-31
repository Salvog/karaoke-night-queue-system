<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\EventNight;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_delete_events(): void
    {
        $staff = AdminUser::create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => 'password',
            'role' => AdminUser::ROLE_STAFF,
        ]);

        $venue = Venue::create([
            'name' => 'Main Stage',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENT123456',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_DRAFT,
            'starts_at' => now(),
        ]);

        $response = $this->actingAs($staff, 'admin')->delete("/admin/events/{$eventNight->id}");

        $response->assertStatus(403);
    }
}
