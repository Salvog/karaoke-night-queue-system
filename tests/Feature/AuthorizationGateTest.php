<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthorizationGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_manage_event_nights_gate_allows_authenticated_users(): void
    {
        $user = AdminUser::create([
            'name' => 'Gate User',
            'email' => 'gate@example.com',
            'password' => 'password123',
            'role' => AdminUser::ROLE_STAFF,
        ]);

        $this->assertTrue(Gate::forUser($user)->allows('manage-event-nights'));
    }
}
