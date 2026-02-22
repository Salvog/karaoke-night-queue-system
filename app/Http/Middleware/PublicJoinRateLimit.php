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

        $bucket = $scope === 'read' ? 'read' : 'write';

        $ipKey = sprintf('public-join:%s:ip:%s:%s', $bucket, $eventCode, $request->ip());
        $participantKey = $deviceCookieId
            ? sprintf('public-join:%s:participant:%s:%s', $bucket, $eventCode, $deviceCookieId)
            : null;

        $maxIp = (int) config("public_join.rate_limit_{$bucket}_per_ip", config('public_join.rate_limit_per_ip', 20));
        $maxParticipant = (int) config("public_join.rate_limit_{$bucket}_per_participant", config('public_join.rate_limit_per_participant', 10));
        $decaySeconds = (int) config("public_join.rate_limit_{$bucket}_decay_seconds", config('public_join.rate_limit_decay_seconds', 60));

        if (RateLimiter::tooManyAttempts($ipKey, $maxIp)) {
            return $this->tooManyAttemptsResponse($request);
        }

        if ($participantKey && RateLimiter::tooManyAttempts($participantKey, $maxParticipant)) {
            return $this->tooManyAttemptsResponse($request);
        }

        RateLimiter::hit($ipKey, $decaySeconds);

        if ($participantKey) {
            RateLimiter::hit($participantKey, $decaySeconds);
        }

        return $next($request);
    }

    private function tooManyAttemptsResponse(Request $request): Response|RedirectResponse
    {
        $message = 'Troppe richieste in poco tempo. Attendi qualche secondo e riprova.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 429);
        }

        return back()
            ->withErrors(['rate_limit' => $message])
            ->setStatusCode(429);
    }
}
