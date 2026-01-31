<?php

namespace App\Modules\PublicScreen\Realtime;

use App\Models\EventNight;

interface RealtimePublisher
{
    public function publishPlaybackUpdated(EventNight $eventNight): void;

    public function publishQueueUpdated(EventNight $eventNight): void;

    public function publishThemeUpdated(EventNight $eventNight): void;
}
