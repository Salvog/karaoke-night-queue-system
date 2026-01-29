<?php

namespace Tests\Feature;

use Illuminate\Contracts\Hashing\Hasher;
use Tests\TestCase;

class HashBindingTest extends TestCase
{
    public function test_hash_binding_is_available(): void
    {
        $this->assertInstanceOf(Hasher::class, app('hash'));
    }
}
