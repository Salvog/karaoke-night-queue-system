<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Song;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSongCrudTest extends TestCase
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
            'artist' => 'Artist',
            'duration_seconds' => 200,
            'lyrics' => 'Lyrics',
        ]);

        $response = $this->actingAs($admin, 'admin')->put("/admin/songs/{$song->id}", [
            'title' => 'Updated',
            'artist' => 'Artist',
            'duration_seconds' => 210,
            'lyrics' => 'New lyrics',
        ]);

        $response->assertRedirect("/admin/songs/{$song->id}/edit");
        $this->assertSame('Updated', $song->fresh()->title);

        $delete = $this->actingAs($admin, 'admin')->delete("/admin/songs/{$song->id}");

        $delete->assertRedirect('/admin/songs');
        $this->assertDatabaseCount('songs', 0);
    }
}
