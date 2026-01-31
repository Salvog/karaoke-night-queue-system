<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\EventNight;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVenueManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_block_delete_venue_with_events(): void
    {
        $admin = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => AdminUser::ROLE_ADMIN,
        ]);

        $createResponse = $this->actingAs($admin, 'admin')->post('/admin/venues', [
            'name' => 'Main Hall',
            'timezone' => 'UTC',
        ]);

        $createResponse->assertStatus(302);
        $venue = Venue::firstOrFail();

        $updateResponse = $this->actingAs($admin, 'admin')->put("/admin/venues/{$venue->id}", [
            'name' => 'Main Hall Updated',
            'timezone' => 'Europe/Rome',
        ]);

        $updateResponse->assertStatus(302);
        $this->assertDatabaseHas('venues', [
            'id' => $venue->id,
            'name' => 'Main Hall Updated',
            'timezone' => 'Europe/Rome',
        ]);

        EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'LIVE01',
            'starts_at' => now(),
            'ends_at' => now()->addHours(4),
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
        ]);

        $deleteResponse = $this->actingAs($admin, 'admin')->delete("/admin/venues/{$venue->id}");

        $deleteResponse->assertStatus(302);
        $this->assertDatabaseHas('venues', ['id' => $venue->id]);
    }
}
