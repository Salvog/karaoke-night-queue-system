<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\EventNight;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_event(): void
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

        $response = $this->actingAs($admin, 'admin')->post('/admin/events', [
            'code' => 'eventx1',
            'venue_id' => $venue->id,
            'starts_at' => '2024-04-01 19:00:00',
            'ends_at' => '2024-04-01 23:30:00',
            'break_seconds' => 120,
            'request_cooldown_seconds' => 60,
            'join_pin' => '1234',
            'status' => EventNight::STATUS_DRAFT,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseCount('event_nights', 1);

        $eventNight = EventNight::firstOrFail();
        $this->assertSame('EVENTX1', $eventNight->code);
        $this->assertSame($venue->id, $eventNight->venue_id);
        $this->assertSame(EventNight::STATUS_DRAFT, $eventNight->status);
    }
}
