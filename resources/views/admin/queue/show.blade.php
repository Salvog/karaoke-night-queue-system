@extends('admin.layout')

@section('content')
    <h1>Queue for Event #{{ $eventNight->id }}</h1>
    <p>Venue: {{ $eventNight->venue?->name ?? 'N/A' }}</p>

    <div class="actions" style="margin-bottom: 16px;">
        <form method="POST" action="{{ route('admin.queue.stop', $eventNight) }}">
            @csrf
            <button class="button danger" type="submit">Stop Playback</button>
        </form>
        <form method="POST" action="{{ route('admin.queue.next', $eventNight) }}">
            @csrf
            <button class="button" type="submit">Next Song</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Participant</th>
                <th>Song</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($queue as $request)
            <tr>
                <td>{{ $request->id }}</td>
                <td>{{ $request->participant?->name ?? 'Guest' }}</td>
                <td>{{ $request->song?->title ?? 'Unknown' }}</td>
                <td><span class="pill">{{ $request->status }}</span></td>
                <td>
                    <div class="actions">
                        <form method="POST" action="{{ route('admin.queue.skip', $eventNight) }}">
                            @csrf
                            <input type="hidden" name="song_request_id" value="{{ $request->id }}">
                            <button class="button secondary" type="submit">Skip</button>
                        </form>
                        <form method="POST" action="{{ route('admin.queue.cancel', $eventNight) }}">
                            @csrf
                            <input type="hidden" name="song_request_id" value="{{ $request->id }}">
                            <button class="button danger" type="submit">Cancel</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">No queued songs.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
