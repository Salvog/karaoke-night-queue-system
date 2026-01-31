<?php

namespace App\Modules\PublicScreen\Realtime;

use App\Models\EventNight;
use Illuminate\Contracts\Cache\Repository;

class SseStateStore
{
    public function __construct(
        private readonly Repository $cache,
        private readonly int $ttlSeconds
    ) {
    }

    public function write(EventNight $eventNight, string $type, array $payload): void
    {
        $this->cache->put(
            $this->payloadKey($eventNight, $type),
            [
                'updated_at_ms' => now()->valueOf(),
                'payload' => $payload,
            ],
            $this->ttlSeconds
        );
    }

    public function read(EventNight $eventNight, string $type): ?array
    {
        return $this->cache->get($this->payloadKey($eventNight, $type));
    }

    private function payloadKey(EventNight $eventNight, string $type): string
    {
        return "public-screen:{$eventNight->id}:{$type}";
    }
}
