<?php

use Illuminate\Support\Facades\Route;
use App\Modules\PublicScreen\Controllers\PublicJoinController;

Route::view('/', 'public.index')->name('public.index');

Route::get('/join/{eventCode}/{joinToken}', PublicJoinController::class);
