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
            throw new AuthorizationException('La serata non è attiva.');
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
                'pin' => 'PIN non valido. Controlla e riprova.',
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
        int $songId,
        string $displayName
    ): SongRequest {
        $this->assertJoinToken($participant, $joinToken);
        $this->assertPinVerified($eventNight, $participant);

        $normalizedDisplayName = $this->normalizeDisplayName($displayName);
        $participant->forceFill([
            'display_name' => $normalizedDisplayName,
        ])->save();

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
                'join_token' => 'Sessione non valida. Ricarica la pagina e riprova.',
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
                'pin' => 'Per prenotare devi prima attivarti con il PIN.',
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
                'song_id' => 'Hai già prenotato questo brano per questa serata.',
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
        $remainingMinutes = (int) ceil($remaining / 60);
        $minuteLabel = $remainingMinutes === 1 ? 'minuto' : 'minuti';

        throw ValidationException::withMessages([
            'cooldown' => "Attendi {$remainingMinutes} {$minuteLabel} prima di prenotare un altro brano.",
        ]);
    }

    private function normalizeDisplayName(string $displayName): string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $displayName) ?? '');

        if ($normalized === '') {
            throw ValidationException::withMessages([
                'display_name' => 'Inserisci il tuo nome prima di prenotare.',
            ]);
        }

        return $normalized;
    }
}
