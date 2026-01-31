<?php

namespace App\Modules\Admin\Services;

use App\Models\EventNight;
use App\Models\Participant;
use App\Models\Song;
use App\Models\SongRequest;
use App\Modules\PublicScreen\Realtime\RealtimePublisher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManualQueueService
{
    public function __construct(private readonly RealtimePublisher $publisher)
    {
    }

    public function addManualRequest(EventNight $eventNight, string $participantName, int $songId): SongRequest
    {
        $participantName = trim($participantName);
        $song = Song::findOrFail($songId);

        $songRequest = DB::transaction(function () use ($eventNight, $participantName, $song) {
            $participant = $this->findOrCreateParticipant($eventNight, $participantName);

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

    private function findOrCreateParticipant(EventNight $eventNight, string $participantName): Participant
    {
        $existingParticipant = Participant::where('event_night_id', $eventNight->id)
            ->where('display_name', $participantName)
            ->first();

        if ($existingParticipant) {
            return $existingParticipant;
        }

        $joinToken = Str::random(32);

        return Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => (string) Str::uuid(),
            'join_token_hash' => hash('sha256', $joinToken),
            'display_name' => $participantName,
        ]);
    }
}
