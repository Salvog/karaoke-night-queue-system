<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\EventNight;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminThemeAssetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_theme_assets(): void
    {
        $this->skipIfGdMissing();

        Storage::fake('public');

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
            'code' => 'THEME1',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $response = $this->actingAs($admin, 'admin')->post("/admin/events/{$eventNight->id}/theme-ads", [
            'background_image' => UploadedFile::fake()->image('background.jpg'),
            'overlay_texts' => ['Welcome singers!', 'Tip your host'],
        ]);

        $response->assertStatus(302);

        $eventNight->refresh();

        $this->assertNotNull($eventNight->background_image_path);
        Storage::disk('public')->assertExists($eventNight->background_image_path);
        $this->assertSame(['Welcome singers!', 'Tip your host'], $eventNight->overlay_texts);
    }

    public function test_staff_cannot_upload_theme_assets(): void
    {
        $this->skipIfGdMissing();

        Storage::fake('public');

        $staff = AdminUser::create([
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'password' => 'password',
            'role' => AdminUser::ROLE_STAFF,
        ]);

        $venue = Venue::create([
            'name' => 'Side Room',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'THEME2',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $response = $this->actingAs($staff, 'admin')->post("/admin/events/{$eventNight->id}/theme-ads", [
            'background_image' => UploadedFile::fake()->image('background.jpg'),
        ]);

        $response->assertStatus(403);
    }

    private function skipIfGdMissing(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is required for image upload tests.');
        }
    }
}
