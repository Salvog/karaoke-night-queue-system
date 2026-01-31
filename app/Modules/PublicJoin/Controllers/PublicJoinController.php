<?php

namespace App\Modules\PublicJoin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Modules\PublicJoin\Services\RequestEtaService;
use App\Modules\PublicJoin\Services\PublicJoinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
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

        $eventNight = $this->service->findLiveEvent($eventCode)->load('venue');
        $participant = $this->service->resolveParticipant($eventNight, $deviceCookieId);
        $joinToken = $this->service->issueJoinToken($participant);

        $response = response()->view('public.landing', [
            'eventNight' => $eventNight,
            'joinToken' => $joinToken,
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
        $data = $request->validate([
            'pin' => ['nullable', 'string', 'max:20'],
        ]);

        $deviceCookieId = $this->requireDeviceCookie($request);
        $eventNight = $this->service->findLiveEvent($eventCode);
        $participant = $this->service->resolveParticipant($eventNight, $deviceCookieId);
        $this->service->activateParticipant($eventNight, $participant, $data['pin'] ?? null);

        return back()->with('status', 'Access granted.');
    }

    public function requestSong(Request $request, string $eventCode): RedirectResponse
    {
        $data = $request->validate([
            'song_id' => ['required', 'integer', 'exists:songs,id'],
            'join_token' => ['required', 'string', 'min:8', 'max:64'],
        ]);

        $deviceCookieId = $this->requireDeviceCookie($request);
        $eventNight = $this->service->findLiveEvent($eventCode);
        $participant = $this->service->resolveParticipant($eventNight, $deviceCookieId);

        $this->service->requestSong(
            $eventNight,
            $participant,
            $data['join_token'],
            (int) $data['song_id']
        );

        return back()->with('status', 'Song requested.');
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
        $data = $request->validate([
            'join_token' => ['required', 'string', 'min:8', 'max:64'],
        ]);

        $deviceCookieId = $this->requireDeviceCookie($request);
        $eventNight = $this->service->findLiveEvent($eventCode);
        $participant = $this->service->resolveParticipant($eventNight, $deviceCookieId);
        $this->service->validateJoinToken($participant, $data['join_token']);

        $etaSeconds = $etaService->calculateSeconds($eventNight);

        return response()->json([
            'eta_seconds' => $etaSeconds,
            'eta_label' => $this->formatEta($etaSeconds),
        ]);
    }

    private function formatEta(int $seconds): string
    {
        if ($seconds <= 0) {
            return 'Ready soon';
        }

        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes <= 0) {
            return sprintf('%d sec', $remainingSeconds);
        }

        return sprintf('%d min %d sec', $minutes, $remainingSeconds);
    }

    private function requireDeviceCookie(Request $request): string
    {
        $deviceCookieName = config('public_join.device_cookie_name', 'device_cookie_id');
        $deviceCookieId = $request->cookie($deviceCookieName);

        abort_unless($deviceCookieId, 403, 'Missing device cookie.');

        return $deviceCookieId;
    }
}
