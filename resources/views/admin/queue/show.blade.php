@extends('admin.layout')

@section('without_content_card', '1')

@section('content')
    @php
        $playbackState = $eventNight->playbackState;
        $playbackStatus = $playbackState?->state ?? \App\Models\PlaybackState::STATE_IDLE;
        $isPlaying = $playbackStatus === \App\Models\PlaybackState::STATE_PLAYING;
        $isPaused = $playbackStatus === \App\Models\PlaybackState::STATE_PAUSED;
        $playbackStatusLabels = [
            \App\Models\PlaybackState::STATE_IDLE => 'In attesa',
            \App\Models\PlaybackState::STATE_PLAYING => 'In riproduzione',
            \App\Models\PlaybackState::STATE_PAUSED => 'In pausa',
        ];
        $playbackStatusLabel = $playbackStatusLabels[$playbackStatus] ?? ucfirst($playbackStatus);
        $togglePlaybackRoute = $isPlaying
            ? route('admin.queue.stop', $eventNight)
            : ($isPaused ? route('admin.queue.resume', $eventNight) : route('admin.queue.start', $eventNight));
        $togglePlaybackTitle = $isPlaying
            ? 'Metti in pausa la serata'
            : ($isPaused ? 'Riprendi la serata' : 'Avvia la serata');
        $eventStatusLabel = \App\Models\EventNight::STATUS_LABELS[$eventNight->status] ?? $eventNight->status;
        $expectedEndAt = $playbackState?->expected_end_at;
        $cooldownMinutes = (int) ceil($eventNight->request_cooldown_seconds / 60);
    @endphp

    <style>
        .queue-page {
            display: grid;
            gap: clamp(16px, 2.4vw, 24px);
        }

        .queue-grid {
            display: grid;
            gap: clamp(14px, 2.1vw, 20px);
        }

        .queue-grid--top {
            grid-template-columns: minmax(0, 1.38fr) minmax(0, 1fr);
        }

        .queue-grid--tables {
            grid-template-columns: minmax(0, 1fr);
        }

        .queue-grid--tables > .queue-section {
            min-width: 0;
        }

        .queue-section {
            --section-border: rgba(255, 255, 255, 0.2);
            --section-bg-start: rgba(28, 31, 63, 0.72);
            --section-bg-end: rgba(20, 18, 40, 0.66);
            --section-glow: rgba(255, 255, 255, 0.08);

            display: grid;
            gap: 14px;
            padding: 14px;
            border-radius: 12px;
            border: 1px solid var(--section-border);
            background:
                radial-gradient(circle at 100% -26%, var(--section-glow), transparent 52%),
                linear-gradient(155deg, var(--section-bg-start), var(--section-bg-end));
            backdrop-filter: blur(4px);
        }

        .queue-section--overview {
            --section-border: rgba(255, 212, 71, 0.4);
            --section-bg-start: rgba(57, 44, 20, 0.62);
            --section-bg-end: rgba(30, 27, 48, 0.68);
            --section-glow: rgba(255, 212, 71, 0.18);
        }

        .queue-section--playback {
            --section-border: rgba(42, 216, 255, 0.56);
            --section-bg-start: rgba(16, 43, 76, 0.76);
            --section-bg-end: rgba(14, 24, 49, 0.8);
            --section-glow: rgba(42, 216, 255, 0.28);
            box-shadow:
                0 18px 32px rgba(4, 10, 30, 0.46),
                0 0 0 1px rgba(42, 216, 255, 0.2),
                0 0 36px rgba(42, 216, 255, 0.2);
        }

        .queue-section--manual {
            --section-border: rgba(42, 216, 255, 0.56);
            --section-bg-start: rgba(16, 43, 76, 0.76);
            --section-bg-end: rgba(14, 24, 49, 0.8);
            --section-glow: rgba(42, 216, 255, 0.28);
            box-shadow:
                0 18px 32px rgba(4, 10, 30, 0.46),
                0 0 0 1px rgba(42, 216, 255, 0.2),
                0 0 36px rgba(42, 216, 255, 0.2);
        }

        .queue-section--focus {
            --section-border: rgba(42, 216, 255, 0.56);
            --section-bg-start: rgba(16, 43, 76, 0.76);
            --section-bg-end: rgba(14, 24, 49, 0.8);
            --section-glow: rgba(42, 216, 255, 0.28);
            box-shadow:
                0 18px 32px rgba(4, 10, 30, 0.46),
                0 0 0 1px rgba(42, 216, 255, 0.2),
                0 0 36px rgba(42, 216, 255, 0.2);
        }

        .queue-section-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 2px 2px 11px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .queue-copy {
            display: grid;
            gap: 3px;
        }

        .queue-copy h2 {
            margin: 0;
            font-size: 1.08rem;
            line-height: 1.2;
            color: #fbfcff;
        }

        .queue-copy p {
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.32;
            color: rgba(236, 241, 255, 0.88);
        }

        .queue-copy .queue-copy-title--playback {
            font-size: 1.28rem;
            color: #b8f4ff;
            text-shadow: 0 0 10px rgba(42, 216, 255, 0.36), 0 0 22px rgba(42, 216, 255, 0.22);
            letter-spacing: 0.02em;
        }

        .queue-count {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 31px;
            min-width: 74px;
            padding: 0 10px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.24);
            background: rgba(255, 255, 255, 0.08);
            color: #f7f8ff;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .queue-section--playback .queue-count,
        .queue-section--manual .queue-count,
        .queue-section--focus .queue-count {
            border-color: rgba(42, 216, 255, 0.55);
            background: rgba(42, 216, 255, 0.15);
            color: #dbf9ff;
            box-shadow: 0 0 16px rgba(42, 216, 255, 0.2);
        }

        .queue-section--focus .queue-section-head {
            border-bottom-color: rgba(42, 216, 255, 0.26);
        }

        .queue-section--focus .queue-copy h2 {
            color: #b8f4ff;
            text-shadow: 0 0 10px rgba(42, 216, 255, 0.36), 0 0 22px rgba(42, 216, 255, 0.22);
            letter-spacing: 0.02em;
        }

        .queue-save-status {
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: rgba(236, 241, 255, 0.82);
            padding: 6px 8px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.06);
        }

        .queue-save-status[data-state="saving"] {
            border-color: rgba(42, 216, 255, 0.46);
            background: rgba(42, 216, 255, 0.14);
            color: #ddf9ff;
        }

        .queue-save-status[data-state="saved"] {
            border-color: rgba(93, 233, 171, 0.45);
            background: rgba(93, 233, 171, 0.14);
            color: #dfffee;
        }

        .queue-save-status[data-state="error"] {
            border-color: rgba(255, 98, 134, 0.46);
            background: rgba(255, 98, 134, 0.16);
            color: #ffdce5;
        }

        .queue-section--overview .queue-count {
            border-color: rgba(255, 212, 71, 0.5);
            background: rgba(255, 212, 71, 0.14);
            color: #fff0c2;
        }

        .queue-meta-grid {
            display: grid;
            gap: 10px 12px;
            grid-template-columns: repeat(auto-fit, minmax(185px, 1fr));
        }

        .queue-meta-grid .value {
            font-size: 1.02rem;
        }

        .queue-section--playback .queue-meta-grid .value {
            font-size: 1.08rem;
        }

        .queue-note {
            margin: 0;
            font-size: 0.86rem;
            color: rgba(236, 241, 255, 0.8);
        }

        .playback-controls {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: stretch;
            margin-top: 8px;
        }

        .playback-control-form {
            margin: 0;
            display: flex;
        }

        .playback-control-button {
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            transition: transform 120ms ease, background-color 120ms ease, box-shadow 120ms ease;
        }

        .playback-control-button svg {
            width: 21px;
            height: 21px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }

        .playback-control-button:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.15);
        }

        .playback-control-button[disabled] {
            opacity: 0.46;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .playback-control-button--toggle {
            width: 76px;
            min-width: 76px;
            min-height: 76px;
            padding: 0;
            border-radius: 18px;
            border-color: rgba(42, 216, 255, 0.72);
            background: linear-gradient(145deg, rgba(42, 216, 255, 0.36), rgba(42, 216, 255, 0.16));
            color: #dff9ff;
            box-shadow:
                0 0 0 1px rgba(42, 216, 255, 0.2),
                0 12px 22px rgba(5, 16, 37, 0.34),
                0 0 22px rgba(42, 216, 255, 0.24);
        }

        .playback-control-button--toggle svg {
            width: 36px;
            height: 36px;
        }

        .playback-control-button--play polygon {
            fill: currentColor;
            stroke: none;
        }

        .playback-control-button--pause {
            border-color: rgba(255, 212, 71, 0.68);
            background: linear-gradient(145deg, rgba(255, 212, 71, 0.34), rgba(255, 212, 71, 0.16));
            color: #fff3ce;
            box-shadow:
                0 0 0 1px rgba(255, 212, 71, 0.22),
                0 12px 22px rgba(5, 16, 37, 0.34),
                0 0 22px rgba(255, 212, 71, 0.22);
        }

        .playback-control-button--next {
            min-height: 76px;
            padding: 0 22px;
            border-color: rgba(255, 255, 255, 0.32);
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0.08));
            color: #f6fbff;
            box-shadow: 0 12px 22px rgba(5, 16, 37, 0.28);
        }

        .queue-manual-form {
            display: grid;
            gap: 10px 12px;
            grid-template-columns: minmax(0, 1fr) auto;
        }

        .queue-manual-fields {
            display: grid;
            gap: 10px;
        }

        .queue-manual-submit {
            display: flex;
            align-items: end;
        }

        .queue-manual-submit .button {
            min-height: 43px;
            padding: 0 16px;
            border-radius: 10px;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            box-shadow: 0 10px 18px rgba(7, 13, 28, 0.26);
        }

        .queue-manual-submit-button {
            border: 1px solid rgba(42, 216, 255, 0.64);
            background: linear-gradient(145deg, rgba(42, 216, 255, 0.36), rgba(42, 216, 255, 0.16));
            color: #dcf9ff;
            box-shadow:
                0 0 0 1px rgba(42, 216, 255, 0.2),
                0 10px 18px rgba(7, 13, 28, 0.26),
                0 0 18px rgba(42, 216, 255, 0.2);
        }

        .queue-manual-submit-button:hover {
            background: linear-gradient(145deg, rgba(42, 216, 255, 0.46), rgba(42, 216, 255, 0.24));
        }

        .queue-table {
            border-color: rgba(255, 255, 255, 0.16);
            background: rgba(8, 16, 36, 0.62);
            width: 100%;
            max-width: 100%;
            table-layout: fixed;
        }

        .queue-table--upcoming {
            min-width: 720px;
        }

        .queue-table--history {
            min-width: 620px;
        }

        .queue-table-wrap {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
        }

        .queue-table thead th {
            border-bottom-color: rgba(255, 255, 255, 0.14);
            background: rgba(255, 255, 255, 0.08);
            color: rgba(236, 241, 255, 0.86);
        }

        .queue-table tbody tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.03);
        }

        .queue-table tbody tr:hover {
            background: rgba(42, 216, 255, 0.08);
        }

        .queue-table td {
            border-bottom-color: rgba(255, 255, 255, 0.09);
            color: rgba(246, 248, 255, 0.96);
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .queue-table--upcoming td:nth-child(2),
        .queue-table--upcoming td:nth-child(3) {
            font-weight: 600;
        }

        .queue-table--upcoming th:nth-child(1),
        .queue-table--upcoming td:nth-child(1) {
            width: 58px;
            text-align: center;
        }

        .queue-table--upcoming th:nth-child(4),
        .queue-table--upcoming td:nth-child(4) {
            width: 132px;
        }

        .queue-table--upcoming th:nth-child(5),
        .queue-table--upcoming td:nth-child(5) {
            width: 278px;
        }

        .queue-table--upcoming td:last-child {
            min-width: 0;
        }

        .queue-table--history th:nth-child(1),
        .queue-table--history td:nth-child(1) {
            width: 92px;
        }

        .queue-table--history th:nth-child(4),
        .queue-table--history td:nth-child(4) {
            width: 124px;
        }

        .queue-table-actions {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: wrap;
        }

        .queue-row--movable {
            cursor: grab;
        }

        .queue-row--movable:active {
            cursor: grabbing;
        }

        .queue-row--dragging {
            opacity: 0.5;
        }

        .queue-row--drag-over {
            outline: 1px dashed rgba(42, 216, 255, 0.72);
            outline-offset: -2px;
        }

        .queue-reorder-controls {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 6px;
            border: 1px dashed rgba(42, 216, 255, 0.42);
            border-radius: 10px;
            background: rgba(42, 216, 255, 0.08);
        }

        .queue-order-button {
            width: 27px;
            height: 27px;
            border-radius: 8px;
            border: 1px solid rgba(42, 216, 255, 0.52);
            background: rgba(42, 216, 255, 0.16);
            color: #dff9ff;
            cursor: pointer;
            font-weight: 700;
            line-height: 1;
            padding: 0;
        }

        .queue-order-button:hover {
            background: rgba(42, 216, 255, 0.28);
        }

        .queue-order-button[disabled] {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .queue-drag-handle {
            font-size: 0.82rem;
            letter-spacing: -0.12em;
            color: rgba(219, 249, 255, 0.9);
            cursor: grab;
            user-select: none;
            padding-right: 2px;
        }

        .queue-lock-note {
            display: inline-block;
            border-radius: 999px;
            border: 1px solid rgba(255, 212, 71, 0.48);
            background: rgba(255, 212, 71, 0.16);
            color: #ffeebc;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 4px 8px;
        }

        .queue-action-button {
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            padding: 6px 11px;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            white-space: nowrap;
            color: #eef4ff;
            background: rgba(255, 255, 255, 0.08);
            cursor: pointer;
            transition: transform 120ms ease, background-color 120ms ease, border-color 120ms ease;
        }

        .queue-action-button:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.14);
        }

        .queue-action-button--skip {
            border-color: rgba(42, 216, 255, 0.48);
            background: rgba(42, 216, 255, 0.14);
            color: #ddf9ff;
        }

        .queue-action-button--cancel {
            border-color: rgba(255, 98, 134, 0.46);
            background: rgba(255, 98, 134, 0.14);
            color: #ffdce5;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        @media (max-width: 980px) {
            .queue-grid--top {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 760px) {
            .queue-copy .queue-copy-title--playback {
                font-size: 1.18rem;
            }

            .playback-control-button--toggle {
                width: 68px;
                min-width: 68px;
                min-height: 68px;
                border-radius: 16px;
            }

            .playback-control-button--next {
                min-height: 68px;
                padding: 0 16px;
            }

            .queue-manual-form {
                grid-template-columns: 1fr;
            }

            .queue-manual-submit {
                justify-content: flex-start;
            }

            .queue-table {
                display: table;
                white-space: normal;
            }

            .queue-table--upcoming {
                min-width: 680px;
            }

            .queue-table--history {
                min-width: 560px;
            }
        }
    </style>

    <div class="queue-page">
        <section class="queue-section queue-section--overview">
            <header class="queue-section-head">
                <div class="queue-copy">
                    <h2>Evento in gestione</h2>
                    <p>Panoramica rapida della serata attiva e delle informazioni principali.</p>
                </div>
                <span class="queue-count">{{ $eventStatusLabel }}</span>
            </header>
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
                    <div class="label">Secondi di pausa</div>
                    <div class="value">{{ $eventNight->break_seconds }}</div>
                </div>
                <div>
                    <div class="label">Attesa richieste (minuti)</div>
                    <div class="value">{{ $cooldownMinutes }}</div>
                </div>
            </div>
        </section>

        <div class="queue-grid queue-grid--top">
            <section class="queue-section queue-section--playback">
                <header class="queue-section-head">
                    <div class="queue-copy">
                        <h2 class="queue-copy-title--playback">Controllo riproduzione</h2>
                        <p>Due controlli principali: avvio/pausa e passaggio alla prossima canzone.</p>
                    </div>
                    <span class="queue-count" data-playback-status-badge>{{ $playbackStatusLabel }}</span>
                </header>

                <div class="queue-meta-grid">
                    <div>
                        <div class="label">Stato flusso</div>
                        <div class="value" data-playback-status-text>{{ $playbackStatusLabel }}</div>
                    </div>
                    <div>
                        <div class="label">Canzone corrente</div>
                        <div class="value" data-playback-current-song>{{ $playbackState?->currentRequest?->song?->title ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="label">Fine prevista</div>
                        <div class="value" data-playback-expected-end>
                            <span data-expected-end="{{ $expectedEndAt?->toIso8601String() ?? '' }}">{{ $expectedEndAt?->format('H:i:s') ?? '—' }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="label">Conteggi coda</div>
                        <div class="value" data-queue-counts-summary>
                            Corrente {{ $playbackState?->currentRequest ? 1 : 0 }} · Prossime {{ $queue->where('status', \App\Models\SongRequest::STATUS_QUEUED)->count() }} · Storico {{ $history->count() }}
                        </div>
                    </div>
                </div>

                <div class="playback-controls">
                    <form class="playback-control-form" method="POST" action="{{ $togglePlaybackRoute }}">
                        @csrf
                        <button
                            class="playback-control-button playback-control-button--toggle {{ $isPlaying ? 'playback-control-button--pause' : 'playback-control-button--play' }}"
                            type="submit"
                            aria-label="{{ $togglePlaybackTitle }}"
                            title="{{ $togglePlaybackTitle }}"
                        >
                            @if ($isPlaying)
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M8 6v12"></path>
                                    <path d="M16 6v12"></path>
                                </svg>
                                <span class="sr-only">Pausa</span>
                            @else
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <polygon points="8,6 18,12 8,18"></polygon>
                                </svg>
                                <span class="sr-only">Play</span>
                            @endif
                        </button>
                    </form>

                    <form class="playback-control-form" method="POST" action="{{ route('admin.queue.next', $eventNight) }}">
                        @csrf
                        <button class="playback-control-button playback-control-button--next" type="submit" aria-label="Passa alla prossima canzone" title="Passa alla prossima canzone">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m5 4 10 8-10 8V4Z"></path>
                                <path d="M19 5v14"></path>
                            </svg>
                            <span>Prossima canzone</span>
                        </button>
                    </form>
                </div>
            </section>

            <section class="queue-section queue-section--manual">
                <header class="queue-section-head">
                    <div class="queue-copy">
                        <h2>Aggiungi un partecipante</h2>
                        <p>Inserisci rapidamente un cantante presente in sala direttamente nella coda corrente.</p>
                    </div>
                </header>
                <form method="POST" action="{{ route('admin.queue.add', $eventNight) }}" class="queue-manual-form">
                    @csrf
                    <div class="queue-manual-fields">
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
                    </div>
                    <div class="queue-manual-submit">
                        <button class="button queue-manual-submit-button" type="submit">Aggiungi</button>
                    </div>
                </form>
            </section>
        </div>

        <div class="queue-grid queue-grid--tables">
            <section class="queue-section queue-section--focus">
                <header class="queue-section-head">
                    <div class="queue-copy">
                        <h2>Prossimi</h2>
                        <p>Riordina velocemente con i pulsanti o trascinando le righe. La canzone in riproduzione resta bloccata.</p>
                    </div>
                    <div style="display: inline-flex; align-items: center; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
                        <span class="queue-save-status" data-queue-state-badge data-state="idle" aria-live="polite">In attesa sync</span>
                        <span class="queue-save-status" data-queue-save-status data-state="idle" aria-live="polite">Pronto</span>
                        <span class="queue-count" data-queue-next-count>{{ $queue->where('status', \App\Models\SongRequest::STATUS_QUEUED)->count() }}</span>
                    </div>
                </header>
                <form id="queue-reorder-form" method="POST" action="{{ route('admin.queue.reorder', $eventNight) }}" class="sr-only">
                    @csrf
                    <div id="queue-reorder-inputs"></div>
                </form>
                <div class="queue-table-wrap">
                    <table class="queue-table queue-table--upcoming">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Partecipante</th>
                                <th>Canzone</th>
                                <th>Stato</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody data-queue-upcoming-body>
                        @forelse ($queue as $request)
                            @php($isQueued = $request->status === \App\Models\SongRequest::STATUS_QUEUED)
                            @php($isPlayingRequest = $request->status === \App\Models\SongRequest::STATUS_PLAYING)
                            <tr
                                data-queue-row
                                data-song-request-id="{{ $request->id }}"
                                data-movable="{{ $isQueued ? '1' : '0' }}"
                                class="{{ $isQueued ? 'queue-row--movable' : '' }}"
                                @if ($isQueued) draggable="true" @endif
                            >
                                <td>{{ $request->position ?? '—' }}</td>
                                <td>{{ $request->participant?->display_name ?? 'Ospite' }}</td>
                                <td>{{ $request->song?->title ?? 'Sconosciuta' }}</td>
                                <td><span class="pill">{{ $request->status }}</span></td>
                                <td>
                                    <div class="queue-table-actions">
                                        @if ($isQueued)
                                            <div class="queue-reorder-controls" aria-label="Riordina coda">
                                                <button class="queue-order-button" type="button" data-direction="up" aria-label="Sposta su" title="Sposta su">↑</button>
                                                <button class="queue-order-button" type="button" data-direction="down" aria-label="Sposta giù" title="Sposta giù">↓</button>
                                                <span class="queue-drag-handle" data-drag-handle aria-hidden="true" title="Trascina per riordinare">⋮⋮</span>
                                            </div>
                                        @elseif ($isPlayingRequest)
                                            <span class="queue-lock-note">In riproduzione</span>
                                        @endif
                                        <form method="POST" action="{{ route('admin.queue.skip', $eventNight) }}">
                                            @csrf
                                            <input type="hidden" name="song_request_id" value="{{ $request->id }}">
                                            <button class="queue-action-button queue-action-button--skip" type="submit">Salta</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.queue.cancel', $eventNight) }}">
                                            @csrf
                                            <input type="hidden" name="song_request_id" value="{{ $request->id }}">
                                            <button class="queue-action-button queue-action-button--cancel" type="submit">Annulla</button>
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
            </section>

            <section class="queue-section">
                <header class="queue-section-head">
                    <div class="queue-copy">
                        <h2>Riprodotte e saltate</h2>
                        <p>Storico recente delle canzoni gia processate.</p>
                    </div>
                </header>
                <div class="queue-table-wrap">
                    <table class="queue-table queue-table--history">
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
            </section>
        </div>
    </div>

    <script>
        (function () {
            const eventTimezone = @json($eventNight->venue?->timezone ?? config('app.timezone', 'Europe/Rome'));
            const queueStateUrl = @json(route('admin.queue.state', $eventNight));
            const playbackStatusLabels = @json($playbackStatusLabels);
            const formatTime = (date) => {
                try {
                    return date.toLocaleTimeString('it-IT', { timeZone: eventTimezone });
                } catch (error) {
                    return date.toLocaleTimeString('it-IT');
                }
            };

            document.querySelectorAll('[data-expected-end]').forEach((element) => {
                const isoValue = element.dataset.expectedEnd;
                const parsed = isoValue ? new Date(isoValue) : null;

                if (parsed && !Number.isNaN(parsed.getTime())) {
                    element.textContent = formatTime(parsed);
                }
            });

            const queueTableBody = document.querySelector('[data-queue-upcoming-body]');
            const reorderForm = document.getElementById('queue-reorder-form');
            const reorderInputs = document.getElementById('queue-reorder-inputs');
            const saveStatusElement = document.querySelector('[data-queue-save-status]');
            const playbackStatusBadge = document.querySelector('[data-playback-status-badge]');
            const playbackStatusText = document.querySelector('[data-playback-status-text]');
            const playbackCurrentSong = document.querySelector('[data-playback-current-song]');
            const playbackExpectedEnd = document.querySelector('[data-playback-expected-end] [data-expected-end]');
            const queueCountsSummary = document.querySelector('[data-queue-counts-summary]');
            const queueNextCount = document.querySelector('[data-queue-next-count]');
            const queueStateBadge = document.querySelector('[data-queue-state-badge]');

            if (!queueTableBody || !reorderForm || !reorderInputs) {
                return;
            }

            const getMovableRows = () => Array.from(
                queueTableBody.querySelectorAll('[data-queue-row][data-movable="1"]')
            );

            const serializeOrder = () => getMovableRows()
                .map((row) => row.dataset.songRequestId || '')
                .filter((value) => value !== '');

            let persistedOrderKey = serializeOrder().join(',');
            let saveInFlight = false;
            let pendingOrderedIds = null;
            let statusTimer = null;

            const setSaveStatus = (message, state = 'idle') => {
                if (!saveStatusElement) {
                    return;
                }

                saveStatusElement.textContent = message;
                saveStatusElement.dataset.state = state;
            };

            const setSavedStatusWithReset = () => {
                setSaveStatus('Salvato', 'saved');

                if (statusTimer) {
                    window.clearTimeout(statusTimer);
                }

                statusTimer = window.setTimeout(() => {
                    setSaveStatus('Pronto', 'idle');
                }, 1100);
            };

            const formatIsoTime = (isoString) => {
                if (!isoString) {
                    return '—';
                }

                const parsed = new Date(isoString);
                if (Number.isNaN(parsed.getTime())) {
                    return '—';
                }

                return formatTime(parsed);
            };

            const setQueueStateBadge = (message, state = 'idle') => {
                if (!queueStateBadge) {
                    return;
                }

                queueStateBadge.textContent = message;
                queueStateBadge.dataset.state = state;
            };

            const updateStateNodes = (payload) => {
                const playbackState = payload?.playback?.state || null;
                const playbackLabel = playbackStatusLabels[playbackState] || playbackState || '—';
                const currentTitle = payload?.playback?.current?.title || '—';
                const nextCount = payload?.counts?.next ?? null;
                const historyCount = payload?.counts?.history ?? null;
                const currentCount = payload?.counts?.current ?? 0;

                if (playbackStatusBadge) {
                    playbackStatusBadge.textContent = playbackLabel;
                }

                if (playbackStatusText) {
                    playbackStatusText.textContent = playbackLabel;
                }

                if (playbackCurrentSong) {
                    playbackCurrentSong.textContent = currentTitle;
                }

                if (playbackExpectedEnd) {
                    playbackExpectedEnd.dataset.expectedEnd = payload?.timestamps?.expected_end_at || '';
                    playbackExpectedEnd.textContent = formatIsoTime(payload?.timestamps?.expected_end_at || null);
                }

                if (queueNextCount && nextCount !== null) {
                    queueNextCount.textContent = String(nextCount);
                }

                if (queueCountsSummary) {
                    queueCountsSummary.textContent = `Corrente ${currentCount} · Prossime ${nextCount ?? '—'} · Storico ${historyCount ?? '—'}`;
                }

                setQueueStateBadge(`Aggiornato ${formatIsoTime(payload?.timestamps?.server_now || null)}`, 'saved');
            };

            let pollInFlight = false;

            const pollQueueState = async () => {
                if (pollInFlight || !window.fetch) {
                    return;
                }

                pollInFlight = true;

                try {
                    const response = await window.fetch(queueStateUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    updateStateNodes(payload);
                } catch (error) {
                    // fallback silenzioso: non bloccare la UI in caso di errori rete temporanei
                } finally {
                    pollInFlight = false;
                }
            };

            const refreshReorderButtonsState = () => {
                const rows = getMovableRows();

                rows.forEach((row, index) => {
                    const upButton = row.querySelector('[data-direction="up"]');
                    const downButton = row.querySelector('[data-direction="down"]');

                    if (upButton) {
                        upButton.disabled = index === 0;
                    }

                    if (downButton) {
                        downButton.disabled = index === rows.length - 1;
                    }
                });
            };

            const persistOrder = async (orderedIds) => {
                const nextOrderKey = orderedIds.join(',');
                if (nextOrderKey === persistedOrderKey) {
                    return;
                }

                if (saveInFlight) {
                    pendingOrderedIds = orderedIds;
                    return;
                }

                const csrfToken = reorderForm.querySelector('input[name="_token"]')?.value || '';
                if (!window.fetch || csrfToken === '') {
                    reorderInputs.replaceChildren();

                    orderedIds.forEach((songRequestId) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ordered_song_request_ids[]';
                        input.value = songRequestId;
                        reorderInputs.appendChild(input);
                    });

                    reorderForm.submit();
                    return;
                }

                saveInFlight = true;
                setSaveStatus('Salvataggio…', 'saving');

                const payload = new URLSearchParams();
                payload.append('_token', csrfToken);
                orderedIds.forEach((songRequestId) => {
                    payload.append('ordered_song_request_ids[]', songRequestId);
                });

                try {
                    const response = await window.fetch(reorderForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: payload.toString(),
                    });

                    if (!response.ok) {
                        throw new Error('save-failed');
                    }

                    persistedOrderKey = nextOrderKey;
                    setSavedStatusWithReset();
                } catch (error) {
                    setSaveStatus('Errore salvataggio', 'error');
                } finally {
                    saveInFlight = false;

                    if (pendingOrderedIds) {
                        const pending = pendingOrderedIds;
                        pendingOrderedIds = null;
                        persistOrder(pending);
                    }
                }
            };

            const submitOrder = () => {
                const orderedIds = serializeOrder();

                if (orderedIds.length === 0) {
                    return;
                }

                persistOrder(orderedIds);
            };

            const moveRow = (row, direction) => {
                if (!row || row.dataset.movable !== '1') {
                    return;
                }

                const movableRows = getMovableRows();
                const index = movableRows.indexOf(row);
                if (index < 0) {
                    return;
                }

                const targetIndex = direction === 'up' ? index - 1 : index + 1;
                if (targetIndex < 0 || targetIndex >= movableRows.length) {
                    return;
                }

                const targetRow = movableRows[targetIndex];
                if (direction === 'up') {
                    queueTableBody.insertBefore(row, targetRow);
                } else {
                    queueTableBody.insertBefore(targetRow, row);
                }

                refreshReorderButtonsState();
                submitOrder();
            };

            queueTableBody.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }

                const button = target.closest('.queue-order-button');
                if (!button) {
                    return;
                }

                const row = button.closest('[data-queue-row]');
                const direction = button.getAttribute('data-direction') === 'up' ? 'up' : 'down';
                moveRow(row, direction);
            });

            let draggedRow = null;
            let dragArmedRow = null;

            queueTableBody.addEventListener('pointerdown', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    dragArmedRow = null;
                    return;
                }

                const handle = target.closest('[data-drag-handle]');
                dragArmedRow = handle ? handle.closest('[data-queue-row][data-movable="1"]') : null;
            });

            const clearDragState = () => {
                if (draggedRow) {
                    draggedRow.classList.remove('queue-row--dragging');
                }

                queueTableBody.querySelectorAll('.queue-row--drag-over').forEach((row) => {
                    row.classList.remove('queue-row--drag-over');
                });

                draggedRow = null;
            };

            queueTableBody.addEventListener('dragstart', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }

                const row = target.closest('[data-queue-row][data-movable="1"]');
                if (!row || dragArmedRow !== row) {
                    event.preventDefault();
                    return;
                }

                draggedRow = row;
                draggedRow.classList.add('queue-row--dragging');

                if (event.dataTransfer) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', row.dataset.songRequestId || '');
                }
            });

            queueTableBody.addEventListener('dragover', (event) => {
                if (!draggedRow) {
                    return;
                }

                event.preventDefault();

                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }

                const targetRow = target.closest('[data-queue-row][data-movable="1"]');
                if (!targetRow || targetRow === draggedRow) {
                    return;
                }

                queueTableBody.querySelectorAll('.queue-row--drag-over').forEach((row) => {
                    if (row !== targetRow) {
                        row.classList.remove('queue-row--drag-over');
                    }
                });

                targetRow.classList.add('queue-row--drag-over');

                const targetRect = targetRow.getBoundingClientRect();
                const shouldInsertAfter = event.clientY > targetRect.top + (targetRect.height / 2);

                if (shouldInsertAfter) {
                    if (targetRow.nextSibling !== draggedRow) {
                        queueTableBody.insertBefore(draggedRow, targetRow.nextSibling);
                    }
                } else if (targetRow !== draggedRow.nextSibling) {
                    queueTableBody.insertBefore(draggedRow, targetRow);
                }

                refreshReorderButtonsState();
            });

            queueTableBody.addEventListener('drop', (event) => {
                if (!draggedRow) {
                    return;
                }

                event.preventDefault();
                clearDragState();
                submitOrder();
            });

            queueTableBody.addEventListener('dragend', () => {
                clearDragState();
                dragArmedRow = null;
                submitOrder();
            });

            refreshReorderButtonsState();
            pollQueueState();
            window.setInterval(pollQueueState, 4000);
        })();
    </script>
@endsection
