@extends('admin.layout')

@section('content')
    <div class="actions" style="justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin: 0;">Songs</h1>
            <div class="helper">Edit or remove songs from the catalog.</div>
        </div>
    </div>
    <div class="divider"></div>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Artist</th>
                <th>Duration</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($songs as $song)
            <tr>
                <td>{{ $song->title }}</td>
                <td>{{ $song->artist ?? 'Unknown' }}</td>
                <td>{{ $song->duration_seconds }}s</td>
                <td>
                    <div class="actions">
                        <a class="button secondary" href="{{ route('admin.songs.edit', $song) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.songs.destroy', $song) }}">
                            @csrf
                            @method('DELETE')
                            <button class="button danger" type="submit">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No songs available.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
