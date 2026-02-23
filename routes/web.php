<?php

use App\Modules\PublicJoin\Controllers\PublicEntryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    });

    Route::post('/_ops/cache-rebuild', function (Request $request) {
        abort_unless((bool) config('ops.cache_rebuild.enabled', false), 404);

        $providedToken = trim((string) ($request->header('X-Ops-Token') ?? $request->input('token', '')));
        $expectedToken = trim((string) config('ops.cache_rebuild.token', ''));

        abort_if($expectedToken === '' || ! hash_equals($expectedToken, $providedToken), 403);

        $allowedIps = collect(explode(',', (string) config('ops.cache_rebuild.allowed_ips', '')))
            ->map(fn (string $ip) => trim($ip))
            ->filter()
            ->values();

        if ($allowedIps->isNotEmpty()) {
            abort_unless($allowedIps->contains((string) $request->ip()), 403);
        }

        $lockFile = storage_path('framework/cache-rebuild.lock');
        abort_if(is_file($lockFile), 410, 'Endpoint already used');

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

        @file_put_contents($lockFile, now()->toDateTimeString());

        return response()->json([
            'ok' => true,
            'message' => 'Cache rebuilt successfully',
            'results' => $results,
        ]);
    })->middleware('throttle:2,1');

    Route::prefix('admin')
        ->group(base_path('routes/admin.php'));

    Route::prefix('public')
        ->group(base_path('routes/public.php'));

    Route::get('/', [PublicEntryController::class, 'show'])->name('public.home');

    Route::group([], base_path('routes/public-join.php'));
    Route::group([], base_path('routes/public-screen.php'));
});
