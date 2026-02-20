<?php

namespace App\Modules\PublicScreen\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicScreen\Realtime\SseStateStore;
use App\Modules\PublicScreen\Services\PublicScreenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicScreenController extends Controller
{
    public function __construct(
        private readonly PublicScreenService $service,
        private readonly SseStateStore $store
    ) {
    }

    public function show(Request $request, string $eventCode): Response
    {
        $validated = validator(['eventCode' => $eventCode], [
            'eventCode' => ['required', 'string', 'min:4', 'max:12'],
        ])->validate();

        $eventNight = $this->service->findLiveEvent($validated['eventCode']);
        $state = $this->service->buildState($eventNight);

        return response()->view('public.screen', [
            'eventNight' => $eventNight,
            'state' => $state,
            'realtimeEnabled' => $this->isRealtimeEnabled(),
            'pollSeconds' => (int) config('public_screen.poll_seconds', 5),
            'realtimeMaxConsecutiveErrors' => (int) config('public_screen.realtime.max_consecutive_errors', 3),
            'realtimeConnectTimeoutSeconds' => (int) config('public_screen.realtime.connect_timeout_seconds', 15),
        ]);
    }

    public function state(Request $request, string $eventCode): JsonResponse
    {
        $validated = validator(['eventCode' => $eventCode], [
            'eventCode' => ['required', 'string', 'min:4', 'max:12'],
        ])->validate();

        $eventNight = $this->service->findLiveEvent($validated['eventCode']);

        return response()->json($this->service->buildState($eventNight));
    }

    public function stream(Request $request, string $eventCode): StreamedResponse
    {
        $validated = validator(['eventCode' => $eventCode], [
            'eventCode' => ['required', 'string', 'min:4', 'max:12'],
        ])->validate();

        $eventNight = $this->service->findLiveEvent($validated['eventCode']);
        $streamSeconds = (int) config('public_screen.realtime.stream_seconds', 20);
        $sleepSeconds = (int) config('public_screen.realtime.sleep_seconds', 1);
        $headers = [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
        ];

        if (! $this->isRealtimeEnabled()) {
            return response()->stream(function () use ($eventNight) {
                $this->sendEvent('snapshot', $this->service->buildState($eventNight));
            }, 200, $headers);
        }

        return response()->stream(function () use ($eventNight, $streamSeconds, $sleepSeconds) {
            if (function_exists('set_time_limit')) {
                @set_time_limit(0);
            }

            $start = microtime(true);
            $lastUpdate = [
                'playback' => null,
                'queue' => null,
                'theme' => null,
            ];

            $this->sendEvent('snapshot', $this->service->buildState($eventNight));

            while (! connection_aborted() && (microtime(true) - $start) < $streamSeconds) {
                foreach (['playback', 'queue', 'theme'] as $type) {
                    $cached = $this->store->read($eventNight, $type);

                    if (! $cached || ! isset($cached['updated_at_ms'])) {
                        continue;
                    }

                    if ($lastUpdate[$type] === $cached['updated_at_ms']) {
                        continue;
                    }

                    $lastUpdate[$type] = $cached['updated_at_ms'];
                    $this->sendEvent($type, $cached['payload'] ?? []);
                }

                sleep($sleepSeconds);
            }
        }, 200, $headers);
    }

    public function media(string $path): StreamedResponse
    {
        $normalizedPath = trim($path, '/');

        abort_if(
            $normalizedPath === '' || str_contains($normalizedPath, '..') || str_contains($normalizedPath, '\\'),
            404
        );
        abort_unless(Storage::disk('public')->exists($normalizedPath), 404);

        return Storage::disk('public')->response($normalizedPath);
    }

    private function sendEvent(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
        ob_flush();
        flush();
    }

    private function isRealtimeEnabled(): bool
    {
        if (! (bool) config('public_screen.realtime.enabled', true)) {
            return false;
        }

        if ((bool) config('public_screen.realtime.disable_on_cli_server', true) && PHP_SAPI === 'cli-server') {
            return false;
        }

        return true;
    }
}
