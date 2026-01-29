<?php

use Illuminate\Support\Facades\Route;

Route::get('/join/{eventCode}/{joinToken}', function (string $eventCode, string $joinToken) {
    return response()->json([
        'message' => 'Public join stub',
        'eventCode' => $eventCode,
        'joinToken' => $joinToken,
    ]);
});
