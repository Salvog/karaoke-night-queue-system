<?php

namespace App\Modules\PublicJoin\Services;

use App\Models\EventNight;
use App\Models\Participant;
use App\Models\Song;
use App\Models\SongRequest;
use App\Modules\PublicScreen\Realtime\RealtimePublisher;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicJoinService
{
    public function __construct(private readonly RealtimePublisher $publisher)
    {
    }

    public function findLiveEvent(string $eventCode): EventNight
    {
        $eventNight = EventNight::where('code', $eventCode)->firstOrFail();

        if ($eventNight->status !== EventNight::STATUS_ACTIVE) {
            throw new AuthorizationException('Event is not active.');
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

    public function activateParticipant(EventNight $eventNight, Participant $participant, ?string $pin): void
    {
        $this->validatePin($eventNight, $pin);

        if (! $eventNight->join_pin) {
            return;
        }

        $participant->forceFill([
            'pin_verified_at' => now(),
        ])->save();
    }

    public function requestSong(
        EventNight $eventNight,
        Participant $participant,
        string $joinToken,
        int $songId
    ): SongRequest {
        $this->assertJoinToken($participant, $joinToken);
        $this->assertPinVerified($eventNight, $participant);

        $song = Song::findOrFail($songId);

        $songRequest = DB::transaction(function () use ($eventNight, $participant, $song) {
            $this->enforceCooldown($eventNight, $participant);
            $this->enforceUniqueSong($eventNight, $participant, $song);

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

    public function validateJoinToken(Participant $participant, string $joinToken): void
    {
        $this->assertJoinToken($participant, $joinToken);
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

    private function assertPinVerified(EventNight $eventNight, Participant $participant): void
    {
        if (! $eventNight->join_pin) {
            return;
        }

        if (! $participant->pin_verified_at) {
            throw ValidationException::withMessages([
                'pin' => 'PIN activation required.',
            ]);
        }
    }

    private function enforceUniqueSong(EventNight $eventNight, Participant $participant, Song $song): void
    {
        $alreadyRequested = SongRequest::where('event_night_id', $eventNight->id)
            ->where('participant_id', $participant->id)
            ->where('song_id', $song->id)
            ->exists();

        if ($alreadyRequested) {
            throw ValidationException::withMessages([
                'song_id' => 'You already requested this song for tonight.',
            ]);
        }
    }

    private function enforceCooldown(EventNight $eventNight, Participant $participant): void
    {
        $cooldownMinutes = (int) $eventNight->request_cooldown_seconds;

        if ($cooldownMinutes <= 0) {
            return;
        }

        $cooldownSeconds = $cooldownMinutes * 60;

        $latestRequest = SongRequest::where('event_night_id', $eventNight->id)
            ->where('participant_id', $participant->id)
            ->latest('created_at')
            ->first();

        if (! $latestRequest) {
            return;
        }

        $secondsSinceLast = $latestRequest->created_at?->diffInSeconds(now()) ?? PHP_INT_MAX;

        if ($secondsSinceLast >= $cooldownSeconds) {
            return;
        }

        $remainingSeconds = $cooldownSeconds - $secondsSinceLast;
        $remainingMinutes = (int) ceil($remainingSeconds / 60);

        throw ValidationException::withMessages([
            'cooldown' => "Please wait {$remainingMinutes} minutes before requesting another song.",
        ]);
    }
}
