<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\AdminDashboardController;

Route::get('/', AdminDashboardController::class);
