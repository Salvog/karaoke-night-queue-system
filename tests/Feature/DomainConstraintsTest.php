<?php

namespace Tests\Feature;

use App\Models\EventNight;
use App\Models\Participant;
use App\Models\Song;
use App\Models\SongRequest;
use App\Models\Venue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainConstraintsTest extends TestCase
{
    use RefreshDatabase;

    public function test_song_duration_must_be_positive(): void
    {
        $this->expectException(QueryException::class);

        Song::create([
            'title' => 'Zero Duration',
            'artist' => 'Silence',
            'duration_seconds' => 0,
        ]);
    }

    public function test_song_request_status_must_be_valid(): void
    {
        $venue = Venue::create([
            'name' => 'Test Venue',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'TEST1',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_SCHEDULED,
        ]);

        $song = Song::create([
            'title' => 'Valid Song',
            'artist' => 'Artist',
            'duration_seconds' => 120,
        ]);

        $participant = Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-123',
            'join_token_hash' => str_repeat('a', 64),
        ]);

        $this->expectException(QueryException::class);

        SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $song->id,
            'status' => 'invalid-status',
        ]);
    }
}
