@extends('admin.layout')

@section('content')
    <h1>Queue Control Room</h1>
    <p class="muted">Event #{{ $eventNight->id }} · {{ $eventNight->venue?->name ?? 'N/A' }}</p>

    <section class="section">
        <div class="split">
            <div class="card-panel">
                <h3>Playback Status</h3>
                <p class="muted">State: <strong>{{ $eventNight->playbackState?->state ?? 'idle' }}</strong></p>
                <p class="muted">Current song: {{ $eventNight->playbackState?->currentRequest?->song?->title ?? 'None' }}</p>
                <p class="muted">Singer: {{ $eventNight->playbackState?->currentRequest?->participant?->display_name ?? '—' }}</p>
                <p class="muted">
                    Expected end:
                    {{ $eventNight->playbackState?->expected_end_at?->format('Y-m-d H:i') ?? '—' }}
                </p>
                <div class="actions" style="margin-top: 16px;">
                    <form method="POST" action="{{ route('admin.queue.start', $eventNight) }}">
                        @csrf
                        <button class="button" type="submit">Start / Resume Queue</button>
                    </form>
                    <form method="POST" action="{{ route('admin.queue.pause', $eventNight) }}">
                        @csrf
                        <button class="button danger" type="submit">Pause Queue</button>
                    </form>
                    <form method="POST" action="{{ route('admin.queue.next', $eventNight) }}">
                        @csrf
                        <button class="button secondary" type="submit">Skip to Next Song</button>
                    </form>
                </div>
            </div>
            <div class="card-panel">
                <h3>Queue Rules</h3>
                <p class="muted">Break Seconds: <strong>{{ $eventNight->break_seconds }}</strong></p>
                <small class="help">Adds a pause after each song before the next one starts.</small>
                <p class="muted" style="margin-top: 12px;">Request Cooldown Seconds: <strong>{{ $eventNight->request_cooldown_seconds }}</strong></p>
                <small class="help">Guests must wait this time before requesting another song.</small>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="section-header">
            <h2>Manual Add</h2>
        </div>
        <form method="POST" action="{{ route('admin.queue.manual', $eventNight) }}" class="split">
            @csrf
            <div>
                <label for="participant_name">Participant Name</label>
                <input id="participant_name" type="text" name="participant_name" value="{{ old('participant_name') }}" required>
            </div>
            <div>
                <label for="song_id">Song</label>
                <select id="song_id" name="song_id" required>
                    @forelse ($songs as $song)
                        <option value="{{ $song->id }}">{{ $song->title }} ({{ $song->artist ?? 'Unknown' }})</option>
                    @empty
                        <option value="" disabled>No songs available</option>
                    @endforelse
                </select>
                <small class="help">Choose from the full song catalog.</small>
            </div>
            <div class="actions" style="align-self: end;">
                <button class="button" type="submit" @disabled($songs->isEmpty())>Add to Queue</button>
            </div>
        </form>
    </section>

    <section class="section">
        <div class="section-header">
            <h2>Live Queue</h2>
            <span class="pill">{{ $queue->count() }} in line</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Participant</th>
                    <th>Song</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($queue as $request)
                <tr>
                    <td>{{ $request->position ?? '—' }}</td>
                    <td>{{ $request->participant?->display_name ?? 'Guest' }}</td>
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
    </section>

    <section class="section">
        <div class="section-header">
            <h2>Completed & Skipped</h2>
            <span class="pill">{{ $history->count() }} total</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Participant</th>
                    <th>Song</th>
                    <th>Status</th>
                    <th>Completed At</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($history as $request)
                <tr>
                    <td>{{ $request->participant?->display_name ?? 'Guest' }}</td>
                    <td>{{ $request->song?->title ?? 'Unknown' }}</td>
                    <td><span class="pill">{{ $request->status }}</span></td>
                    <td>{{ $request->played_at?->format('Y-m-d H:i') ?? $request->updated_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No completed or skipped songs yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </section>
@endsection
