<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karaoke Night | Join</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #0f172a; color: #f8fafc; }
        header { padding: 24px; background: #111827; }
        main { padding: 24px; max-width: 900px; margin: 0 auto; }
        h1 { margin: 0 0 8px; font-size: 28px; }
        .card { background: #1f2937; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        .song { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #374151; }
        .song:last-child { border-bottom: none; }
        .button { background: #38bdf8; color: #0f172a; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; }
        .button.secondary { background: #64748b; color: #f8fafc; }
        .button[disabled] { opacity: 0.5; cursor: not-allowed; }
        .status { color: #38bdf8; margin-bottom: 12px; }
        .errors { color: #fca5a5; margin-bottom: 12px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; }
        input[type="password"] { padding: 8px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #f8fafc; width: 200px; }
        input[type="text"] { padding: 8px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #f8fafc; width: 100%; }
        .cooldown { font-size: 14px; color: #cbd5f5; }
        .search-bar { display: flex; gap: 12px; align-items: center; margin-bottom: 12px; }
        .search-meta { font-size: 14px; color: #cbd5f5; }
        .pagination { display: flex; gap: 8px; align-items: center; margin-top: 12px; }
        .empty-state { color: #cbd5f5; font-style: italic; padding: 12px 0; }
    </style>
</head>
<body>
<header>
    <h1>Join {{ $eventNight->venue?->name ?? 'Karaoke Night' }}</h1>
    <div>Event code: <strong>{{ $eventNight->code }}</strong></div>
</header>
<main>
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($eventNight->join_pin)
        <div class="card">
            <form method="POST" action="{{ route('public.join.activate', $eventNight->code) }}">
                @csrf
                <label for="pin">Enter PIN to activate</label>
                <input id="pin" name="pin" type="password" autocomplete="one-time-code">
                <button class="button secondary" type="submit">Activate</button>
            </form>
        </div>
    @endif

    <div class="card">
        <h2>Request a song</h2>
        @if ($eventNight->request_cooldown_seconds > 0)
            <p class="cooldown">You can request a song every {{ $eventNight->request_cooldown_seconds }} seconds.</p>
        @endif
        <div class="search-bar">
            <label for="song-search">Search by title or artist</label>
            <input id="song-search" type="text" placeholder="Start typing to search...">
        </div>
        <div class="search-meta" id="search-meta">Showing results.</div>
        <div id="song-results"></div>
        <div class="pagination">
            <button class="button secondary" id="prev-page" type="button">Previous</button>
            <span id="page-info"></span>
            <button class="button secondary" id="next-page" type="button">Next</button>
        </div>
        <noscript>
            <p class="empty-state">Enable JavaScript to search songs.</p>
        </noscript>
    </div>
</main>
<script>
    const joinTokenKey = @json(config('public_join.join_token_storage_key', 'join_token'));
    const joinToken = @json($joinToken);
    const eventCode = @json($eventNight->code);
    const requestUrl = @json(route('public.join.request', $eventNight->code));
    const searchUrl = @json(route('public.join.songs', $eventNight->code));
    const etaUrl = @json(route('public.join.eta', $eventNight->code));
    const csrfToken = @json(csrf_token());

    localStorage.setItem(joinTokenKey, joinToken);

    const elements = {
        searchInput: document.getElementById('song-search'),
        results: document.getElementById('song-results'),
        meta: document.getElementById('search-meta'),
        prevPage: document.getElementById('prev-page'),
        nextPage: document.getElementById('next-page'),
        pageInfo: document.getElementById('page-info'),
    };

    const state = {
        query: '',
        page: 1,
        lastPage: 1,
        perPage: 10,
        pending: null,
    };

    const formatDuration = (seconds) => {
        const minutes = Math.floor(seconds / 60);
        const remaining = seconds % 60;
        if (minutes <= 0) {
            return `${remaining}s`;
        }
        return `${minutes}m ${remaining}s`;
    };

    const renderResults = (payload) => {
        elements.results.innerHTML = '';
        if (payload.data.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.textContent = 'No songs found.';
            elements.results.appendChild(empty);
        } else {
            payload.data.forEach((song) => {
                const container = document.createElement('div');
                container.className = 'song';

                const details = document.createElement('div');
                const title = document.createElement('strong');
                title.textContent = song.title;
                const artist = document.createElement('span');
                artist.textContent = song.artist ?? 'Unknown artist';
                const duration = document.createElement('span');
                duration.className = 'search-meta';
                duration.textContent = formatDuration(song.duration_seconds);

                details.appendChild(title);
                details.appendChild(document.createElement('br'));
                details.appendChild(artist);
                details.appendChild(document.createElement('br'));
                details.appendChild(duration);

                const form = document.createElement('form');
                form.className = 'song-request-form';
                form.method = 'POST';
                form.action = requestUrl;

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;

                const songInput = document.createElement('input');
                songInput.type = 'hidden';
                songInput.name = 'song_id';
                songInput.value = song.id;

                const joinInput = document.createElement('input');
                joinInput.type = 'hidden';
                joinInput.name = 'join_token';
                joinInput.value = joinToken;

                const button = document.createElement('button');
                button.className = 'button';
                button.type = 'submit';
                button.textContent = 'Request';

                form.appendChild(csrfInput);
                form.appendChild(songInput);
                form.appendChild(joinInput);
                form.appendChild(button);

                container.appendChild(details);
                container.appendChild(form);

                elements.results.appendChild(container);
            });
        }

        elements.pageInfo.textContent = `Page ${payload.meta.current_page} of ${payload.meta.last_page}`;
        elements.prevPage.disabled = payload.meta.current_page <= 1;
        elements.nextPage.disabled = payload.meta.current_page >= payload.meta.last_page;
        elements.meta.textContent = `Showing ${payload.meta.total} result(s).`;
    };

    const renderError = (message) => {
        elements.results.innerHTML = '';
        const empty = document.createElement('div');
        empty.className = 'empty-state';
        empty.textContent = message;
        elements.results.appendChild(empty);
        elements.pageInfo.textContent = '';
        elements.prevPage.disabled = true;
        elements.nextPage.disabled = true;
        elements.meta.textContent = message;
    };

    const fetchSongs = async () => {
        const params = new URLSearchParams({
            q: state.query,
            page: state.page,
            per_page: state.perPage,
        });

        elements.meta.textContent = 'Loading songs...';

        const response = await fetch(`${searchUrl}?${params.toString()}`, {
            headers: { 'Accept': 'application/json' },
        });

        if (!response.ok) {
            renderError('Unable to load songs.');
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

        try {
            const response = await fetch(`${etaUrl}?join_token=${encodeURIComponent(joinToken)}`, {
                headers: { 'Accept': 'application/json' },
            });

            if (!response.ok) {
                throw new Error('Unable to fetch ETA');
            }

            const payload = await response.json();
            const shouldSubmit = confirm(`Estimated wait: ${payload.eta_label}. Confirm request?`);

            if (shouldSubmit) {
                event.target.submit();
            }
        } catch (error) {
            const shouldSubmit = confirm('Unable to calculate ETA right now. Request anyway?');
            if (shouldSubmit) {
                event.target.submit();
            }
        }
    });

    fetchSongs();
</script>
</body>
</html>
