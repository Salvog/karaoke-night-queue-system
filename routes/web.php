<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    });

    Route::prefix('admin')
        ->group(base_path('routes/admin.php'));

    Route::prefix('public')
        ->group(base_path('routes/public.php'));

    Route::group([], base_path('routes/public-join.php'));
    Route::group([], base_path('routes/public-screen.php'));
});
