@extends('admin.layout')

@php
    $formatDate = static function ($date): string {
        if (! $date) {
            return '—';
        }

        return \Illuminate\Support\Str::ucfirst($date->copy()->locale('it')->isoFormat('dddd D MMMM YYYY [alle] HH:mm'));
    };

    $statusLabel = static fn ($event) => \App\Models\EventNight::STATUS_LABELS[$event->status] ?? $event->status;
@endphp

@section('without_content_card', '1')

@section('content')
    <style>
        .events-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: clamp(18px, 2.6vw, 28px);
            flex-wrap: wrap;
        }

        .events-page {
            display: grid;
            gap: clamp(24px, 3.5vw, 42px);
        }

        .event-section {
            display: grid;
            gap: 14px;
        }

        .section-head {
            display: grid;
            gap: 4px;
            padding: 13px 15px;
            border-radius: 15px;
            border: 1px solid;
            box-shadow: 0 10px 24px rgba(8, 8, 23, 0.28);
        }

        .section-head h2 {
            margin: 0;
            font-size: 1.15rem;
            color: #ffffff;
        }

        .section-head p {
            margin: 0;
            color: rgba(244, 248, 255, 0.9);
            font-size: 0.94rem;
        }

        .section-head--ongoing {
            border-color: rgba(42, 216, 255, 0.42);
            background:
                radial-gradient(circle at 14% 16%, rgba(42, 216, 255, 0.28), transparent 45%),
                radial-gradient(circle at 88% 18%, rgba(255, 79, 216, 0.24), transparent 48%),
                rgba(22, 28, 64, 0.72);
        }

        .section-head--ongoing h2 {
            font-size: 1.35rem;
        }

        .section-head--future {
            border-color: rgba(255, 212, 71, 0.46);
            background:
                radial-gradient(circle at 12% 24%, rgba(255, 212, 71, 0.22), transparent 44%),
                radial-gradient(circle at 86% 20%, rgba(42, 216, 255, 0.18), transparent 45%),
                rgba(53, 33, 13, 0.58);
        }

        .section-head--past {
            border-color: rgba(255, 128, 163, 0.38);
            background:
                radial-gradient(circle at 14% 22%, rgba(255, 128, 163, 0.2), transparent 48%),
                radial-gradient(circle at 88% 18%, rgba(182, 150, 255, 0.2), transparent 44%),
                rgba(39, 30, 54, 0.6);
        }

        .events-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        }

        .events-grid--ongoing {
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 16px;
        }

        .event-card {
            display: grid;
            gap: 10px;
            padding: 14px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 13px;
            background: rgba(255, 255, 255, 0.04);
        }

        .event-card--ongoing {
            padding: 18px;
            border-color: rgba(42, 216, 255, 0.3);
            background:
                radial-gradient(circle at 12% 20%, rgba(42, 216, 255, 0.14), transparent 48%),
                rgba(11, 17, 39, 0.52);
            box-shadow: 0 12px 28px rgba(8, 8, 24, 0.28);
        }

        .event-card--secondary {
            padding: 12px;
        }

        .event-main {
            display: flex;
            align-items: center;
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
            font-size: 1.14rem;
        }

        .event-subtitle {
            font-size: 0.9rem;
            color: var(--muted);
        }

        .event-card--ongoing .event-subtitle {
            font-size: 0.98rem;
        }

        .event-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 8px 14px;
        }

        .event-meta-item .label {
            margin-bottom: 2px;
        }

        .event-meta-item .value {
            font-weight: 600;
            font-size: 0.96rem;
        }

        .event-card--ongoing .event-meta-item .value {
            font-size: 1.02rem;
        }

        .event-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 2px;
        }

        @media (max-width: 900px) {
            .events-grid {
                grid-template-columns: 1fr;
            }

            .events-grid--ongoing {
                grid-template-columns: 1fr;
            }

            .section-head--ongoing h2 {
                font-size: 1.2rem;
            }
        }

        .icon-button {
            width: 38px;
            height: 38px;
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

        .event-card--ongoing .icon-button {
            width: 41px;
            height: 41px;
        }
    </style>

    <div class="events-header">
        <h1 style="margin: 0;">Eventi</h1>
        <a class="button" href="{{ route('admin.events.create') }}">Crea evento</a>
    </div>

    <div class="events-page">
        <section class="event-section event-section--ongoing">
            <header class="section-head section-head--ongoing">
                <h2>Eventi in corso</h2>
                <p>Serate attualmente attive, in evidenza per monitoraggio e azioni rapide.</p>
            </header>
            @if ($ongoingEvents->isEmpty())
                <div class="panel muted">Nessun evento in corso.</div>
            @else
                <div class="events-grid events-grid--ongoing">
                    @foreach ($ongoingEvents as $event)
                        <article class="event-card event-card--ongoing">
                            <div class="event-main">
                                <div>
                                    <div class="event-title">{{ $event->venue?->name ?? 'Location non definita' }}</div>
                                    <div class="event-subtitle">Codice evento: <strong>{{ $event->code }}</strong></div>
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
                                <a class="icon-button" href="{{ route('admin.events.edit', $event) }}" aria-label="Modifica evento" title="Modifica evento">
                                    <svg viewBox="0 0 24 24"><path d="M4 20h4l10-10-4-4L4 16v4z"></path><path d="M13 7l4 4"></path></svg>
                                </a>
                                <a class="icon-button queue" href="{{ route('admin.queue.show', $event) }}" aria-label="Apri coda" title="Apri coda">
                                    <svg viewBox="0 0 24 24"><path d="M4 6h16"></path><path d="M4 12h16"></path><path d="M4 18h10"></path></svg>
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

        <section class="event-section">
            <header class="section-head section-head--future">
                <h2>Eventi futuri</h2>
                <p>Programmazione prossima con tutte le serate già pianificate.</p>
            </header>
            @if ($futureEvents->isEmpty())
                <div class="panel muted">Nessun evento futuro programmato.</div>
            @else
                <div class="events-grid">
                    @foreach ($futureEvents as $event)
                        <article class="event-card event-card--secondary">
                            <div class="event-main">
                                <div>
                                    <div class="event-title">{{ $event->venue?->name ?? 'Location non definita' }}</div>
                                    <div class="event-subtitle">Codice evento: <strong>{{ $event->code }}</strong></div>
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
                                <a class="icon-button" href="{{ route('admin.events.edit', $event) }}" aria-label="Modifica evento" title="Modifica evento">
                                    <svg viewBox="0 0 24 24"><path d="M4 20h4l10-10-4-4L4 16v4z"></path><path d="M13 7l4 4"></path></svg>
                                </a>
                                <a class="icon-button queue" href="{{ route('admin.queue.show', $event) }}" aria-label="Apri coda" title="Apri coda">
                                    <svg viewBox="0 0 24 24"><path d="M4 6h16"></path><path d="M4 12h16"></path><path d="M4 18h10"></path></svg>
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

        <section class="event-section">
            <header class="section-head section-head--past">
                <h2>Eventi passati</h2>
                <p>Storico serate concluse, utile per consultazione e verifica.</p>
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
                                    <div class="event-subtitle">Codice evento: <strong>{{ $event->code }}</strong></div>
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
                                <a class="icon-button" href="{{ route('admin.events.edit', $event) }}" aria-label="Modifica evento" title="Modifica evento">
                                    <svg viewBox="0 0 24 24"><path d="M4 20h4l10-10-4-4L4 16v4z"></path><path d="M13 7l4 4"></path></svg>
                                </a>
                                <a class="icon-button queue" href="{{ route('admin.queue.show', $event) }}" aria-label="Apri coda" title="Apri coda">
                                    <svg viewBox="0 0 24 24"><path d="M4 6h16"></path><path d="M4 12h16"></path><path d="M4 18h10"></path></svg>
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
