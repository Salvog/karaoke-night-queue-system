<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MaintenanceCacheRebuildEndpointTest extends TestCase
{
    protected function tearDown(): void
    {
        @unlink(storage_path('framework/cache-rebuild.lock'));

        parent::tearDown();
    }

    public function test_endpoint_returns_not_found_when_disabled(): void
    {
        config([
            'ops.cache_rebuild.enabled' => false,
            'ops.cache_rebuild.token' => 'secret-token',
        ]);

        $response = $this->post('/_ops/cache-rebuild', [
            'token' => 'secret-token',
        ]);

        $response->assertNotFound();
    }

    public function test_endpoint_returns_forbidden_for_invalid_token(): void
    {
        config([
            'ops.cache_rebuild.enabled' => true,
            'ops.cache_rebuild.token' => 'secret-token',
        ]);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/_ops/cache-rebuild', [
                'token' => 'wrong-token',
            ]);

        $response->assertForbidden();
    }

    public function test_endpoint_rebuilds_cache_once_with_valid_credentials(): void
    {
        config([
            'ops.cache_rebuild.enabled' => true,
            'ops.cache_rebuild.token' => 'secret-token',
            'ops.cache_rebuild.allowed_ips' => '127.0.0.1',
        ]);

        Artisan::shouldReceive('call')->times(6)->andReturn(0);
        Artisan::shouldReceive('output')->times(6)->andReturn('ok');

        $first = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeader('X-Ops-Token', 'secret-token')
            ->post('/_ops/cache-rebuild');

        $first->assertOk();
        $first->assertJsonPath('ok', true);
        $first->assertJsonCount(6, 'results');

        $second = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeader('X-Ops-Token', 'secret-token')
            ->post('/_ops/cache-rebuild');

        $second->assertStatus(410);
    }
}
