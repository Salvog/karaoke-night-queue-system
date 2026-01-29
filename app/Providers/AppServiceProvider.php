<?php

namespace App\Providers;

use App\Modules\Queue\Services\NullRealtimeBroadcaster;
use App\Modules\Queue\Services\RealtimeBroadcasterInterface;
use Illuminate\Support\ServiceProvider;

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
        //
    }
}
