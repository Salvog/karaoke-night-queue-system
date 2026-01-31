<?php

return [
    'queue_next_count' => 5,
    'queue_recent_count' => 5,
    'poll_seconds' => 5,
    'realtime' => [
        'enabled' => env('PUBLIC_SCREEN_REALTIME_ENABLED', true),
        'cache_ttl_seconds' => 3600,
        'stream_seconds' => 20,
        'sleep_seconds' => 1,
    ],
];
