<?php

namespace App\Modules\PublicScreen\Realtime;

use App\Models\EventNight;

class NullRealtimePublisher implements RealtimePublisher
{
    public function publishPlaybackUpdated(EventNight $eventNight): void
    {
        // Intentionally no-op for disabled realtime.
    }

    public function publishQueueUpdated(EventNight $eventNight): void
    {
        // Intentionally no-op for disabled realtime.
    }

    public function publishThemeUpdated(EventNight $eventNight): void
    {
        // Intentionally no-op for disabled realtime.
    }
}
