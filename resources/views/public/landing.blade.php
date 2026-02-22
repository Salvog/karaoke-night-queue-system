<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karaoke Night | Partecipa</title>
    <style>
        :root {
            color-scheme: dark;
        }
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; background: #0f172a; color: #f8fafc; }
        header { padding: 24px 16px; background: #111827; border-bottom: 1px solid #1f2937; }
        main { padding: 20px 16px 28px; max-width: 900px; margin: 0 auto; }
        h1 { margin: 0 0 8px; font-size: 28px; }
        h2 { margin-top: 0; }
        .card { background: #1f2937; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        .song { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #374151; }
        .song:last-child { border-bottom: none; }
        .button { background: #38bdf8; color: #0f172a; border: none; padding: 10px 14px; border-radius: 8px; cursor: pointer; font-weight: 700; }
        .button.secondary { background: #64748b; color: #f8fafc; }
        .button[disabled] { opacity: 0.5; cursor: not-allowed; }
        .alert { border-radius: 10px; padding: 12px; margin-bottom: 14px; font-weight: 600; }
        .alert.success { background: #0f766e; color: #ecfeff; }
        .alert.error { background: #7f1d1d; color: #fee2e2; }
        .alert.info { background: #1e3a8a; color: #dbeafe; }
        label { display: block; margin-bottom: 6px; font-weight: 600; }
        input[type="password"], input[type="text"] { padding: 10px; border-radius: 8px; border: 1px solid #475569; background: #0f172a; color: #f8fafc; width: 100%; }
        .cooldown { font-size: 14px; color: #cbd5f5; }
        .search-bar { margin-bottom: 12px; }
        .search-meta { font-size: 14px; color: #cbd5f5; }
        .pagination { display: flex; gap: 8px; align-items: center; margin-top: 12px; flex-wrap: wrap; }
        .empty-state { color: #cbd5f5; font-style: italic; padding: 12px 0; }
        .section-subtitle { margin-top: -8px; color: #cbd5f5; }
        .meta-line { color: #cbd5f5; font-size: 13px; margin-top: 4px; }
        @media (max-width: 680px) {
            .song { flex-direction: column; align-items: stretch; }
            .song form { width: 100%; }
            .song .button { width: 100%; }
            h1 { font-size: 24px; }
        }
    </style>
</head>
<body>
<header>
    <h1>Partecipa a {{ $eventNight->venue?->name ?? 'Karaoke Night' }}</h1>
    <div>Codice evento: <strong>{{ $eventNight->code }}</strong></div>
</header>
<main>
    @if (session('status'))
        <div class="alert success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="alert info">Se non riesci a prenotare un brano, ricarica la pagina e riprova.</div>

    @if ($eventNight->join_pin)
        <div class="card">
            <form method="POST" action="{{ route('public.join.activate', $eventNight->code) }}">
                @csrf
                <label for="pin">Inserisci il PIN per attivare</label>
                <input id="pin" name="pin" type="password" autocomplete="one-time-code">
                <button class="button secondary" type="submit" style="margin-top:10px;">Attiva</button>
            </form>
        </div>
    @endif

    <div class="card">
        <h2>Richiedi una canzone</h2>
        <p class="section-subtitle">Inserisci il tuo nome e scegli il brano da prenotare.</p>
        @php($cooldownMinutes = (int) ceil($eventNight->request_cooldown_seconds / 60))
        @if ($cooldownMinutes > 0)
            <p class="cooldown">Puoi richiedere una canzone ogni {{ $cooldownMinutes }} {{ \Illuminate\Support\Str::plural('minuto', $cooldownMinutes) }}.</p>
        @endif

        <label for="singer-name">Il tuo nome</label>
        <input id="singer-name" type="text" placeholder="Es. Marco" maxlength="50">

        <div class="search-bar" style="margin-top:12px;">
            <label for="song-search">Cerca per titolo o artista</label>
            <input id="song-search" type="text" placeholder="Inizia a digitare per cercare...">
        </div>
        <div class="search-meta" id="search-meta">Mostro i risultati.</div>
        <div id="song-results"></div>
        <div class="pagination">
            <button class="button secondary" id="prev-page" type="button">Precedente</button>
            <span id="page-info"></span>
            <button class="button secondary" id="next-page" type="button">Successivo</button>
        </div>
        <noscript>
            <p class="empty-state">Abilita JavaScript per cercare le canzoni.</p>
        </noscript>
    </div>

    <div class="card">
        <h2>Le tue canzoni prenotate</h2>
        <p class="section-subtitle">Qui puoi vedere quando canterai o se hai già cantato.</p>
        <div id="my-requests" class="empty-state">Caricamento prenotazioni...</div>
    </div>
</main>
<script>
    const joinTokenKey = @json(config('public_join.join_token_storage_key', 'join_token'));
    const joinToken = @json($joinToken);
    const requestUrl = @json(route('public.join.request', $eventNight->code));
    const searchUrl = @json(route('public.join.songs', $eventNight->code));
    const etaUrl = @json(route('public.join.eta', $eventNight->code));
    const myRequestsUrl = @json(route('public.join.my_requests', $eventNight->code));
    const csrfToken = @json(csrf_token());

    localStorage.setItem(joinTokenKey, joinToken);

    const elements = {
        singerName: document.getElementById('singer-name'),
        searchInput: document.getElementById('song-search'),
        results: document.getElementById('song-results'),
        myRequests: document.getElementById('my-requests'),
        meta: document.getElementById('search-meta'),
        prevPage: document.getElementById('prev-page'),
        nextPage: document.getElementById('next-page'),
        pageInfo: document.getElementById('page-info'),
    };

    const state = { query: '', page: 1, lastPage: 1, perPage: 10, pending: null };

    const formatDuration = (seconds) => {
        const minutes = Math.floor(seconds / 60);
        const remaining = seconds % 60;
        return minutes <= 0 ? `${remaining}s` : `${minutes}m ${remaining}s`;
    };

    const renderMyRequests = (payload) => {
        elements.myRequests.innerHTML = '';

        if (!payload.data || payload.data.length === 0) {
            elements.myRequests.className = 'empty-state';
            elements.myRequests.textContent = 'Non hai ancora prenotato canzoni.';
            return;
        }

        elements.myRequests.className = '';
        payload.data.forEach((item) => {
            const row = document.createElement('div');
            row.className = 'song';

            const details = document.createElement('div');
            const title = document.createElement('strong');
            title.textContent = `${item.song_title ?? 'Brano'} — ${item.song_artist ?? 'Artista sconosciuto'}`;
            const status = document.createElement('div');
            status.className = 'meta-line';
            status.textContent = `Stato: ${item.status_label}`;
            const when = document.createElement('div');
            when.className = 'meta-line';
            when.textContent = item.when_label;

            details.appendChild(title);
            details.appendChild(status);
            details.appendChild(when);
            row.appendChild(details);
            elements.myRequests.appendChild(row);
        });
    };

    const fetchMyRequests = async () => {
        try {
            const response = await fetch(`${myRequestsUrl}?join_token=${encodeURIComponent(joinToken)}`, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) {
                throw new Error('Errore nel recupero prenotazioni');
            }
            const payload = await response.json();
            renderMyRequests(payload);
        } catch (error) {
            elements.myRequests.className = 'empty-state';
            elements.myRequests.textContent = 'Impossibile caricare le tue prenotazioni. Ricarica la pagina.';
        }
    };

    const renderResults = (payload) => {
        elements.results.innerHTML = '';
        if (payload.data.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.textContent = 'Nessuna canzone trovata.';
            elements.results.appendChild(empty);
        } else {
            payload.data.forEach((song) => {
                const container = document.createElement('div');
                container.className = 'song';
                const details = document.createElement('div');
                details.innerHTML = `<strong>${song.title}</strong><br><span>${song.artist ?? 'Artista sconosciuto'}</span><br><span class="search-meta">${formatDuration(song.duration_seconds)}</span>`;

                const form = document.createElement('form');
                form.className = 'song-request-form';
                form.method = 'POST';
                form.action = requestUrl;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="song_id" value="${song.id}">
                    <input type="hidden" name="join_token" value="${joinToken}">
                    <input type="hidden" name="singer_name" value="">
                    <button class="button" type="submit">Prenota</button>
                `;

                container.appendChild(details);
                container.appendChild(form);
                elements.results.appendChild(container);
            });
        }

        elements.pageInfo.textContent = `Pagina ${payload.meta.current_page} di ${payload.meta.last_page}`;
        elements.prevPage.disabled = payload.meta.current_page <= 1;
        elements.nextPage.disabled = payload.meta.current_page >= payload.meta.last_page;
        elements.meta.textContent = `Mostrati ${payload.meta.total} risultati.`;
    };

    const renderError = (message) => {
        elements.results.innerHTML = `<div class="empty-state">${message}</div>`;
        elements.pageInfo.textContent = '';
        elements.prevPage.disabled = true;
        elements.nextPage.disabled = true;
        elements.meta.textContent = message;
    };

    const fetchSongs = async () => {
        const params = new URLSearchParams({ q: state.query, page: state.page, per_page: state.perPage });
        elements.meta.textContent = 'Caricamento canzoni...';
        const response = await fetch(`${searchUrl}?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) {
            renderError('Impossibile caricare le canzoni. Ricarica la pagina e riprova.');
            return;
        }

        const payload = await response.json();
        state.lastPage = payload.meta.last_page;
        renderResults(payload);
    };

    const scheduleSearch = () => {
        if (state.pending) {
            clearTimeout(state.pending);
        }
        state.pending = setTimeout(() => {
            state.page = 1;
            fetchSongs();
        }, 250);
    };

    elements.searchInput.addEventListener('input', (event) => {
        state.query = event.target.value.trim();
        scheduleSearch();
    });

    elements.prevPage.addEventListener('click', () => {
        if (state.page > 1) {
            state.page -= 1;
            fetchSongs();
        }
    });

    elements.nextPage.addEventListener('click', () => {
        if (state.page < state.lastPage) {
            state.page += 1;
            fetchSongs();
        }
    });

    elements.results.addEventListener('submit', async (event) => {
        if (!event.target.matches('.song-request-form')) {
            return;
        }

        event.preventDefault();

        const singerName = elements.singerName.value.trim();
        if (!singerName) {
            alert('Inserisci il tuo nome prima di prenotare.');
            elements.singerName.focus();
            return;
        }

        event.target.querySelector('input[name="singer_name"]').value = singerName;

        try {
            const response = await fetch(`${etaUrl}?join_token=${encodeURIComponent(joinToken)}`, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) {
                throw new Error('Impossibile recuperare attesa');
            }

            const payload = await response.json();
            const shouldSubmit = confirm(`Attesa stimata: ${payload.eta_label}. Confermi la prenotazione?`);
            if (shouldSubmit) {
                event.target.submit();
            }
        } catch (error) {
            const shouldSubmit = confirm('Impossibile calcolare l\'attesa adesso. Vuoi prenotare comunque?');
            if (shouldSubmit) {
                event.target.submit();
            }
        }
    });

    fetchSongs();
    fetchMyRequests();
</script>
</body>
</html>
