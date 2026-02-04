@extends('admin.layout')

@section('content')
    <div class="panel" style="margin-bottom: 20px;">
        <div class="panel-row">
            <div>
                <div class="label">Evento</div>
                <div class="value">#{{ $eventNight->id }} · {{ $eventNight->venue?->name ?? 'N/D' }}</div>
            </div>
            <div>
                <div class="label">Inizio</div>
                <div class="value">{{ $eventNight->starts_at?->format('Y-m-d H:i') ?? '—' }}</div>
            </div>
            <div>
                <div class="label">Fine</div>
                <div class="value">{{ $eventNight->ends_at?->format('Y-m-d H:i') ?? '—' }}</div>
            </div>
            <div>
                <div class="label">Stato</div>
                <div class="value">
                    <span class="pill">{{ \App\Models\EventNight::STATUS_LABELS[$eventNight->status] ?? $eventNight->status }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid two" style="margin-bottom: 20px;">
        <div class="panel">
            <h2 style="margin-top: 0;">Controllo riproduzione</h2>
            <div class="helper">Avvia la serata, metti in pausa il flusso o passa al prossimo cantante.</div>
            <div class="divider"></div>
            <div class="panel-row">
                <div>
                    <div class="label">Stato</div>
                    <div class="value">{{ $eventNight->playbackState?->state ?? 'idle' }}</div>
                </div>
                <div>
                    <div class="label">Canzone corrente</div>
                    <div class="value">
                        {{ $eventNight->playbackState?->currentRequest?->song?->title ?? '—' }}
                    </div>
                </div>
                <div>
                    <div class="label">Fine prevista</div>
                    @php($expectedEndAt = $eventNight->playbackState?->expected_end_at)
                    <div class="value">
                        @if ($expectedEndAt)
                            <span data-expected-end="{{ $expectedEndAt->toIso8601String() }}">{{ $expectedEndAt->format('H:i:s') }}</span>
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
            <div class="actions" style="margin-top: 16px;">
                <form method="POST" action="{{ route('admin.queue.start', $eventNight) }}">
                    @csrf
                    <button class="button success" type="submit">Avvia riproduzione</button>
                </form>
                <form method="POST" action="{{ route('admin.queue.stop', $eventNight) }}">
                    @csrf
                    <button class="button secondary" type="submit">Metti in pausa</button>
                </form>
                <form method="POST" action="{{ route('admin.queue.resume', $eventNight) }}">
                    @csrf
                    <button class="button" type="submit">Riprendi</button>
                </form>
                <form method="POST" action="{{ route('admin.queue.next', $eventNight) }}">
                    @csrf
                    <button class="button" type="submit">Prossima canzone</button>
                </form>
            </div>
        </div>

        <div class="panel">
            <h2 style="margin-top: 0;">Impostazioni coda</h2>
            <div class="helper">Queste impostazioni regolano il flusso automatico durante la riproduzione.</div>
            <div class="divider"></div>
            <div class="panel-row">
                <div>
                    <div class="label">Secondi di pausa</div>
                    <div class="value">{{ $eventNight->break_seconds }}</div>
                    <div class="helper">Aggiunti dopo ogni canzone per consentire le transizioni.</div>
                </div>
                <div>
                    <div class="label">Attesa richieste</div>
                    <div class="value">{{ $eventNight->request_cooldown_seconds }}</div>
                    <div class="helper">Attesa minima tra le richieste per cantante.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel" style="margin-bottom: 20px;">
        <h2 style="margin-top: 0;">Aggiungi partecipante manuale</h2>
        <div class="helper">Usa questa sezione per aggiungere cantanti presenti direttamente in coda.</div>
        <form method="POST" action="{{ route('admin.queue.add', $eventNight) }}" class="grid three" style="margin-top: 16px; align-items: end;">
            @csrf
            <div>
                <label for="display_name">Nome cantante</label>
                <input id="display_name" type="text" name="display_name" required>
            </div>
            <div>
                <label for="song_id">Canzone</label>
                <select id="song_id" name="song_id" required>
                    @foreach ($songs as $song)
                        <option value="{{ $song->id }}">{{ $song->artist ? "{$song->artist} - {$song->title}" : $song->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button class="button success" type="submit">Aggiungi alla coda</button>
            </div>
        </form>
    </div>

    <div class="grid two">
        <div class="panel">
            <h2 style="margin-top: 0;">Prossimi</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Partecipante</th>
                        <th>Canzone</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($queue as $request)
                    <tr>
                        <td>{{ $request->position ?? '—' }}</td>
                        <td>{{ $request->participant?->display_name ?? 'Ospite' }}</td>
                        <td>{{ $request->song?->title ?? 'Sconosciuta' }}</td>
                        <td><span class="pill">{{ $request->status }}</span></td>
                        <td>
                            <div class="actions">
                                <form method="POST" action="{{ route('admin.queue.skip', $eventNight) }}">
                                    @csrf
                                    <input type="hidden" name="song_request_id" value="{{ $request->id }}">
                                    <button class="button secondary" type="submit">Salta</button>
                                </form>
                                <form method="POST" action="{{ route('admin.queue.cancel', $eventNight) }}">
                                    @csrf
                                    <input type="hidden" name="song_request_id" value="{{ $request->id }}">
                                    <button class="button danger" type="submit">Annulla</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Nessuna canzone in coda.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2 style="margin-top: 0;">Riprodotte e saltate</h2>
            <table>
                <thead>
                    <tr>
                        <th>Quando</th>
                        <th>Partecipante</th>
                        <th>Canzone</th>
                        <th>Stato</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($history as $request)
                    <tr>
                        <td>{{ ($request->played_at ?? $request->updated_at)?->format('H:i') ?? '—' }}</td>
                        <td>{{ $request->participant?->display_name ?? 'Ospite' }}</td>
                        <td>{{ $request->song?->title ?? 'Sconosciuta' }}</td>
                        <td><span class="pill">{{ $request->status }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Nessuna canzone completata.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-expected-end]').forEach((element) => {
            const isoValue = element.dataset.expectedEnd;
            const parsed = isoValue ? new Date(isoValue) : null;

            if (parsed && !Number.isNaN(parsed.getTime())) {
                element.textContent = parsed.toLocaleTimeString();
            }
        });
    </script>
@endsection
