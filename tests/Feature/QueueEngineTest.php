<?php

namespace Tests\Feature;

use App\Models\EventNight;
use App\Models\Participant;
use App\Models\PlaybackState;
use App\Models\Song;
use App\Models\SongRequest;
use App\Models\Venue;
use App\Modules\Queue\Services\QueueEngine;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class QueueEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_advance_moves_to_next_request(): void
    {
        $eventNight = $this->seedEvent();
        $requests = $this->seedRequests($eventNight);

        $queueEngine = $this->app->make(QueueEngine::class);
        $startedAt = Carbon::parse('2024-01-01 10:00:00');

        $queueEngine->startNext($eventNight, $startedAt);

        $playbackState = PlaybackState::firstOrFail();

        $this->assertSame(PlaybackState::STATE_PLAYING, $playbackState->state);
        $this->assertSame(
            $startedAt->copy()->addSeconds(190)->toDateTimeString(),
            $playbackState->expected_end_at->toDateTimeString()
        );

        $queueEngine->advanceIfNeeded($eventNight, $startedAt->copy()->addSeconds(190));

        $this->assertSame(SongRequest::STATUS_PLAYED, $requests['first']->fresh()->status);
        $this->assertSame(SongRequest::STATUS_PLAYING, $requests['second']->fresh()->status);
        $this->assertSame($requests['second']->id, PlaybackState::firstOrFail()->current_request_id);
    }

    public function test_skip_ends_current_and_starts_next(): void
    {
        $eventNight = $this->seedEvent();
        $requests = $this->seedRequests($eventNight);
        $queueEngine = $this->app->make(QueueEngine::class);

        $queueEngine->startNext($eventNight, Carbon::parse('2024-01-01 10:00:00'));
        $queueEngine->skip($eventNight, $requests['first'], Carbon::parse('2024-01-01 10:01:00'));

        $this->assertSame(SongRequest::STATUS_SKIPPED, $requests['first']->fresh()->status);
        $this->assertSame(SongRequest::STATUS_PLAYING, $requests['second']->fresh()->status);
        $this->assertSame($requests['second']->id, PlaybackState::firstOrFail()->current_request_id);
    }

    public function test_pause_freezes_playback(): void
    {
        $eventNight = $this->seedEvent();
        $requests = $this->seedRequests($eventNight);
        $queueEngine = $this->app->make(QueueEngine::class);

        $queueEngine->startNext($eventNight, Carbon::parse('2024-01-01 10:00:00'));
        $queueEngine->pause($eventNight);

        $queueEngine->advanceIfNeeded($eventNight, Carbon::parse('2024-01-01 10:05:00'));

        $playbackState = PlaybackState::firstOrFail();

        $this->assertSame(PlaybackState::STATE_PAUSED, $playbackState->state);
        $this->assertSame($requests['first']->id, $playbackState->current_request_id);
        $this->assertSame(SongRequest::STATUS_PLAYING, $requests['first']->fresh()->status);
        $this->assertSame(SongRequest::STATUS_QUEUED, $requests['second']->fresh()->status);
    }

    public function test_queue_advance_command_processes_live_events(): void
    {
        $eventNight = $this->seedEvent();
        $requests = $this->seedRequests($eventNight);
        $queueEngine = $this->app->make(QueueEngine::class);

        $queueEngine->startNext($eventNight, Carbon::parse('2024-01-01 10:00:00'));

        PlaybackState::firstOrFail()->update([
            'expected_end_at' => Carbon::parse('2024-01-01 10:00:00')->subSecond(),
        ]);

        Artisan::call('queue:advance');

        $this->assertSame(SongRequest::STATUS_PLAYED, $requests['first']->fresh()->status);
        $this->assertSame(SongRequest::STATUS_PLAYING, $requests['second']->fresh()->status);
    }

    private function seedEvent(): EventNight
    {
        $venue = Venue::create([
            'name' => 'Main Stage',
            'timezone' => 'UTC',
        ]);

        return EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENT100',
            'break_seconds' => 10,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addHours(4),
        ]);
    }

    private function seedRequests(EventNight $eventNight): array
    {
        $participant = Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-1',
            'join_token_hash' => hash('sha256', 'token-1'),
        ]);

        $songA = Song::create([
            'title' => 'Song A',
            'artist' => 'Artist A',
            'duration_seconds' => 180,
        ]);

        $songB = Song::create([
            'title' => 'Song B',
            'artist' => 'Artist B',
            'duration_seconds' => 200,
        ]);

        $first = SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $songA->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 1,
        ]);

        $second = SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $songB->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 2,
        ]);

        return [
            'first' => $first,
            'second' => $second,
        ];
    }
}
