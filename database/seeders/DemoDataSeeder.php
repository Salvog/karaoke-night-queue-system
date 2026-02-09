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
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('DemoDataSeeder skipped outside local/testing environments.');

            return;
        }

        $now = now();

        $this->seedAdminUsers();
        $venues = $this->seedVenues();
        $themes = $this->seedThemes($venues);
        $banners = $this->seedBanners($venues);
        $songs = $this->seedSongs();
        $events = $this->seedEvents($venues, $themes, $banners, $now);

        foreach ($events as $event) {
            $this->resetEventRuntimeData($event);
        }

        $liveParticipants = collect([
            $this->createParticipant($events['live'], 'luca', 'Luca', $now->copy()->subMinutes(45)),
            $this->createParticipant($events['live'], 'sofia', 'Sofia', $now->copy()->subMinutes(28)),
            $this->createParticipant($events['live'], 'marco', 'Marco', $now->copy()->subMinutes(15)),
            $this->createParticipant($events['live'], 'giulia', 'Giulia', $now->copy()->subMinutes(8)),
            $this->createParticipant($events['live'], 'guest_404', 'Guest 404', null),
        ]);

        $playingStartedAt = $now->copy()->subSeconds(95);
        $playingRequest = SongRequest::create([
            'event_night_id' => $events['live']->id,
            'participant_id' => $liveParticipants[0]->id,
            'song_id' => $songs['don_t_stop_believin']->id,
            'status' => SongRequest::STATUS_PLAYING,
            'position' => 1,
            'played_at' => null,
        ]);

        SongRequest::create([
            'event_night_id' => $events['live']->id,
            'participant_id' => $liveParticipants[1]->id,
            'song_id' => $songs['shallow']->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 2,
        ]);

        SongRequest::create([
            'event_night_id' => $events['live']->id,
            'participant_id' => $liveParticipants[2]->id,
            'song_id' => $songs['abbracciame']->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 3,
        ]);

        SongRequest::create([
            'event_night_id' => $events['live']->id,
            'participant_id' => $liveParticipants[3]->id,
            'song_id' => $songs['uptown_funk']->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 4,
        ]);

        SongRequest::create([
            'event_night_id' => $events['live']->id,
            'participant_id' => $liveParticipants[4]->id,
            'song_id' => $songs['blinding_lights']->id,
            'status' => SongRequest::STATUS_CANCELED,
            'position' => null,
            'played_at' => $now->copy()->subMinutes(20),
        ]);

        SongRequest::create([
            'event_night_id' => $events['live']->id,
            'participant_id' => $liveParticipants[0]->id,
            'song_id' => $songs['dancing_queen']->id,
            'status' => SongRequest::STATUS_PLAYED,
            'position' => null,
            'played_at' => $now->copy()->subMinutes(34),
        ]);

        SongRequest::create([
            'event_night_id' => $events['live']->id,
            'participant_id' => $liveParticipants[1]->id,
            'song_id' => $songs['rolling_in_the_deep']->id,
            'status' => SongRequest::STATUS_SKIPPED,
            'position' => null,
            'played_at' => $now->copy()->subMinutes(26),
        ]);

        $playingDuration = $songs['don_t_stop_believin']->duration_seconds + $events['live']->break_seconds;

        PlaybackState::updateOrCreate(
            ['event_night_id' => $events['live']->id],
            [
                'current_request_id' => $playingRequest->id,
                'state' => PlaybackState::STATE_PLAYING,
                'started_at' => $playingStartedAt,
                'expected_end_at' => $playingStartedAt->copy()->addSeconds($playingDuration),
                'paused_at' => null,
            ]
        );

        $chillParticipants = collect([
            $this->createParticipant($events['upcoming'], 'vale', 'Vale', $now->copy()->subMinutes(4)),
            $this->createParticipant($events['upcoming'], 'tommy', 'Tommy', $now->copy()->subMinutes(3)),
        ]);

        SongRequest::create([
            'event_night_id' => $events['upcoming']->id,
            'participant_id' => $chillParticipants[0]->id,
            'song_id' => $songs['fix_you']->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 1,
        ]);

        SongRequest::create([
            'event_night_id' => $events['upcoming']->id,
            'participant_id' => $chillParticipants[1]->id,
            'song_id' => $songs['a_te']->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 2,
        ]);

        PlaybackState::updateOrCreate(
            ['event_night_id' => $events['upcoming']->id],
            [
                'current_request_id' => null,
                'state' => PlaybackState::STATE_IDLE,
                'started_at' => null,
                'expected_end_at' => null,
                'paused_at' => null,
            ]
        );

        PlaybackState::updateOrCreate(
            ['event_night_id' => $events['closed']->id],
            [
                'current_request_id' => null,
                'state' => PlaybackState::STATE_IDLE,
                'started_at' => null,
                'expected_end_at' => null,
                'paused_at' => null,
            ]
        );
    }

    private function seedAdminUsers(): void
    {
        AdminUser::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('password'),
                'role' => AdminUser::ROLE_ADMIN,
            ]
        );

        AdminUser::updateOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Demo Staff',
                'password' => Hash::make('password'),
                'role' => AdminUser::ROLE_STAFF,
            ]
        );
    }

    private function seedVenues(): array
    {
        return [
            'rome' => Venue::updateOrCreate(
                ['name' => 'Demo Karaoke Lounge'],
                ['timezone' => 'Europe/Rome']
            ),
            'milan' => Venue::updateOrCreate(
                ['name' => 'Milano Night Stage'],
                ['timezone' => 'Europe/Rome']
            ),
        ];
    }

    private function seedThemes(array $venues): array
    {
        return [
            'neon' => Theme::updateOrCreate(
                [
                    'venue_id' => $venues['rome']->id,
                    'name' => 'Neon Nights',
                ],
                [
                    'config' => [
                        'primaryColor' => '#2ad8ff',
                        'secondaryColor' => '#12132c',
                        'highlightColor' => '#ff4fd8',
                    ],
                ]
            ),
            'sunset' => Theme::updateOrCreate(
                [
                    'venue_id' => $venues['milan']->id,
                    'name' => 'Warm Sunset',
                ],
                [
                    'config' => [
                        'primaryColor' => '#ff9b42',
                        'secondaryColor' => '#1f2937',
                        'highlightColor' => '#ffd447',
                    ],
                ]
            ),
        ];
    }

    private function seedBanners(array $venues): array
    {
        return [
            'happy_hour' => AdBanner::updateOrCreate(
                [
                    'venue_id' => $venues['rome']->id,
                    'title' => 'Happy Hour Specials',
                ],
                [
                    'image_url' => 'https://example.com/banners/happy-hour.png',
                    'subtitle' => 'Spritz, mocktail & snack fino alle 22:00',
                    'logo_url' => 'https://example.com/logos/happy-hour.png',
                    'is_active' => true,
                ]
            ),
            'merch' => AdBanner::updateOrCreate(
                [
                    'venue_id' => $venues['rome']->id,
                    'title' => 'Limited Merch Drop',
                ],
                [
                    'image_url' => 'https://example.com/banners/merch.png',
                    'subtitle' => 'T-shirt ufficiali + limited poster',
                    'logo_url' => 'https://example.com/logos/merch.png',
                    'is_active' => false,
                ]
            ),
            'duet' => AdBanner::updateOrCreate(
                [
                    'venue_id' => $venues['milan']->id,
                    'title' => 'Duet Challenge',
                ],
                [
                    'image_url' => 'https://example.com/banners/duet-challenge.png',
                    'subtitle' => 'Premi per il duo più energico',
                    'logo_url' => 'https://example.com/logos/duet.png',
                    'is_active' => true,
                ]
            ),
        ];
    }

    private function seedSongs(): array
    {
        $catalog = [
            [
                'key' => 'take_on_me',
                'title' => 'Take On Me',
                'artist' => 'a-ha',
                'lyrics' => 'Talking away, I do not know what I am to say...',
                'duration_seconds' => 225,
            ],
            [
                'key' => 'don_t_stop_believin',
                'title' => "Don't Stop Believin'",
                'artist' => 'Journey',
                'lyrics' => 'Just a small-town girl, living in a lonely world...',
                'duration_seconds' => 250,
            ],
            [
                'key' => 'livin_on_a_prayer',
                'title' => "Livin' on a Prayer",
                'artist' => 'Bon Jovi',
                'lyrics' => 'Whoa, we are halfway there...',
                'duration_seconds' => 249,
            ],
            [
                'key' => 'bohemian_rhapsody',
                'title' => 'Bohemian Rhapsody',
                'artist' => 'Queen',
                'lyrics' => 'Is this the real life? Is this just fantasy?',
                'duration_seconds' => 355,
            ],
            [
                'key' => 'smells_like_teen_spirit',
                'title' => 'Smells Like Teen Spirit',
                'artist' => 'Nirvana',
                'lyrics' => 'With the lights out, it is less dangerous...',
                'duration_seconds' => 301,
            ],
            [
                'key' => 'rolling_in_the_deep',
                'title' => 'Rolling in the Deep',
                'artist' => 'Adele',
                'lyrics' => 'There is a fire starting in my heart...',
                'duration_seconds' => 228,
            ],
            [
                'key' => 'hotel_california',
                'title' => 'Hotel California',
                'artist' => 'Eagles',
                'lyrics' => 'On a dark desert highway...',
                'duration_seconds' => 391,
            ],
            [
                'key' => 'sweet_child_o_mine',
                'title' => "Sweet Child O' Mine",
                'artist' => "Guns N' Roses",
                'lyrics' => 'She has got a smile that it seems to me...',
                'duration_seconds' => 356,
            ],
            [
                'key' => 'uptown_funk',
                'title' => 'Uptown Funk',
                'artist' => 'Mark Ronson ft. Bruno Mars',
                'lyrics' => 'This hit, that ice cold...',
                'duration_seconds' => 269,
            ],
            [
                'key' => 'shallow',
                'title' => 'Shallow',
                'artist' => 'Lady Gaga & Bradley Cooper',
                'lyrics' => "Tell me somethin', girl...",
                'duration_seconds' => 216,
            ],
            [
                'key' => 'seven_rings',
                'title' => '7 rings',
                'artist' => 'Ariana Grande',
                'lyrics' => 'I want it, I got it...',
                'duration_seconds' => 179,
            ],
            [
                'key' => 'blinding_lights',
                'title' => 'Blinding Lights',
                'artist' => 'The Weeknd',
                'lyrics' => "I said, ooh, I'm blinded by the lights...",
                'duration_seconds' => 200,
            ],
            [
                'key' => 'hallelujah',
                'title' => 'Hallelujah',
                'artist' => 'Leonard Cohen',
                'lyrics' => 'Now I have heard there was a secret chord...',
                'duration_seconds' => 282,
            ],
            [
                'key' => 'dancing_queen',
                'title' => 'Dancing Queen',
                'artist' => 'ABBA',
                'lyrics' => 'You can dance, you can jive...',
                'duration_seconds' => 228,
            ],
            [
                'key' => 'i_will_survive',
                'title' => 'I Will Survive',
                'artist' => 'Gloria Gaynor',
                'lyrics' => 'At first I was afraid, I was petrified...',
                'duration_seconds' => 198,
            ],
            [
                'key' => 'suspicious_minds',
                'title' => 'Suspicious Minds',
                'artist' => 'Elvis Presley',
                'lyrics' => "We're caught in a trap...",
                'duration_seconds' => 263,
            ],
            [
                'key' => 'thunderstruck',
                'title' => 'Thunderstruck',
                'artist' => 'AC/DC',
                'lyrics' => 'Thunder, thunder...',
                'duration_seconds' => 292,
            ],
            [
                'key' => 'hips_don_t_lie',
                'title' => "Hips Don't Lie",
                'artist' => 'Shakira',
                'lyrics' => 'Oh baby when you talk like that...',
                'duration_seconds' => 218,
            ],
            [
                'key' => 'fix_you',
                'title' => 'Fix You',
                'artist' => 'Coldplay',
                'lyrics' => 'When you try your best but you do not succeed...',
                'duration_seconds' => 295,
            ],
            [
                'key' => 'mr_brightside',
                'title' => 'Mr. Brightside',
                'artist' => 'The Killers',
                'lyrics' => 'Coming out of my cage and I have been doing just fine...',
                'duration_seconds' => 222,
            ],
            [
                'key' => 'zitti_e_buoni',
                'title' => 'ZITTI E BUONI',
                'artist' => 'Maneskin',
                'lyrics' => 'Loro non sanno di che parlo...',
                'duration_seconds' => 195,
            ],
            [
                'key' => 'a_te',
                'title' => 'A Te',
                'artist' => 'Jovanotti',
                'lyrics' => 'A te che sei l unica al mondo...',
                'duration_seconds' => 260,
            ],
            [
                'key' => 'sara_perche_ti_amo',
                'title' => "Sara Perche Ti Amo",
                'artist' => 'Ricchi e Poveri',
                'lyrics' => 'Che confusione sara perche ti amo...',
                'duration_seconds' => 192,
            ],
            [
                'key' => 'abbracciame',
                'title' => 'Abbracciame',
                'artist' => 'Andrea Sannino',
                'lyrics' => 'Abbracciame cchiu forte...',
                'duration_seconds' => 267,
            ],
            [
                'key' => 'titanium',
                'title' => 'Titanium',
                'artist' => 'David Guetta ft. Sia',
                'lyrics' => 'You shoot me down, but I will not fall...',
                'duration_seconds' => 245,
            ],
            [
                'key' => 'someone_like_you',
                'title' => 'Someone Like You',
                'artist' => 'Adele',
                'lyrics' => 'Never mind, I will find someone like you...',
                'duration_seconds' => 285,
            ],
        ];

        $seededSongs = [];

        foreach ($catalog as $song) {
            $record = Song::updateOrCreate(
                [
                    'title' => $song['title'],
                    'artist' => $song['artist'],
                ],
                [
                    'lyrics' => $song['lyrics'],
                    'duration_seconds' => $song['duration_seconds'],
                ]
            );

            $seededSongs[$song['key']] = $record;
        }

        return $seededSongs;
    }

    private function seedEvents(array $venues, array $themes, array $banners, \Illuminate\Support\Carbon $now): array
    {
        $upcomingStart = $now->copy()->addDay()->setTime(19, 0);

        return [
            'live' => EventNight::updateOrCreate(
                ['code' => 'EVENT1'],
                [
                    'venue_id' => $venues['rome']->id,
                    'theme_id' => $themes['neon']->id,
                    'ad_banner_id' => $banners['happy_hour']->id,
                    'starts_at' => $now->copy()->subHours(2),
                    'ends_at' => $now->copy()->addHours(4),
                    'break_seconds' => 40,
                    'request_cooldown_seconds' => 20 * 60,
                    'join_pin' => '1234',
                    'status' => EventNight::STATUS_ACTIVE,
                    'overlay_texts' => [
                        'Benvenuti! Pronti a cantare?',
                        'Scansiona il QR code e manda la tua richiesta.',
                        'Applaudi e supporta ogni cantante.',
                    ],
                ]
            ),
            'upcoming' => EventNight::updateOrCreate(
                ['code' => 'EVENT2'],
                [
                    'venue_id' => $venues['milan']->id,
                    'theme_id' => $themes['sunset']->id,
                    'ad_banner_id' => $banners['duet']->id,
                    'starts_at' => $upcomingStart,
                    'ends_at' => $upcomingStart->copy()->addDay()->setTime(2, 0),
                    'break_seconds' => 35,
                    'request_cooldown_seconds' => 15 * 60,
                    'join_pin' => null,
                    'status' => EventNight::STATUS_DRAFT,
                    'overlay_texts' => [
                        'Domani sera: sfida duetti.',
                        'Nuove hit e classici intramontabili.',
                    ],
                ]
            ),
            'closed' => EventNight::updateOrCreate(
                ['code' => 'EVENTCLOSED'],
                [
                    'venue_id' => $venues['rome']->id,
                    'theme_id' => $themes['neon']->id,
                    'ad_banner_id' => $banners['merch']->id,
                    'starts_at' => $now->copy()->subDays(2)->setTime(19, 0),
                    'ends_at' => $now->copy()->subDay()->setTime(2, 0),
                    'break_seconds' => 45,
                    'request_cooldown_seconds' => 10 * 60,
                    'join_pin' => null,
                    'status' => EventNight::STATUS_CLOSED,
                    'overlay_texts' => [
                        'Serata conclusa, grazie a tutti!',
                    ],
                ]
            ),
        ];
    }

    private function resetEventRuntimeData(EventNight $event): void
    {
        $event->songRequests()->delete();
        $event->participants()->delete();
        $event->playbackState()->delete();
    }

    private function createParticipant(
        EventNight $event,
        string $deviceSuffix,
        string $displayName,
        ?\Illuminate\Support\Carbon $pinVerifiedAt
    ): Participant {
        return Participant::create([
            'event_night_id' => $event->id,
            'device_cookie_id' => strtolower($event->code . '-' . $deviceSuffix),
            'join_token_hash' => hash('sha256', Str::lower($event->code . '-' . $deviceSuffix . '-token')),
            'display_name' => $displayName,
            'pin_verified_at' => $pinVerifiedAt,
        ]);
    }
}
