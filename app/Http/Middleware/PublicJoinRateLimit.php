<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class PublicJoinRateLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        $eventCode = (string) $request->route('eventCode');
        $deviceCookieName = config('public_join.device_cookie_name', 'device_cookie_id');
        $deviceCookieId = $request->cookie($deviceCookieName);

        $ipKey = sprintf('public-join:ip:%s:%s', $eventCode, $request->ip());
        $participantKey = $deviceCookieId
            ? sprintf('public-join:participant:%s:%s', $eventCode, $deviceCookieId)
            : null;

        $maxIp = (int) config('public_join.rate_limit_per_ip', 20);
        $maxParticipant = (int) config('public_join.rate_limit_per_participant', 10);
        $decaySeconds = (int) config('public_join.rate_limit_decay_seconds', 60);

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

    private function tooManyAttemptsResponse(Request $request): Response
    {
        $message = 'Too many requests. Please slow down.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 429);
        }

        return response($message, 429);
    }
}
