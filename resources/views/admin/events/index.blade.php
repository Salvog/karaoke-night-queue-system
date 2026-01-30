@extends('admin.layout')

@section('content')
    <h1>Events</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Venue</th>
                <th>Code</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($events as $event)
            <tr>
                <td>{{ $event->id }}</td>
                <td>{{ $event->venue?->name ?? 'N/A' }}</td>
                <td>{{ $event->code }}</td>
                <td><span class="pill">{{ $event->status }}</span></td>
                <td>
                    <div class="actions">
                        <a class="button secondary" href="{{ route('admin.queue.show', $event) }}">Queue</a>
                        <a class="button secondary" href="{{ route('admin.theme.show', $event) }}">Theme/Ads</a>
                        @if ($adminUser->isAdmin())
                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}">
                                @csrf
                                @method('DELETE')
                                <button class="button danger" type="submit">Delete</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">No events yet.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
