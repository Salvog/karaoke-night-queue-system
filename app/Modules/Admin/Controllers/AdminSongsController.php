<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Song;
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
}
