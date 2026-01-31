<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVenueCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_venue(): void
    {
        $admin = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => AdminUser::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin, 'admin')->post('/admin/venues', [
            'name' => 'Main Hall',
            'timezone' => 'Europe/Rome',
        ]);

        $venue = Venue::firstOrFail();

        $response->assertRedirect("/admin/venues/{$venue->id}/edit");
        $this->assertSame('Main Hall', $venue->name);

        $update = $this->actingAs($admin, 'admin')->put("/admin/venues/{$venue->id}", [
            'name' => 'Main Hall Updated',
            'timezone' => 'Europe/Milan',
        ]);

        $update->assertRedirect("/admin/venues/{$venue->id}/edit");
        $this->assertSame('Main Hall Updated', $venue->fresh()->name);

        $delete = $this->actingAs($admin, 'admin')->delete("/admin/venues/{$venue->id}");

        $delete->assertRedirect('/admin/venues');
        $this->assertDatabaseCount('venues', 0);
    }
}
