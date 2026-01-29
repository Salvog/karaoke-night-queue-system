<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminRouteTest extends TestCase
{
    public function test_admin_requires_authentication(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(302);
    }

    public function test_admin_allows_authenticated_user(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Admin area stub']);
    }
}
