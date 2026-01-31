<?php

namespace Tests\Unit;

use App\Models\EventNight;
use App\Models\Participant;
use App\Models\PlaybackState;
use App\Models\Song;
use App\Models\SongRequest;
use App\Models\Venue;
use App\Modules\PublicJoin\Services\RequestEtaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestEtaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_eta_is_zero_when_idle_and_queue_empty(): void
    {
        $eventNight = $this->makeEventNight(['break_seconds' => 5]);

        $service = new RequestEtaService();

        $this->assertSame(0, $service->calculateSeconds($eventNight, now()));
    }

    public function test_eta_includes_remaining_time_when_playing(): void
    {
        $eventNight = $this->makeEventNight(['break_seconds' => 10]);

        $now = Carbon::parse('2024-01-01 10:00:00');
        Carbon::setTestNow($now);

        PlaybackState::create([
            'event_night_id' => $eventNight->id,
            'state' => PlaybackState::STATE_PLAYING,
            'expected_end_at' => $now->copy()->addSeconds(120),
        ]);

        $service = new RequestEtaService();

        $this->assertSame(120, $service->calculateSeconds($eventNight, $now));

        Carbon::setTestNow();
    }

    public function test_eta_includes_queue_durations_when_stopped(): void
    {
        $eventNight = $this->makeEventNight(['break_seconds' => 5]);

        $songA = Song::create([
            'title' => 'Song A',
            'artist' => 'Artist A',
            'duration_seconds' => 100,
        ]);

        $songB = Song::create([
            'title' => 'Song B',
            'artist' => 'Artist B',
            'duration_seconds' => 200,
        ]);

        $participantA = $this->makeParticipant($eventNight, 'device-a');
        $participantB = $this->makeParticipant($eventNight, 'device-b');

        SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participantA->id,
            'song_id' => $songA->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 1,
        ]);

        SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participantB->id,
            'song_id' => $songB->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 2,
        ]);

        $service = new RequestEtaService();

        $this->assertSame(310, $service->calculateSeconds($eventNight, now()));
    }

    public function test_eta_includes_remaining_time_and_queue(): void
    {
        $eventNight = $this->makeEventNight(['break_seconds' => 10]);

        $songA = Song::create([
            'title' => 'Song A',
            'artist' => 'Artist A',
            'duration_seconds' => 60,
        ]);

        $songB = Song::create([
            'title' => 'Song B',
            'artist' => 'Artist B',
            'duration_seconds' => 120,
        ]);

        $participantA = $this->makeParticipant($eventNight, 'device-c');
        $participantB = $this->makeParticipant($eventNight, 'device-d');

        SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participantA->id,
            'song_id' => $songA->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 1,
        ]);

        SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participantB->id,
            'song_id' => $songB->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 2,
        ]);

        $now = Carbon::parse('2024-01-01 10:00:00');
        Carbon::setTestNow($now);

        PlaybackState::create([
            'event_night_id' => $eventNight->id,
            'state' => PlaybackState::STATE_PLAYING,
            'expected_end_at' => $now->copy()->addSeconds(90),
        ]);

        $service = new RequestEtaService();

        $this->assertSame(290, $service->calculateSeconds($eventNight, $now));

        Carbon::setTestNow();
    }

    private function makeEventNight(array $overrides = []): EventNight
    {
        $venue = Venue::create([
            'name' => 'Test Venue',
            'timezone' => 'UTC',
        ]);

        return EventNight::create(array_merge([
            'venue_id' => $venue->id,
            'code' => 'EVENT' . random_int(1000, 9999),
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ], $overrides));
    }

    private function makeParticipant(EventNight $eventNight, string $deviceCookieId): Participant
    {
        return Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => $deviceCookieId,
            'join_token_hash' => hash('sha256', $deviceCookieId),
        ]);
    }
}
