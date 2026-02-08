<?php

namespace Tests;

use Illuminate\Foundation\Application;
use RuntimeException;

trait CreatesApplication
{
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        if ($app->environment('testing')) {
            $connection = (string) $app['config']->get('database.default');
            $database = (string) $app['config']->get("database.connections.{$connection}.database");

            if ($connection !== 'sqlite' || $database !== ':memory:') {
                throw new RuntimeException(sprintf(
                    'Unsafe test DB configuration: connection=%s database=%s. Expected sqlite/:memory: to avoid data loss.',
                    $connection,
                    $database !== '' ? $database : 'null'
                ));
            }
        }

        return $app;
    }
}
