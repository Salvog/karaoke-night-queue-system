<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_logout(): void
    {
        $user = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret-password',
            'role' => AdminUser::ROLE_ADMIN,
        ]);

        $loginResponse = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ]);

        $loginResponse->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user, 'admin');

        $logoutResponse = $this->post('/admin/logout');
        $logoutResponse->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }
}
