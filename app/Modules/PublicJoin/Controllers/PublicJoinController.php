<?php

namespace App\Modules\PublicJoin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Modules\PublicJoin\Services\PublicJoinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

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
        $songs = Song::orderBy('title')->get();

        $response = response()->view('public.landing', [
            'eventNight' => $eventNight,
            'songs' => $songs,
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

    private function requireDeviceCookie(Request $request): string
    {
        $deviceCookieName = config('public_join.device_cookie_name', 'device_cookie_id');
        $deviceCookieId = $request->cookie($deviceCookieName);

        abort_unless($deviceCookieId, 403, 'Missing device cookie.');

        return $deviceCookieId;
    }
}
