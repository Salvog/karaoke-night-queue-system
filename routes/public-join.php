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

Route::get('/e/{eventCode}/my-requests', [PublicJoinController::class, 'myRequests'])
    ->middleware('public.rate_limit:read')
    ->name('public.join.my-requests');

Route::get('/e/{eventCode}/songs', [PublicJoinController::class, 'searchSongs'])
    ->middleware('public.rate_limit:read')
    ->name('public.join.songs');

Route::get('/e/{eventCode}/eta', [PublicJoinController::class, 'eta'])
    ->middleware('public.rate_limit')
    ->name('public.join.eta');
