@extends('admin.layout')

@section('content')
    <h1>Queue for Event #{{ $eventNight->id }}</h1>
    <p>Venue: {{ $eventNight->venue?->name ?? 'N/A' }}</p>
    <p>Event code: <strong>{{ $eventNight->code }}</strong></p>
    <p>
        Starts: {{ $eventNight->starts_at?->format('Y-m-d H:i') ?? '—' }}
        | Ends: {{ $eventNight->ends_at?->format('Y-m-d H:i') ?? '—' }}
        | Status: <span class="pill">{{ \App\Models\EventNight::STATUS_LABELS[$eventNight->status] ?? $eventNight->status }}</span>
    </p>

    @php
        $playbackState = $eventNight->playbackState;
        $currentRequest = $playbackState?->currentRequest;
        $expectedEndLabel = $playbackState?->expected_end_at
            ? $playbackState->expected_end_at->copy()->setTimezone($timezone)->format('H:i:s')
            : '—';
    @endphp

    <div style="margin: 16px 0; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <h2 style="margin-top: 0;">Playback Status</h2>
        <p>State: <strong id="playback-state">{{ $playbackState?->state ?? 'idle' }}</strong></p>
        <p>
            Current song:
            <strong id="playback-current-song">{{ $currentRequest?->song?->title ?? 'None' }}</strong>
            @if ($currentRequest?->participant)
                (<span id="playback-current-participant">{{ $currentRequest->participant->display_name ?? 'Guest' }}</span>)
            @else
                (<span id="playback-current-participant">Guest</span>)
            @endif
        </p>
        <p>Expected end: <span id="playback-expected-end">{{ $expectedEndLabel }}</span> ({{ $timezone }})</p>
        <p>Break: {{ $eventNight->break_seconds }}s | Request cooldown: {{ $eventNight->request_cooldown_seconds }}s</p>
        <p style="margin-bottom: 0; color: #6b7280;">
            Break seconds add extra time after each song. Cooldown seconds limit how often participants can request.
        </p>
        <p style="margin-top: 8px; color: #6b7280;">Last refresh: <span id="queue-last-refresh">just now</span></p>
    </div>

    <div class="actions" style="margin-bottom: 16px;">
        @if (! $playbackState || $playbackState->state !== \App\Models\PlaybackState::STATE_PLAYING)
            <form method="POST" action="{{ route('admin.queue.start', $eventNight) }}">
                @csrf
                <button class="button" type="submit">Start / Resume Playback</button>
            </form>
        @endif
        @if ($playbackState && $playbackState->state === \App\Models\PlaybackState::STATE_PLAYING)
            <form method="POST" action="{{ route('admin.queue.stop', $eventNight) }}">
                @csrf
                <button class="button secondary" type="submit">Pause Playback</button>
            </form>
        @endif
        <form method="POST" action="{{ route('admin.queue.next', $eventNight) }}">
            @csrf
            <button class="button" type="submit">Next Song</button>
        </form>
    </div>

    @if ($eventNight->status !== \App\Models\EventNight::STATUS_ACTIVE)
        <p style="margin-bottom: 16px; color: #b45309;">
            Auto-advance runs only when the event status is Active.
        </p>
    @endif

    <div style="margin-bottom: 24px;">
        <h2>Add Manual Request</h2>
        <form method="POST" action="{{ route('admin.queue.add', $eventNight) }}" class="actions" style="align-items: flex-end;">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label for="display_name">Participant name</label>
                <input id="display_name" type="text" name="display_name" required>
            </div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label for="song_id">Song</label>
                <select id="song_id" name="song_id" required>
                    @foreach ($songs as $song)
                        <option value="{{ $song->id }}">{{ $song->artist ?? 'Unknown' }} - {{ $song->title }}</option>
                    @endforeach
                </select>
            </div>
            <button class="button" type="submit">Add to Queue</button>
        </form>
    </div>

    <h2>Up Next</h2>
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
        <tbody id="queue-body">
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

    <h2 style="margin-top: 24px;">Played / Skipped</h2>
    <table>
        <thead>
            <tr>
                <th>When</th>
                <th>Participant</th>
                <th>Song</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="history-body">
        @forelse ($history as $request)
            @php
                $playedLabel = $request->played_at
                    ? $request->played_at->copy()->setTimezone($timezone)->format('H:i')
                    : '—';
            @endphp
            <tr>
                <td>{{ $playedLabel }}</td>
                <td>{{ $request->participant?->display_name ?? 'Guest' }}</td>
                <td>{{ $request->song?->title ?? 'Unknown' }}</td>
                <td><span class="pill">{{ $request->status }}</span></td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No completed requests yet.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <script>
        const queueStateUrl = @json(route('admin.queue.state', $eventNight));
        const queuePollMs = 5000;

        const playbackStateEl = document.getElementById('playback-state');
        const playbackSongEl = document.getElementById('playback-current-song');
        const playbackParticipantEl = document.getElementById('playback-current-participant');
        const playbackExpectedEl = document.getElementById('playback-expected-end');
        const queueBodyEl = document.getElementById('queue-body');
        const historyBodyEl = document.getElementById('history-body');
        const queueRefreshEl = document.getElementById('queue-last-refresh');

        const renderQueueRows = (items) => {
            if (!items || items.length === 0) {
                return '<tr><td colspan="5">No queued songs.</td></tr>';
            }

            return items.map((item) => (
                `<tr>
                    <td>${item.position ?? '—'}</td>
                    <td>${item.participant}</td>
                    <td>${item.song}</td>
                    <td><span class="pill">${item.status}</span></td>
                    <td>
                        <div class="actions">
                            <form method="POST" action="{{ route('admin.queue.skip', $eventNight) }}">
                                @csrf
                                <input type="hidden" name="song_request_id" value="${item.id}">
                                <button class="button secondary" type="submit">Skip</button>
                            </form>
                            <form method="POST" action="{{ route('admin.queue.cancel', $eventNight) }}">
                                @csrf
                                <input type="hidden" name="song_request_id" value="${item.id}">
                                <button class="button danger" type="submit">Cancel</button>
                            </form>
                        </div>
                    </td>
                </tr>`
            )).join('');
        };

        const renderHistoryRows = (items) => {
            if (!items || items.length === 0) {
                return '<tr><td colspan="4">No completed requests yet.</td></tr>';
            }

            return items.map((item) => (
                `<tr>
                    <td>${item.played_at_label ?? '—'}</td>
                    <td>${item.participant}</td>
                    <td>${item.song}</td>
                    <td><span class="pill">${item.status}</span></td>
                </tr>`
            )).join('');
        };

        const updateQueueState = (state) => {
            playbackStateEl.textContent = state.playback?.state ?? 'idle';
            playbackSongEl.textContent = state.playback?.current_song ?? 'None';
            playbackParticipantEl.textContent = state.playback?.current_participant ?? 'Guest';
            playbackExpectedEl.textContent = state.playback?.expected_end_label ?? '—';
            queueBodyEl.innerHTML = renderQueueRows(state.queue);
            historyBodyEl.innerHTML = renderHistoryRows(state.history);
            queueRefreshEl.textContent = new Date().toLocaleTimeString();
        };

        const pollQueueState = async () => {
            try {
                const response = await fetch(queueStateUrl, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) {
                    return;
                }
                const state = await response.json();
                updateQueueState(state);
            } catch (error) {
                console.warn('Queue polling failed', error);
            }
        };

        pollQueueState();
        setInterval(pollQueueState, queuePollMs);
    </script>
@endsection
