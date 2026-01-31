@extends('admin.layout')

@section('content')
    <h1>Songs</h1>
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
