<?php

namespace App\Modules\PublicScreen\Realtime;

use App\Models\EventNight;
use App\Modules\PublicScreen\Services\PublicScreenService;

class SseRealtimePublisher implements RealtimePublisher
{
    public function __construct(
        private readonly SseStateStore $store,
        private readonly PublicScreenService $service
    ) {
    }

    public function publishPlaybackUpdated(EventNight $eventNight): void
    {
        $payload = $this->service->buildPlaybackPayload($eventNight);
        $this->store->write($eventNight, 'playback', $payload);
    }

    public function publishQueueUpdated(EventNight $eventNight): void
    {
        $payload = $this->service->buildQueuePayload($eventNight);
        $this->store->write($eventNight, 'queue', $payload);
    }

    public function publishThemeUpdated(EventNight $eventNight): void
    {
        $payload = $this->service->buildThemePayload($eventNight);
        $this->store->write($eventNight, 'theme', $payload);
    }
}
