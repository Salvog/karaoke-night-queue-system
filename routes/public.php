<?php

use App\Modules\PublicJoin\Controllers\PublicEntryController;
use App\Modules\PublicScreen\Controllers\PublicJoinController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicEntryController::class, 'show'])->name('public.index');

Route::get('/join/{eventCode}/{joinToken}', PublicJoinController::class);
