<?php

namespace App\Modules\Queue\Services;

use App\Models\EventNight;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class QueueAutoAdvanceService
{
    private const LOCK_SECONDS = 1;

    public function __construct(private readonly QueueEngine $queueEngine)
    {
    }

    public function ensureAdvanced(EventNight $eventNight, ?Carbon $now = null): void
    {
        if ($eventNight->status !== EventNight::STATUS_ACTIVE) {
            return;
        }

        $now = $now ?? now();
        $lockKey = $this->lockKey($eventNight);

        // Best-effort short lock to avoid duplicate auto-advance work on concurrent HTTP hits.
        if (! Cache::add($lockKey, $now->getTimestamp(), now()->addSeconds(self::LOCK_SECONDS))) {
            return;
        }

        try {
            $this->queueEngine->advanceIfNeeded($eventNight, $now);
        } finally {
            Cache::forget($lockKey);
        }
    }

    private function lockKey(EventNight $eventNight): string
    {
        return "queue:auto-advance:{$eventNight->id}";
    }
}
