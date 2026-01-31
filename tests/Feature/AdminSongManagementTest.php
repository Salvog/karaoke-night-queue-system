<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Song;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSongManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_and_delete_song(): void
    {
        $admin = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => AdminUser::ROLE_ADMIN,
        ]);

        $song = Song::create([
            'title' => 'Original',
            'artist' => 'Singer',
            'duration_seconds' => 180,
            'lyrics' => 'Old lyrics',
        ]);

        $updateResponse = $this->actingAs($admin, 'admin')->put("/admin/songs/{$song->id}", [
            'title' => 'Updated',
            'artist' => 'New Artist',
            'duration_seconds' => 200,
            'lyrics' => 'New lyrics',
        ]);

        $updateResponse->assertStatus(302);
        $this->assertDatabaseHas('songs', [
            'id' => $song->id,
            'title' => 'Updated',
            'artist' => 'New Artist',
            'duration_seconds' => 200,
        ]);

        $deleteResponse = $this->actingAs($admin, 'admin')->delete("/admin/songs/{$song->id}");

        $deleteResponse->assertStatus(302);
        $this->assertDatabaseMissing('songs', ['id' => $song->id]);
    }
}
