<?php

namespace App\Providers;

use App\Modules\Queue\Services\NullRealtimeBroadcaster;
use App\Modules\Queue\Services\RealtimeBroadcasterInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RealtimeBroadcasterInterface::class, function () {
            return new NullRealtimeBroadcaster();
        });
    }

    public function boot(): void
    {
        Gate::define('access-admin', fn ($user) => $user !== null);
    }
}
