<?php

namespace App\Modules\Queue\Services;

interface RealtimeBroadcasterInterface
{
    public function broadcast(string $channel, string $event, array $payload = []): void;
}
