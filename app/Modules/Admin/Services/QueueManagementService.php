<?php

namespace App\Modules\Admin\Services;

use App\Models\EventNight;
use App\Models\Participant;
use App\Models\Song;
use App\Models\SongRequest;
use App\Modules\PublicScreen\Realtime\RealtimePublisher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueueManagementService
{
    public function __construct(private readonly RealtimePublisher $publisher)
    {
    }

    public function addManualRequest(EventNight $eventNight, string $displayName, int $songId): SongRequest
    {
        $song = Song::findOrFail($songId);

        $songRequest = DB::transaction(function () use ($eventNight, $displayName, $song) {
            $participant = Participant::create([
                'event_night_id' => $eventNight->id,
                'device_cookie_id' => (string) Str::uuid(),
                'join_token_hash' => hash('sha256', Str::random(32)),
                'display_name' => $displayName,
                'pin_verified_at' => $eventNight->join_pin ? now() : null,
            ]);

            $maxPosition = SongRequest::where('event_night_id', $eventNight->id)->max('position');

            return SongRequest::create([
                'event_night_id' => $eventNight->id,
                'participant_id' => $participant->id,
                'song_id' => $song->id,
                'status' => SongRequest::STATUS_QUEUED,
                'position' => ($maxPosition ?? 0) + 1,
            ]);
        });

        $this->publisher->publishQueueUpdated($eventNight);

        return $songRequest;
    }
}
