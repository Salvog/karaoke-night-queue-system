<?php

use App\Modules\PublicJoin\Controllers\PublicEntryController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    });

    Route::get('/_ops/cache-rebuild', function () {
        $commands = [
            'config:clear',
            'route:clear',
            'view:clear',
            'config:cache',
            'route:cache',
            'view:cache',
        ];

        $results = [];

        foreach ($commands as $command) {
            $exitCode = Artisan::call($command);
            $results[] = [
                'command' => $command,
                'exit_code' => $exitCode,
                'output' => trim(Artisan::output()),
            ];

            if ($exitCode !== 0) {
                return response()->json([
                    'ok' => false,
                    'failed_command' => $command,
                    'results' => $results,
                ], 500);
            }
        }

        return response()->json([
            'ok' => true,
            'message' => 'Cache rebuilt successfully',
            'results' => $results,
        ]);
    });

    Route::prefix('admin')
        ->group(base_path('routes/admin.php'));

    Route::prefix('public')
        ->group(base_path('routes/public.php'));

    Route::get('/', [PublicEntryController::class, 'show'])->name('public.home');

    Route::group([], base_path('routes/public-join.php'));
    Route::group([], base_path('routes/public-screen.php'));
});
