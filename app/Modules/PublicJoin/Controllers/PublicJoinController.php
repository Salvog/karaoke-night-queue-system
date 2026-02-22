<?php

namespace App\Modules\PublicJoin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EventNight;
use App\Models\Participant;
use App\Models\PlaybackState;
use App\Models\Song;
use App\Models\SongRequest;
use App\Modules\PublicJoin\Services\PublicJoinService;
use App\Modules\PublicJoin\Services\RequestEtaService;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PublicJoinController extends Controller
{
    public function __construct(private readonly PublicJoinService $service)
    {
    }

    public function show(Request $request, string $eventCode): Response
    {
        $deviceCookieName = config('public_join.device_cookie_name', 'device_cookie_id');
        $deviceCookieId = $request->cookie($deviceCookieName);
        $shouldSetCookie = false;

        if (! $deviceCookieId) {
            $deviceCookieId = $this->service->generateDeviceCookieId();
            $shouldSetCookie = true;
        }

        $eventNight = $this->service->findLiveEvent($eventCode)->loadMissing(['venue', 'playbackState']);
        $participant = $this->service->resolveParticipant($eventNight, $deviceCookieId);
        $joinToken = $this->service->issueJoinToken($participant);

        $response = response()->view('public.landing', [
            'eventNight' => $eventNight,
            'joinToken' => $joinToken,
            'participantName' => $participant->display_name,
            'myRequestsInitialPayload' => $this->buildMyRequestsPayload($eventNight, $participant),
        ]);

        if ($shouldSetCookie) {
            $response->withCookie(cookie(
                $deviceCookieName,
                $deviceCookieId,
                60 * 24 * 365,
                '/',
                null,
                $request->isSecure(),
                false
            ));
        }

        return $response;
    }

    public function activate(Request $request, string $eventCode): RedirectResponse
    {
        $data = $request->validate(
            [
                'pin' => ['nullable', 'string', 'max:20'],
            ],
            [
                'pin.max' => 'Il PIN può contenere al massimo 20 caratteri.',
            ]
        );

        $deviceCookieId = $this->requireDeviceCookie($request);
        $eventNight = $this->service->findLiveEvent($eventCode);
        $participant = $this->service->resolveParticipant($eventNight, $deviceCookieId);
        $this->service->activateParticipant($eventNight, $participant, $data['pin'] ?? null);

        return back()->with('status', 'Accesso confermato. Ora puoi prenotare i brani.');
    }

    public function requestSong(Request $request, string $eventCode): RedirectResponse
    {
        $data = $request->validate(
            [
                'song_id' => ['required', 'integer', 'exists:songs,id'],
                'join_token' => ['required', 'string', 'min:8', 'max:64'],
                'display_name' => ['required', 'string', 'min:2', 'max:80'],
            ],
            [
                'song_id.required' => 'Seleziona una canzone da prenotare.',
                'song_id.integer' => 'La canzone selezionata non è valida.',
                'song_id.exists' => 'La canzone selezionata non è disponibile.',
                'join_token.required' => 'Sessione non valida. Ricarica la pagina e riprova.',
                'join_token.string' => 'Sessione non valida. Ricarica la pagina e riprova.',
                'join_token.min' => 'Sessione non valida. Ricarica la pagina e riprova.',
                'join_token.max' => 'Sessione non valida. Ricarica la pagina e riprova.',
                'display_name.required' => 'Inserisci il tuo nome prima di prenotare.',
                'display_name.string' => 'Il nome inserito non è valido.',
                'display_name.min' => 'Inserisci almeno 2 caratteri per il nome.',
                'display_name.max' => 'Il nome può contenere al massimo 80 caratteri.',
            ]
        );

        $deviceCookieId = $this->requireDeviceCookie($request);
        $eventNight = $this->service->findLiveEvent($eventCode);
        $participant = $this->service->resolveParticipant($eventNight, $deviceCookieId);

        $this->service->requestSong(
            $eventNight,
            $participant,
            $data['join_token'],
            (int) $data['song_id'],
            $data['display_name']
        );

        return back()->with('status', 'Prenotazione confermata. Controlla la sezione "Le tue prenotazioni".');
    }

    public function searchSongs(Request $request, string $eventCode): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $this->service->findLiveEvent($eventCode);

        $term = $data['q'] ?? null;
        $perPage = $data['per_page'] ?? 10;
        $page = $data['page'] ?? 1;

        $query = Song::query()->select(['id', 'title', 'artist', 'duration_seconds']);

        if ($term !== null && $term !== '') {
            $term = Str::lower($term);
            $like = '%' . $term . '%';

            $query->where(function ($builder) use ($like) {
                $builder->whereRaw('LOWER(title) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(artist) LIKE ?', [$like]);
            });
        }

        $songs = $query->orderBy('title')
            ->paginate($perPage, ['id', 'title', 'artist', 'duration_seconds'], 'page', $page);

        return response()->json([
            'data' => $songs->items(),
            'meta' => [
                'current_page' => $songs->currentPage(),
                'last_page' => $songs->lastPage(),
                'per_page' => $songs->perPage(),
                'total' => $songs->total(),
            ],
        ]);
    }

    public function eta(
        Request $request,
        string $eventCode,
        RequestEtaService $etaService
    ): JsonResponse {
        $data = $request->validate(
            [
                'join_token' => ['required', 'string', 'min:8', 'max:64'],
            ],
            [
                'join_token.required' => 'Sessione non valida. Ricarica la pagina e riprova.',
                'join_token.string' => 'Sessione non valida. Ricarica la pagina e riprova.',
                'join_token.min' => 'Sessione non valida. Ricarica la pagina e riprova.',
                'join_token.max' => 'Sessione non valida. Ricarica la pagina e riprova.',
            ]
        );

        $eventNight = $this->service->findLiveEvent($eventCode);
        $participant = $this->resolveParticipantForRead($eventNight, $request, $data['join_token']);
        $this->service->validateJoinToken($participant, $data['join_token']);

        $etaSeconds = $etaService->calculateSeconds($eventNight);

        return response()->json([
            'eta_seconds' => $etaSeconds,
            'eta_label' => $this->formatEta($etaSeconds),
        ]);
    }

    public function myRequests(Request $request, string $eventCode): JsonResponse
    {
        $data = $request->validate(
            [
                'join_token' => ['required', 'string', 'min:8', 'max:64'],
            ],
            [
                'join_token.required' => 'Sessione non valida. Ricarica la pagina e riprova.',
                'join_token.string' => 'Sessione non valida. Ricarica la pagina e riprova.',
                'join_token.min' => 'Sessione non valida. Ricarica la pagina e riprova.',
                'join_token.max' => 'Sessione non valida. Ricarica la pagina e riprova.',
            ]
        );

        $eventNight = $this->service->findLiveEvent($eventCode)->loadMissing(['venue', 'playbackState']);
        $participant = $this->resolveParticipantForRead($eventNight, $request, $data['join_token']);
        $this->service->validateJoinToken($participant, $data['join_token']);

        return response()->json($this->buildMyRequestsPayload($eventNight, $participant));
    }

    private function buildMyRequestsPayload(EventNight $eventNight, Participant $participant): array
    {
        $now = now();
        $eventNight->loadMissing(['venue', 'playbackState']);
        $timezone = $eventNight->venue?->timezone ?? config('app.timezone', 'Europe/Rome');
        $playbackState = $eventNight->playbackState?->state ?? PlaybackState::STATE_IDLE;
        $queuedEtaByRequestId = $this->buildQueuedEtaByRequestId($eventNight, $now);

        $activeRequests = SongRequest::query()
            ->where('event_night_id', $eventNight->id)
            ->where('participant_id', $participant->id)
            ->whereIn('status', [SongRequest::STATUS_PLAYING, SongRequest::STATUS_QUEUED])
            ->with('song:id,title,artist,duration_seconds')
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [SongRequest::STATUS_PLAYING])
            ->orderByRaw('position is null')
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $historyRequests = SongRequest::query()
            ->where('event_night_id', $eventNight->id)
            ->where('participant_id', $participant->id)
            ->whereIn('status', [SongRequest::STATUS_PLAYED, SongRequest::STATUS_SKIPPED, SongRequest::STATUS_CANCELED])
            ->with('song:id,title,artist,duration_seconds')
            ->orderByRaw('COALESCE(played_at, updated_at) desc')
            ->orderByDesc('id')
            ->get();

        $requests = $activeRequests->concat($historyRequests)->values();

        return [
            'data' => $requests->map(fn (SongRequest $songRequest) => $this->serializeMyRequest(
                $songRequest,
                $queuedEtaByRequestId,
                $now,
                $timezone,
                $playbackState
            ))->values()->all(),
            'meta' => [
                'count' => $requests->count(),
                'playback_state' => $playbackState,
                'updated_at' => $now->toIso8601String(),
            ],
        ];
    }

    /**
     * @return array<int, int>
     */
    private function buildQueuedEtaByRequestId(EventNight $eventNight, CarbonInterface $now): array
    {
        $offsetSeconds = $this->resolveQueueOffsetSeconds($eventNight, $now);

        $queuedRequests = SongRequest::query()
            ->where('event_night_id', $eventNight->id)
            ->where('status', SongRequest::STATUS_QUEUED)
            ->with('song:id,duration_seconds')
            ->orderByRaw('position is null')
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $etaByRequestId = [];

        foreach ($queuedRequests as $queuedRequest) {
            $etaByRequestId[$queuedRequest->id] = $offsetSeconds;
            $offsetSeconds += max(0, (int) $queuedRequest->song?->duration_seconds) + max(0, (int) $eventNight->break_seconds);
        }

        return $etaByRequestId;
    }

    private function resolveQueueOffsetSeconds(EventNight $eventNight, CarbonInterface $now): int
    {
        $playbackState = $eventNight->playbackState;

        if (! $playbackState || ! $playbackState->expected_end_at) {
            return 0;
        }

        if ($playbackState->state === PlaybackState::STATE_PLAYING) {
            return max(0, $now->diffInSeconds($playbackState->expected_end_at, false));
        }

        if ($playbackState->state === PlaybackState::STATE_PAUSED) {
            if ($playbackState->paused_at) {
                return max(0, $playbackState->paused_at->diffInSeconds($playbackState->expected_end_at, false));
            }

            return max(0, $now->diffInSeconds($playbackState->expected_end_at, false));
        }

        return 0;
    }

    /**
     * @param  array<int, int>  $queuedEtaByRequestId
     */
    private function serializeMyRequest(
        SongRequest $songRequest,
        array $queuedEtaByRequestId,
        CarbonInterface $now,
        string $timezone,
        string $playbackState
    ): array {
        $etaSeconds = null;
        $etaLabel = null;
        $scheduledAt = null;
        $scheduledAtLabel = null;

        if ($songRequest->status === SongRequest::STATUS_QUEUED && $playbackState !== PlaybackState::STATE_PAUSED) {
            if (array_key_exists($songRequest->id, $queuedEtaByRequestId)) {
                $etaSeconds = (int) $queuedEtaByRequestId[$songRequest->id];
                $etaLabel = $this->formatEta($etaSeconds);
                $scheduledAt = $now->copy()->addSeconds($etaSeconds)->toIso8601String();
                $scheduledAtLabel = $this->resolveClockLabel($now->copy()->addSeconds($etaSeconds), $timezone);
            }
        }

        $playedAt = $songRequest->played_at ?? $songRequest->updated_at;

        return [
            'id' => $songRequest->id,
            'title' => $songRequest->song?->title ?? 'Brano non disponibile',
            'artist' => $songRequest->song?->artist,
            'status' => $songRequest->status,
            'status_label' => $this->statusLabel($songRequest->status),
            'queue_position' => $songRequest->status === SongRequest::STATUS_QUEUED ? $songRequest->position : null,
            'requested_at' => $songRequest->created_at?->toIso8601String(),
            'requested_at_label' => $this->resolveClockLabel($songRequest->created_at, $timezone),
            'played_at' => $playedAt?->toIso8601String(),
            'played_at_label' => $this->resolveClockLabel($playedAt, $timezone),
            'eta_seconds' => $etaSeconds,
            'eta_label' => $etaLabel,
            'scheduled_at' => $scheduledAt,
            'scheduled_at_label' => $scheduledAtLabel,
            'timeline_note' => $this->buildTimelineNote(
                $songRequest,
                $etaSeconds,
                $scheduledAtLabel,
                $playbackState,
                $timezone
            ),
        ];
    }

    private function buildTimelineNote(
        SongRequest $songRequest,
        ?int $etaSeconds,
        ?string $scheduledAtLabel,
        string $playbackState,
        string $timezone
    ): string {
        if ($songRequest->status === SongRequest::STATUS_PLAYING) {
            return 'Sei sul palco adesso.';
        }

        if ($songRequest->status === SongRequest::STATUS_QUEUED) {
            if ($playbackState === PlaybackState::STATE_PAUSED) {
                return 'La serata è in pausa: la stima riprenderà quando lo staff riavvia la coda.';
            }

            if ($etaSeconds === null) {
                return 'Stima non disponibile al momento. Ricarica la pagina per aggiornare la tua posizione.';
            }

            if ($etaSeconds <= 0) {
                return 'Sei il prossimo a cantare.';
            }

            if ($scheduledAtLabel) {
                return sprintf('Canterai %s (circa alle %s).', Str::lower($this->formatEta($etaSeconds)), $scheduledAtLabel);
            }

            return sprintf('Canterai %s.', Str::lower($this->formatEta($etaSeconds)));
        }

        if ($songRequest->status === SongRequest::STATUS_PLAYED) {
            $playedAtLabel = $this->resolveClockLabel($songRequest->played_at ?? $songRequest->updated_at, $timezone);

            if ($playedAtLabel) {
                return sprintf('Hai già cantato alle %s.', $playedAtLabel);
            }

            return 'Hai già cantato.';
        }

        if ($songRequest->status === SongRequest::STATUS_SKIPPED) {
            return 'Brano saltato dallo staff.';
        }

        if ($songRequest->status === SongRequest::STATUS_CANCELED) {
            return 'Prenotazione annullata dallo staff.';
        }

        return 'Stato prenotazione aggiornato.';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            SongRequest::STATUS_QUEUED => 'In coda',
            SongRequest::STATUS_PLAYING => 'In esibizione',
            SongRequest::STATUS_PLAYED => 'Cantata',
            SongRequest::STATUS_SKIPPED => 'Saltata',
            SongRequest::STATUS_CANCELED => 'Annullata',
            default => ucfirst($status),
        };
    }

    private function resolveClockLabel(?CarbonInterface $timestamp, string $timezone): ?string
    {
        if (! $timestamp) {
            return null;
        }

        try {
            return $timestamp->copy()->setTimezone($timezone)->format('H:i');
        } catch (\Throwable) {
            return $timestamp->format('H:i');
        }
    }

    private function formatEta(int $seconds): string
    {
        if ($seconds <= 0) {
            return 'a brevissimo';
        }

        return 'tra ' . $this->formatDuration($seconds);
    }

    private function formatDuration(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes <= 0) {
            return sprintf('%d %s', $remainingSeconds, $remainingSeconds === 1 ? 'secondo' : 'secondi');
        }

        if ($remainingSeconds <= 0) {
            return sprintf('%d %s', $minutes, $minutes === 1 ? 'minuto' : 'minuti');
        }

        return sprintf(
            '%d %s e %d %s',
            $minutes,
            $minutes === 1 ? 'minuto' : 'minuti',
            $remainingSeconds,
            $remainingSeconds === 1 ? 'secondo' : 'secondi'
        );
    }

    private function requireDeviceCookie(Request $request): string
    {
        $deviceCookieName = config('public_join.device_cookie_name', 'device_cookie_id');
        $deviceCookieId = $request->cookie($deviceCookieName);

        abort_unless($deviceCookieId, 403, 'Sessione non valida. Ricarica la pagina e riprova.');

        return $deviceCookieId;
    }

    private function resolveParticipantForRead(
        EventNight $eventNight,
        Request $request,
        string $joinToken
    ): Participant {
        $deviceCookieName = config('public_join.device_cookie_name', 'device_cookie_id');
        $deviceCookieId = $request->cookie($deviceCookieName);

        if ($deviceCookieId) {
            return $this->service->resolveParticipant($eventNight, $deviceCookieId);
        }

        $participant = Participant::query()
            ->where('event_night_id', $eventNight->id)
            ->where('join_token_hash', hash('sha256', $joinToken))
            ->first();

        abort_unless($participant, 403, 'Sessione non valida. Ricarica la pagina e riprova.');

        return $participant;
    }
}
