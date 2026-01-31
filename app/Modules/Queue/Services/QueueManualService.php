<?php

namespace App\Modules\Queue\Services;

use App\Models\EventNight;
use App\Models\Participant;
use App\Models\SongRequest;
use App\Modules\PublicScreen\Realtime\RealtimePublisher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueueManualService
{
    public function __construct(private readonly RealtimePublisher $publisher)
    {
    }

    public function addParticipantRequest(EventNight $eventNight, string $displayName, int $songId): SongRequest
    {
        $songRequest = DB::transaction(function () use ($eventNight, $displayName, $songId) {
            $participant = Participant::create([
                'event_night_id' => $eventNight->id,
                'device_cookie_id' => (string) Str::uuid(),
                'join_token_hash' => hash('sha256', Str::random(32)),
                'display_name' => $displayName,
            ]);

            $maxPosition = SongRequest::where('event_night_id', $eventNight->id)->max('position');

            return SongRequest::create([
                'event_night_id' => $eventNight->id,
                'participant_id' => $participant->id,
                'song_id' => $songId,
                'status' => SongRequest::STATUS_QUEUED,
                'position' => ($maxPosition ?? 0) + 1,
            ]);
        });

        $this->publisher->publishQueueUpdated($eventNight);

        return $songRequest;
    }
}
