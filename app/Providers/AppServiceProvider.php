<?php

namespace App\Providers;

use App\Models\AdminUser;
use App\Models\EventNight;
use App\Policies\EventNightPolicy;
use App\Modules\PublicScreen\Realtime\NullRealtimePublisher;
use App\Modules\PublicScreen\Realtime\RealtimePublisher;
use App\Modules\PublicScreen\Realtime\SseRealtimePublisher;
use App\Modules\PublicScreen\Realtime\SseStateStore;
use App\Modules\Queue\Services\NullRealtimeBroadcaster;
use App\Modules\Queue\Services\RealtimeBroadcasterInterface;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RealtimeBroadcasterInterface::class, function () {
            return new NullRealtimeBroadcaster();
        });

        $this->app->singleton(SseStateStore::class, function ($app) {
            return new SseStateStore(
                $app->make(Repository::class),
                (int) config('public_screen.realtime.cache_ttl_seconds', 3600)
            );
        });

        $this->app->bind(RealtimePublisher::class, function ($app) {
            if (! config('public_screen.realtime.enabled', true)) {
                return new NullRealtimePublisher();
            }

            return $app->make(SseRealtimePublisher::class);
        });
    }

    public function boot(): void
    {
        Blueprint::macro('check', function (string $expression) {
            // Simplest cross-driver option: skip unsupported CHECK constraints in the schema builder.
            return $this;
        });

        Gate::policy(EventNight::class, EventNightPolicy::class);
        Gate::define('access-admin', fn (AdminUser $user) => in_array($user->role, AdminUser::ROLES, true));
        Gate::define('manage-event-nights', fn (AdminUser $user) => in_array($user->role, AdminUser::ROLES, true));
    }
}
