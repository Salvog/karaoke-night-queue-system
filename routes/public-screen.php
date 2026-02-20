<?php

use App\Modules\PublicScreen\Controllers\PublicScreenController;
use Illuminate\Support\Facades\Route;

Route::get('/screen/{eventCode}', [PublicScreenController::class, 'show'])
    ->name('public.screen.show');

Route::get('/screen/{eventCode}/state', [PublicScreenController::class, 'state'])
    ->name('public.screen.state');

Route::get('/screen/{eventCode}/stream', [PublicScreenController::class, 'stream'])
    ->name('public.screen.stream');

Route::get('/media/{path}', [PublicScreenController::class, 'media'])
    ->where('path', '.*')
    ->name('public.screen.media');
