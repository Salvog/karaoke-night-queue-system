<?php

namespace App\Console\Commands;

use App\Models\EventNight;
use App\Modules\Queue\Services\QueueEngine;
use Illuminate\Console\Command;

class RunQueueEngine extends Command
{
    protected $signature = 'queue:advance';

    protected $description = 'Advance playback for active events when expected end times are reached.';

    public function handle(QueueEngine $queueEngine): int
    {
        $now = now();

        EventNight::where('status', EventNight::STATUS_ACTIVE)
            ->select('id')
            ->chunkById(100, function ($events) use ($queueEngine, $now) {
                foreach ($events as $eventNight) {
                    $queueEngine->advanceIfNeeded($eventNight, $now);
                }
            });

        return self::SUCCESS;
    }
}
