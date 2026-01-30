<?php

use Illuminate\Support\Str;

return [
    'default' => env('CACHE_DRIVER', 'array'),

    'stores' => [
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
            'lock_connection' => null,
        ],
    ],

    'prefix' => env(
        'CACHE_PREFIX',
        Str::slug(env('APP_NAME', 'karaoke-night-queue'), '_').'_cache_'
    ),
];
