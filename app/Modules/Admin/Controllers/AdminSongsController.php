<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminSongsController extends Controller
{
    public function index(Request $request): View
    {
        Gate::forUser($request->user('admin'))->authorize('access-admin');

        $songs = Song::orderBy('artist')->orderBy('title')->get();

        return view('admin.songs.index', [
            'songs' => $songs,
        ]);
    }

    public function edit(Request $request, Song $song): View
    {
        Gate::forUser($request->user('admin'))->authorize('access-admin');

        return view('admin.songs.edit', [
            'song' => $song,
        ]);
    }

    public function update(Request $request, Song $song): RedirectResponse
    {
        Gate::forUser($request->user('admin'))->authorize('access-admin');

        $data = $this->validatedSongData($request);

        $song->update($data);

        return redirect()
            ->route('admin.songs.edit', $song)
            ->with('status', 'Song updated.');
    }

    public function destroy(Request $request, Song $song): RedirectResponse
    {
        Gate::forUser($request->user('admin'))->authorize('access-admin');

        $song->delete();

        return redirect()
            ->route('admin.songs.index')
            ->with('status', 'Song deleted.');
    }

    private function validatedSongData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['nullable', 'string', 'max:255'],
            'duration_seconds' => ['required', 'integer', 'min:1'],
            'lyrics' => ['nullable', 'string'],
        ]);
    }
}
