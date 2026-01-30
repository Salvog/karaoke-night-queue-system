<?php

namespace App\Modules\PublicJoin\Services;

use App\Models\EventNight;
use App\Models\Participant;
use App\Models\Song;
use App\Models\SongRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicJoinService
{
    public function findLiveEvent(string $eventCode): EventNight
    {
        $eventNight = EventNight::where('code', $eventCode)->firstOrFail();

        if ($eventNight->status !== EventNight::STATUS_LIVE) {
            throw new AuthorizationException('Event is not live.');
        }

        return $eventNight;
    }

    public function resolveParticipant(EventNight $eventNight, string $deviceCookieId): Participant
    {
        return Participant::firstOrCreate(
            [
                'event_night_id' => $eventNight->id,
                'device_cookie_id' => $deviceCookieId,
            ],
            [
                // Simplest deterministic default to satisfy non-null join_token_hash.
                'join_token_hash' => $this->hashToken($this->generateJoinToken()),
            ]
        );
    }

    public function issueJoinToken(Participant $participant): string
    {
        $token = $this->generateJoinToken();

        $participant->update([
            'join_token_hash' => $this->hashToken($token),
        ]);

        return $token;
    }

    public function validatePin(EventNight $eventNight, ?string $pin): void
    {
        if (! $eventNight->join_pin) {
            return;
        }

        if (! $pin || $pin !== $eventNight->join_pin) {
            throw ValidationException::withMessages([
                'pin' => 'The provided PIN is invalid.',
            ]);
        }
    }

    public function requestSong(
        EventNight $eventNight,
        Participant $participant,
        string $joinToken,
        int $songId
    ): SongRequest {
        $this->assertJoinToken($participant, $joinToken);

        $song = Song::findOrFail($songId);

        return DB::transaction(function () use ($eventNight, $participant, $song) {
            $this->enforceCooldown($eventNight, $participant);

            $maxPosition = SongRequest::where('event_night_id', $eventNight->id)->max('position');

            return SongRequest::create([
                'event_night_id' => $eventNight->id,
                'participant_id' => $participant->id,
                'song_id' => $song->id,
                'status' => SongRequest::STATUS_QUEUED,
                'position' => ($maxPosition ?? 0) + 1,
            ]);
        });
    }

    public function generateDeviceCookieId(): string
    {
        return (string) Str::uuid();
    }

    private function generateJoinToken(): string
    {
        return Str::random(32);
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    private function assertJoinToken(Participant $participant, string $joinToken): void
    {
        if (! hash_equals($participant->join_token_hash, $this->hashToken($joinToken))) {
            throw ValidationException::withMessages([
                'join_token' => 'Join token is invalid.',
            ]);
        }
    }

    private function enforceCooldown(EventNight $eventNight, Participant $participant): void
    {
        if ($eventNight->request_cooldown_seconds <= 0) {
            return;
        }

        $latestRequest = SongRequest::where('event_night_id', $eventNight->id)
            ->where('participant_id', $participant->id)
            ->latest('created_at')
            ->first();

        if (! $latestRequest) {
            return;
        }

        $secondsSinceLast = $latestRequest->created_at?->diffInSeconds(now()) ?? PHP_INT_MAX;

        if ($secondsSinceLast >= $eventNight->request_cooldown_seconds) {
            return;
        }

        $remaining = $eventNight->request_cooldown_seconds - $secondsSinceLast;

        throw ValidationException::withMessages([
            'cooldown' => "Please wait {$remaining} seconds before requesting another song.",
        ]);
    }
}
