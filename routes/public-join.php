<?php

use App\Modules\PublicJoin\Controllers\PublicJoinController;
use Illuminate\Support\Facades\Route;

Route::get('/e/{eventCode}', [PublicJoinController::class, 'show'])
    ->name('public.join.show');

Route::post('/e/{eventCode}/activate', [PublicJoinController::class, 'activate'])
    ->middleware('public.rate_limit')
    ->name('public.join.activate');

Route::post('/e/{eventCode}/request', [PublicJoinController::class, 'requestSong'])
    ->middleware('public.rate_limit')
    ->name('public.join.request');
