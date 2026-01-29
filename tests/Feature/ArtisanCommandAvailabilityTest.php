<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ArtisanCommandAvailabilityTest extends TestCase
{
    public function test_expected_artisan_commands_are_registered(): void
    {
        $commands = array_keys(Artisan::all());

        $this->assertContains('key:generate', $commands);
        $this->assertContains('migrate', $commands);
        $this->assertContains('serve', $commands);
    }
}
