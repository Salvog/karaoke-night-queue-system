<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Song;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSongsPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_songs_index_is_paginated(): void
    {
        $admin = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => AdminUser::ROLE_ADMIN,
        ]);

        foreach (range(1, 30) as $index) {
            Song::create([
                'title' => 'Song '.$index,
                'artist' => 'Artist '.$index,
                'duration_seconds' => 180 + $index,
                'lyrics' => null,
            ]);
        }

        $response = $this->actingAs($admin, 'admin')->get('/admin/songs');

        $response->assertOk();
        $response->assertViewHas('songs', function ($songs) {
            return $songs->count() === 25
                && method_exists($songs, 'total')
                && $songs->total() === 30
                && $songs->currentPage() === 1;
        });

        $pageTwo = $this->actingAs($admin, 'admin')->get('/admin/songs?page=2');

        $pageTwo->assertOk();
        $pageTwo->assertViewHas('songs', fn ($songs) => $songs->count() === 5 && $songs->currentPage() === 2);
    }
}
