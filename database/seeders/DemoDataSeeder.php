<?php

namespace Database\Seeders;

use App\Models\AdBanner;
use App\Models\AdminUser;
use App\Models\EventNight;
use App\Models\Participant;
use App\Models\PlaybackState;
use App\Models\Song;
use App\Models\SongRequest;
use App\Models\Theme;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::create([
            'name' => 'Demo Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => AdminUser::ROLE_ADMIN,
        ]);

        AdminUser::create([
            'name' => 'Demo Staff',
            'email' => 'staff@example.com',
            'password' => Hash::make('password'),
            'role' => AdminUser::ROLE_STAFF,
        ]);

        $venueRome = Venue::create([
            'name' => 'Demo Karaoke Lounge',
            'timezone' => 'Europe/Rome',
        ]);

        $venueMilan = Venue::create([
            'name' => 'Milano Night Stage',
            'timezone' => 'Europe/Rome',
        ]);

        $defaultTheme = Theme::create([
            'venue_id' => $venueRome->id,
            'name' => 'Neon Nights',
            'config' => [
                'primaryColor' => '#38bdf8',
                'secondaryColor' => '#0f172a',
            ],
        ]);

        $warmTheme = Theme::create([
            'venue_id' => $venueMilan->id,
            'name' => 'Warm Sunset',
            'config' => [
                'primaryColor' => '#f97316',
                'secondaryColor' => '#1f2937',
            ],
        ]);

        $happyHourBanner = AdBanner::create([
            'venue_id' => $venueRome->id,
            'title' => 'Happy Hour Specials',
            'image_url' => 'https://example.com/banners/happy-hour.png',
            'is_active' => true,
        ]);

        $merchBanner = AdBanner::create([
            'venue_id' => $venueRome->id,
            'title' => 'Limited Merch Drop',
            'image_url' => 'https://example.com/banners/merch.png',
            'is_active' => false,
        ]);

        $songs = collect([
            [
                'title' => 'Take On Me',
                'artist' => 'a-ha',
                'lyrics' => 'Talking away, I don’t know what I’m to say...',
                'duration_seconds' => 225,
            ],
            [
                'title' => 'Don’t Stop Believin’',
                'artist' => 'Journey',
                'lyrics' => 'Just a small-town girl, living in a lonely world...',
                'duration_seconds' => 250,
            ],
            [
                'title' => 'Livin’ on a Prayer',
                'artist' => 'Bon Jovi',
                'lyrics' => 'Whoa, we’re halfway there...',
                'duration_seconds' => 249,
            ],
            [
                'title' => 'Bohemian Rhapsody',
                'artist' => 'Queen',
                'lyrics' => 'Is this the real life? Is this just fantasy?',
                'duration_seconds' => 355,
            ],
            [
                'title' => 'Smells Like Teen Spirit',
                'artist' => 'Nirvana',
                'lyrics' => 'With the lights out, it’s less dangerous...',
                'duration_seconds' => 301,
            ],
            [
                'title' => 'Rolling in the Deep',
                'artist' => 'Adele',
                'lyrics' => 'There’s a fire starting in my heart...',
                'duration_seconds' => 228,
            ],
            [
                'title' => 'Hotel California',
                'artist' => 'Eagles',
                'lyrics' => 'On a dark desert highway...',
                'duration_seconds' => 391,
            ],
            [
                'title' => 'Sweet Child O’ Mine',
                'artist' => 'Guns N’ Roses',
                'lyrics' => 'She’s got a smile that it seems to me...',
                'duration_seconds' => 356,
            ],
            [
                'title' => 'Uptown Funk',
                'artist' => 'Mark Ronson ft. Bruno Mars',
                'lyrics' => 'This hit, that ice cold...',
                'duration_seconds' => 269,
            ],
            [
                'title' => 'Shallow',
                'artist' => 'Lady Gaga & Bradley Cooper',
                'lyrics' => 'Tell me somethin’, girl...',
                'duration_seconds' => 216,
            ],
            [
                'title' => '7 rings',
                'artist' => 'Ariana Grande',
                'lyrics' => 'I want it, I got it...',
                'duration_seconds' => 179,
            ],
            [
                'title' => 'Blinding Lights',
                'artist' => 'The Weeknd',
                'lyrics' => 'I said, ooh, I’m blinded by the lights...',
                'duration_seconds' => 200,
            ],
            [
                'title' => 'Hallelujah',
                'artist' => 'Leonard Cohen',
                'lyrics' => 'Now I’ve heard there was a secret chord...',
                'duration_seconds' => 282,
            ],
            [
                'title' => 'Dancing Queen',
                'artist' => 'ABBA',
                'lyrics' => 'You can dance, you can jive...',
                'duration_seconds' => 228,
            ],
            [
                'title' => 'I Will Survive',
                'artist' => 'Gloria Gaynor',
                'lyrics' => 'At first I was afraid, I was petrified...',
                'duration_seconds' => 198,
            ],
            [
                'title' => 'Suspicious Minds',
                'artist' => 'Elvis Presley',
                'lyrics' => 'We’re caught in a trap...',
                'duration_seconds' => 263,
            ],
            [
                'title' => 'Thunderstruck',
                'artist' => 'AC/DC',
                'lyrics' => 'Thunder, thunder...',
                'duration_seconds' => 292,
            ],
            [
                'title' => 'Hips Don’t Lie',
                'artist' => 'Shakira',
                'lyrics' => 'Oh baby when you talk like that...',
                'duration_seconds' => 218,
            ],
        ])->map(fn (array $song) => Song::create($song));

        $eventLive = EventNight::create([
            'venue_id' => $venueRome->id,
            'theme_id' => $defaultTheme->id,
            'ad_banner_id' => $happyHourBanner->id,
            'code' => 'EVENT1',
            'starts_at' => now()->addHours(2),
            'break_seconds' => 90,
            'request_cooldown_seconds' => 240,
            'join_pin' => '1234',
            'status' => EventNight::STATUS_ACTIVE,
            'overlay_texts' => [
                'Welcome singers!',
                'Scan the QR to join.',
                'House rules: Be kind & cheer.',
            ],
        ]);

        $eventChill = EventNight::create([
            'venue_id' => $venueMilan->id,
            'theme_id' => $warmTheme->id,
            'code' => 'EVENT2',
            'starts_at' => now()->addDays(1),
            'break_seconds' => 60,
            'request_cooldown_seconds' => 0,
            'join_pin' => null,
            'status' => EventNight::STATUS_ACTIVE,
        ]);

        $eventClosed = EventNight::create([
            'venue_id' => $venueRome->id,
            'ad_banner_id' => $merchBanner->id,
            'code' => 'EVENTCLOSED',
            'starts_at' => now()->subDays(2),
            'break_seconds' => 90,
            'request_cooldown_seconds' => 180,
            'join_pin' => null,
            'status' => EventNight::STATUS_CLOSED,
        ]);

        $participants = collect([
            [
                'display_name' => 'Luca',
                'pin_verified_at' => now()->subMinutes(30),
            ],
            [
                'display_name' => 'Sofia',
                'pin_verified_at' => now()->subMinutes(10),
            ],
            [
                'display_name' => 'Marco',
                'pin_verified_at' => now()->subMinutes(5),
            ],
            [
                'display_name' => 'Guest 404',
                'pin_verified_at' => null,
            ],
        ])->map(function (array $data) use ($eventLive) {
            return Participant::create([
                'event_night_id' => $eventLive->id,
                'device_cookie_id' => Str::uuid()->toString(),
                'join_token_hash' => hash('sha256', Str::random(32)),
                'display_name' => $data['display_name'],
                'pin_verified_at' => $data['pin_verified_at'],
            ]);
        });

        $playingRequest = SongRequest::create([
            'event_night_id' => $eventLive->id,
            'participant_id' => $participants[0]->id,
            'song_id' => $songs[0]->id,
            'status' => SongRequest::STATUS_PLAYING,
            'position' => 1,
            'played_at' => null,
        ]);

        SongRequest::create([
            'event_night_id' => $eventLive->id,
            'participant_id' => $participants[1]->id,
            'song_id' => $songs[3]->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 2,
        ]);

        SongRequest::create([
            'event_night_id' => $eventLive->id,
            'participant_id' => $participants[2]->id,
            'song_id' => $songs[4]->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 3,
        ]);

        SongRequest::create([
            'event_night_id' => $eventLive->id,
            'participant_id' => $participants[3]->id,
            'song_id' => $songs[5]->id,
            'status' => SongRequest::STATUS_CANCELED,
            'position' => 4,
        ]);

        SongRequest::create([
            'event_night_id' => $eventLive->id,
            'participant_id' => $participants[0]->id,
            'song_id' => $songs[6]->id,
            'status' => SongRequest::STATUS_PLAYED,
            'position' => 0,
            'played_at' => now()->subMinutes(25),
        ]);

        SongRequest::create([
            'event_night_id' => $eventLive->id,
            'participant_id' => $participants[1]->id,
            'song_id' => $songs[7]->id,
            'status' => SongRequest::STATUS_SKIPPED,
            'position' => 0,
            'played_at' => now()->subMinutes(18),
        ]);

        PlaybackState::create([
            'event_night_id' => $eventLive->id,
            'current_request_id' => $playingRequest->id,
            'state' => PlaybackState::STATE_PLAYING,
            'started_at' => now()->subMinutes(1),
            'expected_end_at' => now()->addMinutes(2),
        ]);

        PlaybackState::create([
            'event_night_id' => $eventChill->id,
            'state' => PlaybackState::STATE_IDLE,
        ]);

        PlaybackState::create([
            'event_night_id' => $eventClosed->id,
            'state' => PlaybackState::STATE_IDLE,
        ]);
    }
}
