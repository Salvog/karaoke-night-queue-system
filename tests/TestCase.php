<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            storage_path('framework/views'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
        ] as $path) {
            if (! is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }
    }
}
