<?php

return [
    'cache_rebuild' => [
        'enabled' => env('MAINTENANCE_ENDPOINT_ENABLED', false),
        'token' => env('MAINTENANCE_ENDPOINT_TOKEN'),
        'allowed_ips' => env('MAINTENANCE_ENDPOINT_ALLOWED_IPS', ''),
    ],
];
