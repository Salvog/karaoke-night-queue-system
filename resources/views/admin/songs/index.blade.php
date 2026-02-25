@extends('admin.layout')

@section('content')
    <div class="actions" style="justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin: 0;">Canzoni</h1>
            <div class="helper">Modifica o rimuovi le canzoni dal catalogo.</div>
        </div>
    </div>
    <div class="grid two" style="margin-top: 16px;">
        <div class="panel">
            <div class="actions" style="justify-content: space-between; align-items: center;">
                <h2 style="margin: 0;">Aggiungi nuova canzone</h2>
                <button class="button secondary" type="button" id="toggle-song-form">Aggiungi canzone</button>
            </div>
            <div class="helper" style="margin-bottom: 12px;">Compila i campi per inserire una nuova canzone.</div>
            <form method="POST" action="{{ route('admin.songs.store') }}" class="form-grid" id="song-create-form" style="display: {{ $errors->any() ? 'grid' : 'none' }};">
                @csrf
                @include('admin.songs.form', ['song' => $newSong])
                <div class="actions">
                    <button class="button success" type="submit">Aggiungi canzone</button>
                </div>
            </form>
        </div>
        <div class="panel muted">
            <h2 style="margin: 0 0 8px;">Filtri</h2>
            <form method="GET" action="{{ route('admin.songs.index') }}" class="form-grid">
                <div>
                    <label for="filter_title">Titolo</label>
                    <input id="filter_title" type="text" name="title" value="{{ $filters['title'] ?? '' }}">
                </div>
                <div>
                    <label for="filter_artist">Artista</label>
                    <input id="filter_artist" type="text" name="artist" value="{{ $filters['artist'] ?? '' }}">
                </div>
                <div class="actions">
                    <button class="button secondary" type="submit">Applica filtri</button>
                    <a class="button" href="{{ route('admin.songs.index') }}">Reimposta</a>
                </div>
            </form>
            <div class="helper" style="margin-top: 10px;">
                {{ $songs->total() }} risultati trovati
            </div>
        </div>
    </div>
    <div class="divider"></div>
    <table>
        <thead>
            <tr>
                <th>Titolo</th>
                <th>Artista</th>
                <th>Durata</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($songs as $song)
            <tr>
                <td>{{ $song->title }}</td>
                <td>{{ $song->artist ?? 'Sconosciuto' }}</td>
                <td>{{ $song->duration_seconds }}s</td>
                <td>
                    <div class="actions">
                        <a class="button secondary" href="{{ route('admin.songs.edit', $song) }}">Modifica</a>
                        <form method="POST" action="{{ route('admin.songs.destroy', $song) }}">
                            @csrf
                            @method('DELETE')
                            <button class="button danger" type="submit">Elimina</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">Nessuna canzone disponibile.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    @if ($songs->lastPage() > 1)
        <style>
            .songs-pagination {
                margin-top: 14px;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
                gap: 6px;
            }

            .songs-pagination__link,
            .songs-pagination__current,
            .songs-pagination__dots {
                min-width: 34px;
                height: 34px;
                border-radius: 9px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0 10px;
                font-size: 13px;
                font-weight: 600;
                text-decoration: none;
                line-height: 1;
            }

            .songs-pagination__link {
                color: #e4e8ff;
                border: 1px solid rgba(255, 255, 255, 0.22);
                background: rgba(255, 255, 255, 0.06);
            }

            .songs-pagination__link:hover {
                background: rgba(255, 255, 255, 0.14);
            }

            .songs-pagination__link--muted {
                opacity: 0.45;
                pointer-events: none;
            }

            .songs-pagination__current {
                color: #11152f;
                border: 1px solid rgba(42, 216, 255, 0.6);
                background: linear-gradient(180deg, rgba(87, 233, 255, 0.96), rgba(42, 216, 255, 0.88));
            }

            .songs-pagination__dots {
                color: rgba(228, 232, 255, 0.64);
            }
        </style>

        <nav class="songs-pagination" aria-label="Paginazione canzoni">
            @if ($songs->onFirstPage())
                <span class="songs-pagination__link songs-pagination__link--muted" aria-disabled="true">←</span>
            @else
                <a class="songs-pagination__link" href="{{ $songs->previousPageUrl() }}" rel="prev" aria-label="Pagina precedente">←</a>
            @endif

            @php
                $startPage = max(1, $songs->currentPage() - 2);
                $endPage = min($songs->lastPage(), $songs->currentPage() + 2);
            @endphp

            @if ($startPage > 1)
                <a class="songs-pagination__link" href="{{ $songs->url(1) }}">1</a>
                @if ($startPage > 2)
                    <span class="songs-pagination__dots" aria-hidden="true">…</span>
                @endif
            @endif

            @foreach (range($startPage, $endPage) as $page)
                @if ($page === $songs->currentPage())
                    <span class="songs-pagination__current" aria-current="page">{{ $page }}</span>
                @else
                    <a class="songs-pagination__link" href="{{ $songs->url($page) }}">{{ $page }}</a>
                @endif
            @endforeach

            @if ($endPage < $songs->lastPage())
                @if ($endPage < $songs->lastPage() - 1)
                    <span class="songs-pagination__dots" aria-hidden="true">…</span>
                @endif
                <a class="songs-pagination__link" href="{{ $songs->url($songs->lastPage()) }}">{{ $songs->lastPage() }}</a>
            @endif

            @if ($songs->hasMorePages())
                <a class="songs-pagination__link" href="{{ $songs->nextPageUrl() }}" rel="next" aria-label="Pagina successiva">→</a>
            @else
                <span class="songs-pagination__link songs-pagination__link--muted" aria-disabled="true">→</span>
            @endif
        </nav>
    @endif

    <script>
        (function () {
            const toggle = document.getElementById('toggle-song-form');
            const form = document.getElementById('song-create-form');

            if (!toggle || !form) {
                return;
            }

            const shouldShow = form.style.display !== 'none';
            toggle.textContent = shouldShow ? 'Chiudi' : 'Aggiungi canzone';

            toggle.addEventListener('click', function () {
                const isHidden = form.style.display === 'none';
                form.style.display = isHidden ? 'grid' : 'none';
                toggle.textContent = isHidden ? 'Chiudi' : 'Aggiungi canzone';
            });
        })();
    </script>
@endsection
