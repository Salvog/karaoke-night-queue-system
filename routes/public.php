<?php

use Illuminate\Support\Facades\Route;
use App\Modules\PublicScreen\Controllers\PublicJoinController;

Route::get('/join/{eventCode}/{joinToken}', PublicJoinController::class);
