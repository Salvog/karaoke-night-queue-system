<?php

namespace Tests\Feature;

use App\Models\AdBanner;
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
            'background_image' => $this->fakeImageUpload('background.jpg'),
            'event_logo' => $this->fakeImageUpload('logo.png'),
            'overlay_texts' => ['Welcome singers!', 'Tip your host'],
        ]);

        $response->assertStatus(302);

        $eventNight->refresh();

        $this->assertNotNull($eventNight->background_image_path);
        $this->assertNotNull($eventNight->brand_logo_path);
        Storage::disk('public')->assertExists($eventNight->background_image_path);
        Storage::disk('public')->assertExists($eventNight->brand_logo_path);
        $this->assertSame(['Welcome singers!', 'Tip your host'], $eventNight->overlay_texts);
    }

    public function test_staff_cannot_upload_theme_assets(): void
    {
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
            'background_image' => $this->fakeImageUpload('background.jpg'),
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_theme_page_renders_media_urls_with_same_origin_paths(): void
    {
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
            'code' => 'THEME3',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $backgroundPath = "event-themes/{$eventNight->id}/background.jpg";
        $logoPath = "event-branding/{$eventNight->id}/logo.png";
        $bannerImagePath = "ad-banners/{$eventNight->id}/sponsor.jpg";
        $bannerLogoPath = "ad-banners/{$eventNight->id}/sponsor-logo.png";
        Storage::disk('public')->put($backgroundPath, 'bg');
        Storage::disk('public')->put($logoPath, 'logo');
        Storage::disk('public')->put($bannerImagePath, 'banner');
        Storage::disk('public')->put($bannerLogoPath, 'banner-logo');

        $eventNight->update([
            'background_image_path' => $backgroundPath,
            'brand_logo_path' => $logoPath,
        ]);

        AdBanner::create([
            'venue_id' => $venue->id,
            'event_night_id' => $eventNight->id,
            'title' => 'Sponsor',
            'subtitle' => 'Promo',
            'image_url' => "http://localhost:8000/storage/{$bannerImagePath}",
            'logo_url' => "http://127.0.0.1:8000/storage/{$bannerLogoPath}",
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->get("/admin/events/{$eventNight->id}/theme-ads");

        $response->assertOk();
        $response->assertSee('src="/media/'.$backgroundPath.'"', false);
        $response->assertSee('src="/media/'.$logoPath.'"', false);
        $response->assertSee('src="/media/'.$bannerImagePath.'"', false);
        $response->assertSee('src="/media/'.$bannerLogoPath.'"', false);
    }

    public function test_admin_banner_upload_saves_relative_media_urls(): void
    {
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
            'code' => 'THEME4',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $response = $this->actingAs($admin, 'admin')->post("/admin/events/{$eventNight->id}/ad-banners", [
            'title' => 'Sponsor locale',
            'subtitle' => 'Promo',
            'image' => $this->fakeImageUpload('banner.jpg'),
            'logo' => $this->fakeImageUpload('logo.png'),
            'is_active' => '1',
        ]);

        $response->assertStatus(302);

        $banner = AdBanner::query()->firstOrFail();
        $this->assertStringStartsWith('/media/ad-banners/'.$eventNight->id.'/', $banner->image_url);
        $this->assertStringStartsWith('/media/ad-banners/'.$eventNight->id.'/', (string) $banner->logo_url);
        $this->assertSame($eventNight->id, $banner->event_night_id);
    }

    public function test_admin_theme_page_hides_banners_from_other_events_on_same_venue(): void
    {
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

        $eventA = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'THEME5A',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $eventB = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'THEME5B',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now()->addHour(),
        ]);

        AdBanner::create([
            'venue_id' => $venue->id,
            'event_night_id' => $eventA->id,
            'title' => 'Sponsor Evento A',
            'image_url' => '/media/ad-banners/demo-a.jpg',
            'is_active' => true,
        ]);

        AdBanner::create([
            'venue_id' => $venue->id,
            'event_night_id' => $eventB->id,
            'title' => 'Sponsor Evento B',
            'image_url' => '/media/ad-banners/demo-b.jpg',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->get("/admin/events/{$eventB->id}/theme-ads");

        $response->assertOk();
        $response->assertSee('Sponsor Evento B');
        $response->assertDontSee('Sponsor Evento A');
    }

    public function test_admin_cannot_assign_banner_from_other_event_on_same_venue(): void
    {
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

        $eventA = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'THEME6A',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $eventB = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'THEME6B',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now()->addHour(),
        ]);

        $bannerA = AdBanner::create([
            'venue_id' => $venue->id,
            'event_night_id' => $eventA->id,
            'title' => 'Sponsor Evento A',
            'image_url' => '/media/ad-banners/demo-a.jpg',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->post("/admin/events/{$eventB->id}/theme-ads", [
            'ad_banner_id' => $bannerA->id,
        ]);

        $response->assertSessionHasErrors('ad_banner_id');
        $this->assertNull($eventB->fresh()->ad_banner_id);
    }

    private function fakeImageUpload(string $filename): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO2Y8q0AAAAASUVORK5CYII=',
            true
        );

        if ($png === false) {
            throw new \RuntimeException('Unable to build fake image payload.');
        }

        return UploadedFile::fake()->createWithContent($filename, $png);
    }
}
