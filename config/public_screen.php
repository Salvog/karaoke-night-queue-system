<?php

return [
    'queue_next_count' => 10,
    'queue_recent_count' => 2,
    'poll_seconds' => 1,
    'global_brand' => [
        'name' => env('PUBLIC_SCREEN_GLOBAL_BRAND_NAME', 'Karaoke Control Room'),
        'logo' => env('PUBLIC_SCREEN_GLOBAL_BRAND_LOGO', '/images/admin/karaoke-duo.svg'),
    ],
    'join_qr' => [
        'service_url' => env('PUBLIC_SCREEN_QR_SERVICE_URL', 'https://api.qrserver.com/v1/create-qr-code/'),
        'size' => (int) env('PUBLIC_SCREEN_QR_SIZE', 120),
    ],
    'realtime' => [
        'enabled' => env('PUBLIC_SCREEN_REALTIME_ENABLED', true),
        'disable_on_cli_server' => env('PUBLIC_SCREEN_REALTIME_DISABLE_ON_CLI_SERVER', true),
        'cache_ttl_seconds' => 3600,
        'stream_seconds' => 20,
        'sleep_seconds' => 1,
        'max_consecutive_errors' => 2,
        'connect_timeout_seconds' => 10,
    ],
];
