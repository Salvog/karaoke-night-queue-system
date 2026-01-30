@extends('admin.layout')

@section('content')
    <h1>Songs</h1>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Artist</th>
                <th>Duration</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($songs as $song)
            <tr>
                <td>{{ $song->title }}</td>
                <td>{{ $song->artist ?? 'Unknown' }}</td>
                <td>{{ $song->duration_seconds }}s</td>
            </tr>
        @empty
            <tr>
                <td colspan="3">No songs available.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
