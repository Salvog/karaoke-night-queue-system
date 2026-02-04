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
                {{ $songs->count() }} risultati trovati
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
