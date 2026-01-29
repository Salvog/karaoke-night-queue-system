<?php

namespace App\Modules\Queue\Services;

class NullRealtimeBroadcaster implements RealtimeBroadcasterInterface
{
    public function broadcast(string $channel, string $event, array $payload = []): void
    {
        // Intentionally left blank for initial scaffold.
    }
}
