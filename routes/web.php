<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    });

    Route::prefix('admin')
        ->middleware('auth')
        ->group(base_path('routes/admin.php'));

    Route::prefix('public')
        ->group(base_path('routes/public.php'));
});
