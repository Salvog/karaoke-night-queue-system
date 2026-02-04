@extends('admin.layout')

@section('content')
    <h1>Eventi</h1>
    <div style="margin-bottom: 16px;">
        <a class="button" href="{{ route('admin.events.create') }}">Crea evento</a>
    </div>
    <div style="margin-bottom: 24px;">
        <h2 style="margin-top: 0;">Evento corrente</h2>
        @if ($currentEvent)
            <div class="panel">
                <div class="panel-row">
                    <div>
                        <div class="label">Location</div>
                        <div class="value">{{ $currentEvent->venue?->name ?? 'N/D' }}</div>
                    </div>
                    <div>
                        <div class="label">Inizio</div>
                        <div class="value">{{ $currentEvent->starts_at?->format('Y-m-d H:i') ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="label">Fine</div>
                        <div class="value">{{ $currentEvent->ends_at?->format('Y-m-d H:i') ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="label">Codice</div>
                        <div class="value">{{ $currentEvent->code }}</div>
                    </div>
                    <div>
                        <div class="label">Stato</div>
                        <div class="value">
                            <span class="pill">{{ \App\Models\EventNight::STATUS_LABELS[$currentEvent->status] ?? $currentEvent->status }}</span>
                        </div>
                    </div>
                </div>
                <div class="actions" style="margin-top: 12px;">
                    <a class="button secondary" href="{{ route('admin.events.edit', $currentEvent) }}">Modifica</a>
                    <a class="button secondary" href="{{ route('admin.queue.show', $currentEvent) }}">Coda</a>
                    <a class="button secondary" href="{{ route('admin.theme.show', $currentEvent) }}">Tema/Annunci</a>
                </div>
            </div>
        @else
            <div class="panel muted">Nessun evento in corso.</div>
        @endif
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Location</th>
                <th>Inizio</th>
                <th>Fine</th>
                <th>Codice</th>
                <th>Stato</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($events as $event)
            <tr>
                <td>{{ $event->id }}</td>
                <td>{{ $event->venue?->name ?? 'N/D' }}</td>
                <td>{{ $event->starts_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td>{{ $event->ends_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td>{{ $event->code }}</td>
                <td><span class="pill">{{ \App\Models\EventNight::STATUS_LABELS[$event->status] ?? $event->status }}</span></td>
                <td>
                    <div class="actions">
                        <a class="button secondary" href="{{ route('admin.events.edit', $event) }}">Modifica</a>
                        <a class="button secondary" href="{{ route('admin.queue.show', $event) }}">Coda</a>
                        <a class="button secondary" href="{{ route('admin.theme.show', $event) }}">Tema/Annunci</a>
                        @if ($adminUser->isAdmin())
                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}">
                                @csrf
                                @method('DELETE')
                                <button class="button danger" type="submit">Elimina</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">Nessun evento ancora.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
