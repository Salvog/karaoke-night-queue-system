<?php

namespace Tests\Feature;

use Illuminate\Contracts\Encryption\Encrypter;
use Tests\TestCase;

class ContainerBindingsTest extends TestCase
{
    public function test_encrypter_binding_is_available(): void
    {
        $this->assertInstanceOf(Encrypter::class, app('encrypter'));
    }
}
