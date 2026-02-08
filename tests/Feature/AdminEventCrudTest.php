<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\EventNight;
use App\Models\Venue;
use Carbon\Carbon;
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
            'venue_id' => $venue->id,
            'starts_at' => '2024-04-01 19:00:00',
            'ends_at' => '2024-04-01 23:30:00',
            'break_seconds' => 120,
            'request_cooldown_minutes' => 1,
            'join_pin' => '1234',
            'status' => EventNight::STATUS_DRAFT,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseCount('event_nights', 1);

        $eventNight = EventNight::firstOrFail();
        $this->assertNotEmpty($eventNight->code);
        $this->assertSame(10, strlen($eventNight->code));
        $this->assertSame(60, $eventNight->request_cooldown_seconds);
        $this->assertSame($venue->id, $eventNight->venue_id);
        $this->assertSame(EventNight::STATUS_DRAFT, $eventNight->status);
    }

    public function test_create_form_has_expected_defaults(): void
    {
        $frozenNow = Carbon::create(2026, 2, 8, 11, 30, 0, 'UTC');
        Carbon::setTestNow($frozenNow);

        try {
            $admin = AdminUser::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => 'password',
                'role' => AdminUser::ROLE_ADMIN,
            ]);

            Venue::create([
                'name' => 'Main Hall',
                'timezone' => 'UTC',
            ]);

            $response = $this->actingAs($admin, 'admin')->get('/admin/events/create');

            $response->assertOk();
            $response->assertSee('name="request_cooldown_minutes"', false);
            $response->assertSee('value="' . $frozenNow->copy()->setTime(19, 0)->format('Y-m-d\TH:i') . '"', false);
            $response->assertSee('value="' . $frozenNow->copy()->addDay()->setTime(2, 0)->format('Y-m-d\TH:i') . '"', false);
            $response->assertSee('name="break_seconds" min="0" value="40"', false);
            $response->assertSee('value="20"', false);
        } finally {
            Carbon::setTestNow();
        }
    }
}
