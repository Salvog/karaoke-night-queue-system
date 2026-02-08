@extends('admin.layout')

@php
    $formatDate = static function ($date): string {
        if (! $date) {
            return '—';
        }

        return \Illuminate\Support\Str::ucfirst($date->copy()->locale('it')->isoFormat('dddd D MMMM YYYY [alle] HH:mm'));
    };

    $statusLabel = static fn ($event) => \App\Models\EventNight::STATUS_LABELS[$event->status] ?? $event->status;

    $eventCountLabel = static function (int $count): string {
        return $count === 1 ? '1 serata' : $count.' serate';
    };
@endphp

@section('without_content_card', '1')

@section('content')
    <style>
        .events-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: clamp(14px, 2.2vw, 24px);
            flex-wrap: wrap;
        }

        .events-page {
            display: grid;
            gap: clamp(18px, 2.5vw, 28px);
        }

        .event-section {
            --section-border: rgba(255, 255, 255, 0.2);
            --section-bg-start: rgba(28, 31, 63, 0.72);
            --section-bg-end: rgba(20, 18, 40, 0.66);
            --section-glow: rgba(255, 255, 255, 0.08);

            display: grid;
            gap: 12px;
            padding: 12px;
            border-radius: 4px;
            border: 1px solid var(--section-border);
            background:
                radial-gradient(circle at 100% -24%, var(--section-glow), transparent 52%),
                linear-gradient(155deg, var(--section-bg-start), var(--section-bg-end));
            backdrop-filter: blur(4px);
        }

        .event-section--ongoing {
            --section-border: rgba(42, 216, 255, 0.44);
            --section-bg-start: rgba(16, 56, 95, 0.7);
            --section-bg-end: rgba(44, 26, 78, 0.72);
            --section-glow: rgba(42, 216, 255, 0.24);

            gap: 14px;
            padding: 14px;
            box-shadow: 0 14px 34px rgba(8, 10, 28, 0.3);
        }

        .event-section--future {
            --section-border: rgba(255, 212, 71, 0.44);
            --section-bg-start: rgba(58, 40, 16, 0.68);
            --section-bg-end: rgba(28, 44, 58, 0.66);
            --section-glow: rgba(255, 212, 71, 0.2);
        }

        .event-section--past {
            --section-border: rgba(255, 128, 163, 0.4);
            --section-bg-start: rgba(57, 34, 63, 0.66);
            --section-bg-end: rgba(31, 32, 52, 0.66);
            --section-glow: rgba(255, 128, 163, 0.18);
        }

        .section-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 2px 2px 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .section-copy {
            display: grid;
            gap: 3px;
            min-width: 0;
        }

        .section-copy h2 {
            margin: 0;
            font-size: 1.08rem;
            line-height: 1.2;
            color: #fbfcff;
        }

        .section-copy p {
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.3;
            color: rgba(236, 241, 255, 0.88);
            max-width: 66ch;
        }

        .event-section--ongoing .section-copy h2 {
            font-size: 1.24rem;
            color: #e6faff;
        }

        .event-section--ongoing .section-copy p {
            color: rgba(232, 247, 255, 0.9);
        }

        .event-section--future .section-copy h2 {
            color: #ffe8b1;
        }

        .event-section--past .section-copy h2 {
            color: #ffd2e3;
        }

        .section-count {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 30px;
            min-width: 72px;
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

        .event-section--ongoing .section-count {
            border-color: rgba(42, 216, 255, 0.5);
            background: rgba(42, 216, 255, 0.16);
            color: #dff8ff;
        }

        .event-section--future .section-count {
            border-color: rgba(255, 212, 71, 0.45);
            background: rgba(255, 212, 71, 0.14);
            color: #fff0c4;
        }

        .event-section--past .section-count {
            border-color: rgba(255, 128, 163, 0.42);
            background: rgba(255, 128, 163, 0.12);
            color: #ffd8e7;
        }

        .events-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }

        .events-grid--ongoing {
            grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
        }

        .event-card {
            display: grid;
            gap: 9px;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            background: rgba(18, 24, 52, 0.5);
        }

        .event-card--ongoing {
            gap: 10px;
            padding: 14px;
            border-color: rgba(42, 216, 255, 0.36);
            background:
                radial-gradient(circle at 10% 20%, rgba(42, 216, 255, 0.12), transparent 50%),
                rgba(10, 27, 57, 0.58);
            box-shadow: 0 10px 22px rgba(8, 9, 22, 0.28);
        }

        .event-main {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .event-title {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
        }

        .event-card--ongoing .event-title {
            font-size: 1.1rem;
        }

        .event-subtitle {
            font-size: 0.89rem;
            color: var(--muted);
        }

        .event-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 8px 12px;
        }

        .event-meta-item .label {
            margin-bottom: 2px;
        }

        .event-meta-item .value {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .event-card--ongoing .event-meta-item .value {
            font-size: 1rem;
        }

        .event-actions {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: wrap;
            margin-top: 1px;
        }

        .icon-button {
            width: 37px;
            height: 37px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 120ms ease, transform 120ms ease;
        }

        .event-card--ongoing .icon-button {
            width: 39px;
            height: 39px;
        }

        .icon-button svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .icon-button:hover {
            background: rgba(255, 255, 255, 0.17);
            transform: translateY(-1px);
        }

        .icon-button.queue {
            color: #b8f4ff;
            border-color: rgba(42, 216, 255, 0.45);
            background: rgba(42, 216, 255, 0.14);
        }

        .icon-button.theme {
            color: #ffe79f;
            border-color: rgba(255, 212, 71, 0.5);
            background: rgba(255, 212, 71, 0.14);
        }

        .icon-button.danger {
            color: #ffd4de;
            border-color: rgba(255, 98, 134, 0.42);
            background: rgba(255, 98, 134, 0.12);
        }

        @media (max-width: 900px) {
            .events-grid,
            .events-grid--ongoing {
                grid-template-columns: 1fr;
            }

            .section-copy h2 {
                font-size: 1.04rem;
            }

            .event-section--ongoing .section-copy h2 {
                font-size: 1.14rem;
            }
        }

        @media (max-width: 620px) {
            .section-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .section-count {
                min-height: 28px;
            }

            .event-card {
                padding: 11px;
            }

            .icon-button,
            .event-card--ongoing .icon-button {
                width: 36px;
                height: 36px;
            }
        }
    </style>

    <div class="events-header"></div>

    <div class="events-page">
        @if ($ongoingEvents->isEmpty())
            <div class="panel muted">Nessun evento in corso.</div>
            <a class="button" href="{{ route('admin.events.create') }}">Crea evento</a>
        @else
            <div class="events-grid events-grid--ongoing">
                @foreach ($ongoingEvents as $event)
                    <article class="event-card event-card--ongoing">
                        <header class="section-head">
                            <div class="section-copy">
                                <h1 style="margin: 0;">Evento In Corso</h1>
                            </div>
                            
                        </header>
                        <div class="event-main">
                            <div>
                                <div class="event-subtitle">{{ $event->code }}</div>
                                <div class="event-title">{{ $event->venue?->name ?? 'Location non definita' }}</div>
                            </div>
                            <span class="pill">{{ $statusLabel($event) }}</span>
                        </div>

                        <div class="event-meta">
                            <div class="event-meta-item">
                                <div class="label">Inizio</div>
                                <div class="value">{{ $formatDate($event->starts_at) }}</div>
                            </div>
                        </div>
                        <div class="event-actions">
                            <a class="icon-button queue" href="{{ route('admin.queue.show', $event) }}" aria-label="Apri coda" title="Apri coda">
                                <svg viewBox="0 0 24 24"><path d="M4 6h16"></path><path d="M4 12h16"></path><path d="M4 18h10"></path></svg>
                            </a>
                            <a class="icon-button" href="{{ route('admin.events.edit', $event) }}" aria-label="Modifica evento" title="Modifica evento">
                                <svg viewBox="0 0 24 24"><path d="M4 20h4l10-10-4-4L4 16v4z"></path><path d="M13 7l4 4"></path></svg>
                            </a>
                            <a class="icon-button theme" href="{{ route('admin.theme.show', $event) }}" aria-label="Apri tema e annunci" title="Apri tema e annunci">
                                <svg viewBox="0 0 24 24"><path d="M12 3v6"></path><path d="M8 6h8"></path><path d="M6 12a6 6 0 1 0 12 0 6 6 0 0 0-12 0z"></path></svg>
                            </a>
                            @if ($adminUser->isAdmin())
                                <form method="POST" action="{{ route('admin.events.destroy', $event) }}" class="js-delete-event-form" data-event-code="{{ $event->code }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="icon-button danger" type="submit" aria-label="Elimina evento" title="Elimina evento">
                                        <svg viewBox="0 0 24 24"><path d="M4 7h16"></path><path d="M8 7V5h8v2"></path><path d="M7 7l1 12h8l1-12"></path></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <br><br>
        
        <section class="event-section event-section--future">
            <header class="section-head">
                <div class="section-copy">
                    <h2>Eventi futuri</h2>
                    <p>Programmazione prossima con tutte le serate gia pianificate.</p>
                </div>
                <span class="section-count">{{ $eventCountLabel($futureEvents->count()) }}</span>
            </header>
            @if ($futureEvents->isEmpty())
                <div class="panel muted">Nessun evento futuro programmato.</div>
            @else
                <div class="events-grid">
                    @foreach ($futureEvents as $event)
                        <article class="event-card event-card--secondary">
                            <div class="event-main">
                                <div>
                                    <div class="event-subtitle">{{ $event->code }}</div>
                                    <div class="event-title">{{ $event->venue?->name ?? 'Location non definita' }}</div>
                                </div>
                                <span class="pill">{{ $statusLabel($event) }}</span>
                            </div>
                            <div class="event-meta">
                                <div class="event-meta-item">
                                    <div class="label">Inizio</div>
                                    <div class="value">{{ $formatDate($event->starts_at) }}</div>
                                </div>
                            </div>
                            <div class="event-actions">
                                <a class="icon-button queue" href="{{ route('admin.queue.show', $event) }}" aria-label="Apri coda" title="Apri coda">
                                    <svg viewBox="0 0 24 24"><path d="M4 6h16"></path><path d="M4 12h16"></path><path d="M4 18h10"></path></svg>
                                </a>
                                <a class="icon-button" href="{{ route('admin.events.edit', $event) }}" aria-label="Modifica evento" title="Modifica evento">
                                    <svg viewBox="0 0 24 24"><path d="M4 20h4l10-10-4-4L4 16v4z"></path><path d="M13 7l4 4"></path></svg>
                                </a>
                                <a class="icon-button theme" href="{{ route('admin.theme.show', $event) }}" aria-label="Apri tema e annunci" title="Apri tema e annunci">
                                    <svg viewBox="0 0 24 24"><path d="M12 3v6"></path><path d="M8 6h8"></path><path d="M6 12a6 6 0 1 0 12 0 6 6 0 0 0-12 0z"></path></svg>
                                </a>
                                @if ($adminUser->isAdmin())
                                    <form method="POST" action="{{ route('admin.events.destroy', $event) }}" class="js-delete-event-form" data-event-code="{{ $event->code }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="icon-button danger" type="submit" aria-label="Elimina evento" title="Elimina evento">
                                            <svg viewBox="0 0 24 24"><path d="M4 7h16"></path><path d="M8 7V5h8v2"></path><path d="M7 7l1 12h8l1-12"></path></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="event-section event-section--past">
            <header class="section-head">
                <div class="section-copy">
                    <h2>Eventi passati</h2>
                    <p>Storico serate concluse, utile per consultazione e verifica.</p>
                </div>
                <span class="section-count">{{ $eventCountLabel($pastEvents->count()) }}</span>
            </header>
            @if ($pastEvents->isEmpty())
                <div class="panel muted">Nessun evento passato.</div>
            @else
                <div class="events-grid">
                    @foreach ($pastEvents as $event)
                        <article class="event-card event-card--secondary">
                            <div class="event-main">
                                <div>
                                    <div class="event-title">{{ $event->venue?->name ?? 'Location non definita' }}</div>
                                </div>
                            </div>
                            <div class="event-meta">
                                <div class="event-meta-item">
                                    <div class="label">Inizio</div>
                                    <div class="value">{{ $formatDate($event->starts_at) }}</div>
                                </div>
                            </div>
                            <div class="event-actions">
                                <a class="icon-button queue" href="{{ route('admin.queue.show', $event) }}" aria-label="Apri coda" title="Apri coda">
                                    <svg viewBox="0 0 24 24"><path d="M4 6h16"></path><path d="M4 12h16"></path><path d="M4 18h10"></path></svg>
                                </a>
                                <a class="icon-button" href="{{ route('admin.events.edit', $event) }}" aria-label="Modifica evento" title="Modifica evento">
                                    <svg viewBox="0 0 24 24"><path d="M4 20h4l10-10-4-4L4 16v4z"></path><path d="M13 7l4 4"></path></svg>
                                </a>
                                <a class="icon-button theme" href="{{ route('admin.theme.show', $event) }}" aria-label="Apri tema e annunci" title="Apri tema e annunci">
                                    <svg viewBox="0 0 24 24"><path d="M12 3v6"></path><path d="M8 6h8"></path><path d="M6 12a6 6 0 1 0 12 0 6 6 0 0 0-12 0z"></path></svg>
                                </a>
                                @if ($adminUser->isAdmin())
                                    <form method="POST" action="{{ route('admin.events.destroy', $event) }}" class="js-delete-event-form" data-event-code="{{ $event->code }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="icon-button danger" type="submit" aria-label="Elimina evento" title="Elimina evento">
                                            <svg viewBox="0 0 24 24"><path d="M4 7h16"></path><path d="M8 7V5h8v2"></path><path d="M7 7l1 12h8l1-12"></path></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <script>
        document.querySelectorAll('.js-delete-event-form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                const eventCode = form.dataset.eventCode ?? '';
                const confirmed = window.confirm(`Confermi l'eliminazione dell'evento ${eventCode}?`);
                if (!confirmed) {
                    event.preventDefault();
                    return;
                }

                const phrase = window.prompt('Scrivi "elimina" per confermare definitivamente.');
                if ((phrase || '').trim().toLowerCase() !== 'elimina') {
                    event.preventDefault();
                    window.alert('Conferma non valida. Evento non eliminato.');
                }
            });
        });
    </script>
@endsection
