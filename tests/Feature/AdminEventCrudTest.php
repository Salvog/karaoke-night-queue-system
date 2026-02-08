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
        $this->assertSame(6, strlen($eventNight->code));
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
            $response->assertSee('value="active" selected', false);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_events_index_splits_ongoing_future_and_past_events(): void
    {
        $frozenNow = Carbon::create(2026, 2, 8, 20, 0, 0, 'UTC');
        Carbon::setTestNow($frozenNow);

        try {
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

            EventNight::create([
                'venue_id' => $venue->id,
                'code' => 'LIVE01',
                'break_seconds' => 40,
                'request_cooldown_seconds' => 1200,
                'status' => EventNight::STATUS_ACTIVE,
                'starts_at' => $frozenNow->copy()->subHours(2),
                'ends_at' => $frozenNow->copy()->addHours(2),
            ]);

            EventNight::create([
                'venue_id' => $venue->id,
                'code' => 'LIVE02',
                'break_seconds' => 40,
                'request_cooldown_seconds' => 1200,
                'status' => EventNight::STATUS_ACTIVE,
                'starts_at' => $frozenNow->copy()->subHours(1),
                'ends_at' => null,
            ]);

            EventNight::create([
                'venue_id' => $venue->id,
                'code' => 'FUTR01',
                'break_seconds' => 40,
                'request_cooldown_seconds' => 1200,
                'status' => EventNight::STATUS_DRAFT,
                'starts_at' => $frozenNow->copy()->addDay(),
                'ends_at' => $frozenNow->copy()->addDay()->addHours(5),
            ]);

            EventNight::create([
                'venue_id' => $venue->id,
                'code' => 'PAST01',
                'break_seconds' => 40,
                'request_cooldown_seconds' => 1200,
                'status' => EventNight::STATUS_CLOSED,
                'starts_at' => $frozenNow->copy()->subDays(2),
                'ends_at' => $frozenNow->copy()->subDay(),
            ]);

            $response = $this->actingAs($admin, 'admin')->get('/admin/events');

            $response->assertOk();
            $response->assertSee('Eventi in corso');
            $response->assertSee('Eventi futuri');
            $response->assertSee('Eventi passati');
            $response->assertSee('LIVE01');
            $response->assertSee('LIVE02');
            $response->assertSee('FUTR01');
            $response->assertSee('PAST01');
        } finally {
            Carbon::setTestNow();
        }
    }
}
