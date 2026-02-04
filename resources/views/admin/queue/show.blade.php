@extends('admin.layout')

@section('content')
    <div class="panel" style="margin-bottom: 20px;">
        <div class="panel-row">
            <div>
                <div class="label">Event</div>
                <div class="value">#{{ $eventNight->id }} · {{ $eventNight->venue?->name ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="label">Starts</div>
                <div class="value">{{ $eventNight->starts_at?->format('Y-m-d H:i') ?? '—' }}</div>
            </div>
            <div>
                <div class="label">Ends</div>
                <div class="value">{{ $eventNight->ends_at?->format('Y-m-d H:i') ?? '—' }}</div>
            </div>
            <div>
                <div class="label">Status</div>
                <div class="value">
                    <span class="pill">{{ \App\Models\EventNight::STATUS_LABELS[$eventNight->status] ?? $eventNight->status }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid two" style="margin-bottom: 20px;">
        <div class="panel">
            <h2 style="margin-top: 0;">Playback Control</h2>
            <div class="helper">Start the night, pause the flow, or move to the next singer.</div>
            <div class="divider"></div>
            <div class="panel-row">
                <div>
                    <div class="label">State</div>
                    <div class="value">{{ $eventNight->playbackState?->state ?? 'idle' }}</div>
                </div>
                <div>
                    <div class="label">Current Song</div>
                    <div class="value">
                        {{ $eventNight->playbackState?->currentRequest?->song?->title ?? '—' }}
                    </div>
                </div>
                <div>
                    <div class="label">Expected End</div>
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
                    <button class="button success" type="submit">Start Playback</button>
                </form>
                <form method="POST" action="{{ route('admin.queue.stop', $eventNight) }}">
                    @csrf
                    <button class="button secondary" type="submit">Pause Playback</button>
                </form>
                <form method="POST" action="{{ route('admin.queue.resume', $eventNight) }}">
                    @csrf
                    <button class="button" type="submit">Resume</button>
                </form>
                <form method="POST" action="{{ route('admin.queue.next', $eventNight) }}">
                    @csrf
                    <button class="button" type="submit">Next Song</button>
                </form>
            </div>
        </div>

        <div class="panel">
            <h2 style="margin-top: 0;">Queue Settings</h2>
            <div class="helper">These settings drive the automatic flow when playback is running.</div>
            <div class="divider"></div>
            <div class="panel-row">
                <div>
                    <div class="label">Break Seconds</div>
                    <div class="value">{{ $eventNight->break_seconds }}</div>
                    <div class="helper">Added after each song to allow transitions.</div>
                </div>
                <div>
                    <div class="label">Request Cooldown</div>
                    <div class="value">{{ $eventNight->request_cooldown_seconds }}</div>
                    <div class="helper">Minimum wait between requests per singer.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel" style="margin-bottom: 20px;">
        <h2 style="margin-top: 0;">Add a Manual Participant</h2>
        <div class="helper">Use this to add walk-in singers directly to the queue.</div>
        <form method="POST" action="{{ route('admin.queue.add', $eventNight) }}" class="grid three" style="margin-top: 16px; align-items: end;">
            @csrf
            <div>
                <label for="display_name">Singer Name</label>
                <input id="display_name" type="text" name="display_name" required>
            </div>
            <div>
                <label for="song_id">Song</label>
                <select id="song_id" name="song_id" required>
                    @foreach ($songs as $song)
                        <option value="{{ $song->id }}">{{ $song->artist ? "{$song->artist} - {$song->title}" : $song->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button class="button success" type="submit">Add to Queue</button>
            </div>
        </form>
    </div>

    <div class="grid two">
        <div class="panel">
            <h2 style="margin-top: 0;">Up Next</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
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
        </div>

        <div class="panel">
            <h2 style="margin-top: 0;">Played & Skipped</h2>
            <table>
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Participant</th>
                        <th>Song</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($history as $request)
                    <tr>
                        <td>{{ ($request->played_at ?? $request->updated_at)?->format('H:i') ?? '—' }}</td>
                        <td>{{ $request->participant?->display_name ?? 'Guest' }}</td>
                        <td>{{ $request->song?->title ?? 'Unknown' }}</td>
                        <td><span class="pill">{{ $request->status }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No completed songs yet.</td>
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
