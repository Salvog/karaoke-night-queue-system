<?php

namespace Tests\Feature;

use Illuminate\Contracts\Cookie\Factory as CookieFactory;
use Tests\TestCase;

class CookieBindingTest extends TestCase
{
    public function test_cookie_factory_binding_is_available(): void
    {
        $this->assertInstanceOf(CookieFactory::class, app('cookie'));
    }
}
