<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class PublicJoinRateLimit
{
    public function handle(Request $request, Closure $next, string $scope = 'write'): Response
    {
        $eventCode = (string) $request->route('eventCode');
        $deviceCookieName = config('public_join.device_cookie_name', 'device_cookie_id');
        $deviceCookieId = $request->cookie($deviceCookieName);
        $joinToken = (string) $request->input('join_token', '');

        $bucket = $scope === 'read' ? 'read' : 'write';

        $ipKey = sprintf('public-join:%s:ip:%s:%s', $bucket, $eventCode, $request->ip());

        $identityKeys = [];

        if ($deviceCookieId !== '') {
            $identityKeys[] = sprintf('public-join:%s:participant-device:%s:%s', $bucket, $eventCode, $deviceCookieId);
        }

        if ($joinToken !== '') {
            $identityKeys[] = sprintf('public-join:%s:participant-token:%s:%s', $bucket, $eventCode, hash('sha256', $joinToken));
        }

        $identityKeys = array_values(array_unique($identityKeys));

        $maxIp = (int) config("public_join.rate_limit_{$bucket}_per_ip", config('public_join.rate_limit_per_ip', 20));
        $maxParticipant = (int) config("public_join.rate_limit_{$bucket}_per_participant", config('public_join.rate_limit_per_participant', 10));
        $decaySeconds = (int) config("public_join.rate_limit_{$bucket}_decay_seconds", config('public_join.rate_limit_decay_seconds', 60));

        if (RateLimiter::tooManyAttempts($ipKey, $maxIp)) {
            return $this->tooManyAttemptsResponse($request);
        }

        foreach ($identityKeys as $identityKey) {
            if (RateLimiter::tooManyAttempts($identityKey, $maxParticipant)) {
                return $this->tooManyAttemptsResponse($request);
            }
        }

        RateLimiter::hit($ipKey, $decaySeconds);

        foreach ($identityKeys as $identityKey) {
            RateLimiter::hit($identityKey, $decaySeconds);
        }

        return $next($request);
    }

    private function tooManyAttemptsResponse(Request $request): Response|RedirectResponse
    {
        $message = 'Troppe richieste in poco tempo. Attendi qualche secondo e riprova.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 429);
        }

        return back()->withErrors(['rate_limit' => $message]);
    }
}
