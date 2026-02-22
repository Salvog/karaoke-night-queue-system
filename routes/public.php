<?php

use Illuminate\Support\Facades\Route;
use App\Modules\PublicScreen\Controllers\PublicJoinController;
use App\Modules\PublicJoin\Controllers\PublicEntryController;

Route::get('/', [PublicEntryController::class, 'show'])->name('public.index');

Route::get('/join/{eventCode}/{joinToken}', PublicJoinController::class);
