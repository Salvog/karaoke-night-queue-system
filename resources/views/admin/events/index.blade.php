@extends('admin.layout')

@section('content')
    <h1>Events</h1>
    <div style="margin-bottom: 16px;">
        <a class="button" href="{{ route('admin.events.create') }}">Create Event</a>
    </div>
    <div style="margin-bottom: 24px;">
        <h2 style="margin-top: 0;">Current Event</h2>
        @if ($currentEvent)
            <div class="panel">
                <div class="panel-row">
                    <div>
                        <div class="label">Venue</div>
                        <div class="value">{{ $currentEvent->venue?->name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="label">Start</div>
                        <div class="value">{{ $currentEvent->starts_at?->format('Y-m-d H:i') ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="label">End</div>
                        <div class="value">{{ $currentEvent->ends_at?->format('Y-m-d H:i') ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="label">Code</div>
                        <div class="value">{{ $currentEvent->code }}</div>
                    </div>
                    <div>
                        <div class="label">Status</div>
                        <div class="value">
                            <span class="pill">{{ \App\Models\EventNight::STATUS_LABELS[$currentEvent->status] ?? $currentEvent->status }}</span>
                        </div>
                    </div>
                </div>
                <div class="actions" style="margin-top: 12px;">
                    <a class="button secondary" href="{{ route('admin.events.edit', $currentEvent) }}">Edit</a>
                    <a class="button secondary" href="{{ route('admin.queue.show', $currentEvent) }}">Queue</a>
                    <a class="button secondary" href="{{ route('admin.theme.show', $currentEvent) }}">Theme/Ads</a>
                </div>
            </div>
        @else
            <div class="panel muted">No event is currently running.</div>
        @endif
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Venue</th>
                <th>Start</th>
                <th>End</th>
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
                <td>{{ $event->starts_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td>{{ $event->ends_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td>{{ $event->code }}</td>
                <td><span class="pill">{{ \App\Models\EventNight::STATUS_LABELS[$event->status] ?? $event->status }}</span></td>
                <td>
                    <div class="actions">
                        <a class="button secondary" href="{{ route('admin.events.edit', $event) }}">Edit</a>
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
                <td colspan="7">No events yet.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
