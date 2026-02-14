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
