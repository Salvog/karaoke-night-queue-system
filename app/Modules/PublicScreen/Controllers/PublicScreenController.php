<?php

namespace App\Modules\PublicScreen\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicScreen\Realtime\SseStateStore;
use App\Modules\PublicScreen\Services\PublicScreenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            'realtimeEnabled' => (bool) config('public_screen.realtime.enabled', true),
            'pollSeconds' => (int) config('public_screen.poll_seconds', 5),
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

        return response()->stream(function () use ($eventNight, $streamSeconds, $sleepSeconds) {
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
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function sendEvent(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
        ob_flush();
        flush();
    }
}
