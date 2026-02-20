<?php

namespace Tests\Feature;

use App\Models\AdBanner;
use App\Models\EventNight;
use App\Models\Participant;
use App\Models\PlaybackState;
use App\Models\Song;
use App\Models\SongRequest;
use App\Models\Theme;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_screen_shows_event_state(): void
    {
        $venue = Venue::create([
            'name' => 'Main Room',
            'timezone' => 'UTC',
        ]);

        $theme = Theme::create([
            'venue_id' => $venue->id,
            'name' => 'Neon Glow',
            'config' => ['primaryColor' => '#ff00ff'],
        ]);

        $banner = AdBanner::create([
            'venue_id' => $venue->id,
            'title' => 'Late Night Happy Hour',
            'subtitle' => 'Promo drink fino a mezzanotte',
            'image_url' => 'https://example.com/banner.png',
            'logo_url' => 'https://example.com/banner-logo.png',
            'is_active' => true,
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'theme_id' => $theme->id,
            'ad_banner_id' => $banner->id,
            'code' => 'SCREEN1',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
            'brand_logo_path' => 'event-branding/screen1-logo.png',
            'overlay_texts' => ['Welcome singers!'],
        ]);

        $participant = Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-999',
            'join_token_hash' => hash('sha256', 'token-999'),
            'display_name' => 'Marco',
        ]);

        $song = Song::create([
            'title' => 'Skyline',
            'artist' => 'Nova',
            'lyrics' => 'City lights in the skyline...',
            'duration_seconds' => 200,
        ]);

        $playingRequest = SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $song->id,
            'status' => SongRequest::STATUS_PLAYING,
            'position' => 1,
        ]);

        SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $song->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 2,
        ]);

        SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $song->id,
            'status' => SongRequest::STATUS_PLAYED,
            'played_at' => now()->subMinute(),
            'position' => 3,
        ]);

        PlaybackState::create([
            'event_night_id' => $eventNight->id,
            'current_request_id' => $playingRequest->id,
            'state' => PlaybackState::STATE_PLAYING,
            'started_at' => now()->subSeconds(10),
            'expected_end_at' => now()->addSeconds(190),
        ]);

        $response = $this->get("/screen/{$eventNight->code}");

        $response->assertStatus(200);
        $response->assertSee('Skyline');
        $response->assertSee('Neon Glow');
        $response->assertSee('Late Night Happy Hour');
        $response->assertSee('Promo drink fino a mezzanotte');
        $response->assertSee('Marco');
    }

    public function test_public_screen_state_endpoint_returns_payload(): void
    {
        $venue = Venue::create([
            'name' => 'Main Room',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'SCREEN2',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $participant = Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-1000',
            'join_token_hash' => hash('sha256', 'token-1000'),
            'display_name' => 'Elena',
        ]);

        $song = Song::create([
            'title' => 'Midnight Drive',
            'artist' => 'Aurora',
            'duration_seconds' => 180,
        ]);

        $request = SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $song->id,
            'status' => SongRequest::STATUS_PLAYING,
            'position' => 1,
        ]);

        PlaybackState::create([
            'event_night_id' => $eventNight->id,
            'current_request_id' => $request->id,
            'state' => PlaybackState::STATE_PLAYING,
            'started_at' => now()->subSeconds(15),
            'expected_end_at' => now()->addSeconds(165),
        ]);

        $response = $this->getJson("/screen/{$eventNight->code}/state");

        $response->assertStatus(200);
        $response->assertJsonPath('playback.song.title', 'Midnight Drive');
        $response->assertJsonPath('playback.song.requested_by', 'Elena');
        $response->assertJsonPath('event.code', 'SCREEN2');
        $response->assertJsonPath('event.join_url', route('public.join.show', $eventNight->code));
        $response->assertJsonPath('queue.total_pending', 0);
    }

    public function test_public_screen_state_normalizes_local_absolute_banner_urls_to_media_route(): void
    {
        Storage::fake('public');

        $venue = Venue::create([
            'name' => 'Main Room',
            'timezone' => 'UTC',
        ]);

        $path = "ad-banners/{$venue->id}/sponsor.jpg";
        Storage::disk('public')->put($path, 'fake-image');

        $banner = AdBanner::create([
            'venue_id' => $venue->id,
            'title' => 'Sponsor locale',
            'subtitle' => 'Promo',
            'image_url' => "http://localhost:8000/storage/{$path}",
            'logo_url' => "http://127.0.0.1:8000/storage/{$path}",
            'is_active' => true,
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'ad_banner_id' => $banner->id,
            'code' => 'SCREEN5',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $response = $this->getJson("/screen/{$eventNight->code}/state");

        $response->assertStatus(200);
        $response->assertJsonPath('theme.banner.image_url', "/media/{$path}");
        $response->assertJsonPath('theme.banner.logo_url', "/media/{$path}");
        $response->assertJsonPath('theme.sponsor_banners.0.image_url', "/media/{$path}");
        $response->assertJsonPath('theme.sponsor_banners.0.logo_url', "/media/{$path}");
    }

    public function test_public_screen_serializes_realtime_configuration_in_view(): void
    {
        config([
            'public_screen.realtime.max_consecutive_errors' => 7,
            'public_screen.realtime.connect_timeout_seconds' => 12,
        ]);

        $venue = Venue::create([
            'name' => 'Main Room',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'SCREEN4',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $response = $this->get("/screen/{$eventNight->code}");

        $response->assertStatus(200);
        $response->assertSee('const realtimeMaxConsecutiveErrors = 7;', false);
        $response->assertSee('const realtimeConnectTimeoutMs = 12000;', false);
    }

    public function test_public_screen_stream_endpoint_returns_sse_snapshot(): void
    {
        config([
            'public_screen.realtime.stream_seconds' => 0,
            'public_screen.realtime.sleep_seconds' => 0,
        ]);

        $venue = Venue::create([
            'name' => 'Main Room',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'STREAM1',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $response = $this->get("/screen/{$eventNight->code}/stream");

        $response->assertStatus(200);
        $response->assertStreamed();
        $this->assertStringStartsWith(
            'text/event-stream',
            (string) $response->baseResponse->headers->get('Content-Type')
        );

        $content = $response->streamedContent();

        $this->assertStringContainsString("event: snapshot\n", $content);
        $this->assertStringContainsString('data: {', $content);
    }

    public function test_public_media_endpoint_streams_existing_file_from_public_disk(): void
    {
        Storage::fake('public');

        $path = 'ad-banners/1/test.jpg';
        Storage::disk('public')->put($path, 'fake-image-content');

        $response = $this->get('/media/' . $path);

        $response->assertStatus(200);
        $response->assertStreamed();
        $this->assertSame('fake-image-content', $response->streamedContent());
    }

    public function test_public_media_endpoint_returns_404_for_missing_file(): void
    {
        Storage::fake('public');

        $response = $this->get('/media/ad-banners/1/missing.jpg');

        $response->assertStatus(404);
    }

    public function test_public_media_endpoint_blocks_path_traversal(): void
    {
        Storage::fake('public');

        $responseWithDots = $this->get('/media/..%2F.env');
        $responseWithBackslashes = $this->get('/media/..%5C.env');

        $responseWithDots->assertStatus(404);
        $responseWithBackslashes->assertStatus(404);
    }

    public function test_public_screen_requires_live_event(): void
    {
        $venue = Venue::create([
            'name' => 'Main Room',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'SCREEN3',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_DRAFT,
            'starts_at' => now(),
        ]);

        $response = $this->get("/screen/{$eventNight->code}");

        $response->assertStatus(403);
    }
}
