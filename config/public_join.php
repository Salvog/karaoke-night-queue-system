<?php

return [
    'device_cookie_name' => 'device_cookie_id',
    'rate_limit_per_ip' => env('PUBLIC_JOIN_RATE_LIMIT_IP', 20),
    'rate_limit_per_participant' => env('PUBLIC_JOIN_RATE_LIMIT_PARTICIPANT', 10),
    'rate_limit_decay_seconds' => env('PUBLIC_JOIN_RATE_LIMIT_DECAY', 60),
    'rate_limit_read_per_ip' => env('PUBLIC_JOIN_RATE_LIMIT_READ_IP', 90),
    'rate_limit_read_per_participant' => env('PUBLIC_JOIN_RATE_LIMIT_READ_PARTICIPANT', 60),
    'rate_limit_read_decay_seconds' => env('PUBLIC_JOIN_RATE_LIMIT_READ_DECAY', 60),
];
