<?php

namespace App\Modules\Queue\Services;

use App\Models\EventNight;

class QueueAutoAdvanceService
{
    public function __construct(private readonly QueueEngine $queueEngine)
    {
    }

    public function advanceForEventIfNeeded(EventNight $eventNight): void
    {
        $this->queueEngine->advanceIfNeeded($eventNight);
    }
}
