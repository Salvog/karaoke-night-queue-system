<?php

namespace Database\Seeders;

use App\Models\AdBanner;
use App\Models\EventNight;
use App\Models\PlaybackState;
use App\Models\Song;
use App\Models\Theme;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $venue = Venue::create([
            'name' => 'Demo Karaoke Lounge',
            'timezone' => 'Europe/Rome',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENT1',
            'break_seconds' => 120,
            'request_cooldown_seconds' => 300,
            'join_pin' => '1234',
            'status' => EventNight::STATUS_LIVE,
        ]);

        PlaybackState::create([
            'event_night_id' => $eventNight->id,
            'state' => PlaybackState::STATE_IDLE,
        ]);

        Song::insert([
            [
                'title' => 'Take On Me',
                'artist' => 'a-ha',
                'lyrics' => 'Talking away, I don’t know what I’m to say...',
                'duration_seconds' => 225,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Don’t Stop Believin’',
                'artist' => 'Journey',
                'lyrics' => 'Just a small-town girl, living in a lonely world...',
                'duration_seconds' => 250,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Livin’ on a Prayer',
                'artist' => 'Bon Jovi',
                'lyrics' => 'Whoa, we’re halfway there...',
                'duration_seconds' => 249,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Theme::create([
            'venue_id' => $venue->id,
            'name' => 'Default Theme',
            'config' => [
                'primaryColor' => '#2f80ed',
                'secondaryColor' => '#111827',
            ],
        ]);

        AdBanner::create([
            'venue_id' => $venue->id,
            'title' => 'Happy Hour Specials',
            'image_url' => 'https://example.com/banners/happy-hour.png',
            'is_active' => true,
        ]);
    }
}
