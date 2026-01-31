<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVenueCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_venues(): void
    {
        $admin = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => AdminUser::ROLE_ADMIN,
        ]);

        $createResponse = $this->actingAs($admin, 'admin')->post('/admin/venues', [
            'name' => 'Main Hall',
            'timezone' => 'Europe/Rome',
        ]);

        $createResponse->assertStatus(302);
        $venue = Venue::firstOrFail();

        $updateResponse = $this->actingAs($admin, 'admin')->put("/admin/venues/{$venue->id}", [
            'name' => 'Main Hall Updated',
            'timezone' => 'UTC',
        ]);

        $updateResponse->assertStatus(302);
        $this->assertDatabaseHas('venues', [
            'id' => $venue->id,
            'name' => 'Main Hall Updated',
            'timezone' => 'UTC',
        ]);

        $deleteResponse = $this->actingAs($admin, 'admin')->delete("/admin/venues/{$venue->id}");

        $deleteResponse->assertStatus(302);
        $this->assertDatabaseMissing('venues', [
            'id' => $venue->id,
        ]);
    }
}
