@extends('admin.layout')

@section('content')
    <h1>Events</h1>
    <div class="actions" style="margin-bottom: 16px;">
        <a class="button" href="{{ route('admin.events.create') }}">Create Event</a>
    </div>

    @if ($currentEvent)
        <section class="section">
            <div class="section-header">
                <h2>Ongoing Event</h2>
                <span class="badge success">Live now</span>
            </div>
            <div class="card-grid">
                <div class="card-panel">
                    <h3>{{ $currentEvent->venue?->name ?? 'N/A' }}</h3>
                    <p class="muted">
                        {{ $currentEvent->starts_at?->format('Y-m-d H:i') ?? '—' }}
                        → {{ $currentEvent->ends_at?->format('Y-m-d H:i') ?? '—' }}
                    </p>
                    <div class="meta">
                        <span class="pill">Code: {{ $currentEvent->code }}</span>
                        <span class="pill">{{ \App\Models\EventNight::STATUS_LABELS[$currentEvent->status] ?? $currentEvent->status }}</span>
                    </div>
                </div>
                <div class="card-panel">
                    <div class="actions">
                        <a class="button secondary" href="{{ route('admin.events.edit', $currentEvent) }}">Edit</a>
                        <a class="button secondary" href="{{ route('admin.queue.show', $currentEvent) }}">Queue</a>
                        <a class="button secondary" href="{{ route('admin.theme.show', $currentEvent) }}">Theme/Ads</a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="section">
        <div class="section-header">
            <h2>All Events</h2>
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
    </section>
@endsection
