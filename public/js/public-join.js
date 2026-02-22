(() => {
    const bootstrap = window.PublicJoinBootstrap || {};
    const joinToken = bootstrap.joinToken;
    const eventCode = bootstrap.eventCode;
    const requestUrl = bootstrap.requestUrl;
    const searchUrl = bootstrap.searchUrl;
    const etaUrl = bootstrap.etaUrl;
    const myRequestsUrl = bootstrap.myRequestsUrl;
    const csrfToken = bootstrap.csrfToken;
    const initialMyRequestsPayload = bootstrap.initialMyRequestsPayload || { data: [], meta: {} };

    const elements = {
        displayNameInput: document.getElementById('display-name'),
        searchInput: document.getElementById('song-search'),
        results: document.getElementById('song-results'),
        meta: document.getElementById('search-meta'),
        prevPage: document.getElementById('prev-page'),
        nextPage: document.getElementById('next-page'),
        pageInfo: document.getElementById('page-info'),
        clientMessage: document.getElementById('client-message'),
        clientMessageTitle: document.getElementById('client-message-title'),
        clientMessageText: document.getElementById('client-message-text'),
        myRequestsList: document.getElementById('my-requests-list'),
        myRequestsMeta: document.getElementById('my-requests-meta'),
    };

    if (!joinToken || !eventCode || !elements.displayNameInput || !elements.searchInput || !elements.results) {
        return;
    }

    const state = {
        query: '',
        page: 1,
        lastPage: 1,
        perPage: 10,
        pendingSearch: null,
        myRequestsPollTimer: null,
    };

    const storageKeys = {
        displayName: `public_join_display_name:${eventCode}`,
    };

    const playbackLabelMap = {
        idle: 'In attesa',
        playing: 'In corso',
        paused: 'In pausa',
    };

    const getDisplayName = () => {
        return (elements.displayNameInput.value || '').trim();
    };

    const setClientMessage = (variant, title, message) => {
        const variants = ['notice--success', 'notice--error', 'notice--warning', 'notice--info'];
        variants.forEach((className) => elements.clientMessage.classList.remove(className));

        const normalizedVariant = ['success', 'error', 'warning', 'info'].includes(variant) ? variant : 'info';
        elements.clientMessage.classList.add(`notice--${normalizedVariant}`);
        elements.clientMessageTitle.textContent = title;
        elements.clientMessageText.textContent = message;
        elements.clientMessage.hidden = false;
    };

    const clearClientMessage = () => {
        elements.clientMessage.hidden = true;
        elements.clientMessageTitle.textContent = '';
        elements.clientMessageText.textContent = '';
    };

    const formatClientTime = (isoString) => {
        if (!isoString) {
            return null;
        }

        const date = new Date(isoString);

        if (Number.isNaN(date.getTime())) {
            return null;
        }

        return date.toLocaleTimeString('it-IT', {
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const extractErrorMessage = async (response, fallbackMessage) => {
        try {
            const payload = await response.json();

            if (payload?.message && typeof payload.message === 'string') {
                return payload.message;
            }

            if (payload?.errors && typeof payload.errors === 'object') {
                const first = Object.values(payload.errors).flat()[0];
                if (typeof first === 'string' && first.trim() !== '') {
                    return first;
                }
            }
        } catch (error) {
            // Ignore parser failures and use fallback.
        }

        return fallbackMessage;
    };

    const renderSearchEmpty = (message) => {
        elements.results.innerHTML = '';
        const empty = document.createElement('div');
        empty.className = 'empty-state';
        empty.textContent = message;
        elements.results.appendChild(empty);
    };

    const renderResults = (payload) => {
        elements.results.innerHTML = '';

        if (!Array.isArray(payload.data) || payload.data.length === 0) {
            renderSearchEmpty('Nessuna canzone trovata. Prova con un altro titolo o artista.');
        } else {
            payload.data.forEach((song) => {
                const container = document.createElement('article');
                container.className = 'song-result';

                const main = document.createElement('div');
                main.className = 'song-main';

                const title = document.createElement('h3');
                title.className = 'song-title';
                title.textContent = song.title || 'Titolo non disponibile';

                const artist = document.createElement('p');
                artist.className = 'song-artist';
                artist.textContent = song.artist || 'Artista sconosciuto';

                main.appendChild(title);
                main.appendChild(artist);

                const form = document.createElement('form');
                form.className = 'song-request-form';
                form.method = 'POST';
                form.action = requestUrl;
                form.dataset.songTitle = song.title || 'questo brano';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;

                const songInput = document.createElement('input');
                songInput.type = 'hidden';
                songInput.name = 'song_id';
                songInput.value = String(song.id);

                const joinInput = document.createElement('input');
                joinInput.type = 'hidden';
                joinInput.name = 'join_token';
                joinInput.value = joinToken;

                const nameInput = document.createElement('input');
                nameInput.type = 'hidden';
                nameInput.name = 'display_name';
                nameInput.value = getDisplayName();

                const button = document.createElement('button');
                button.className = 'button button-primary';
                button.type = 'submit';
                button.textContent = 'Prenota';

                form.appendChild(csrfInput);
                form.appendChild(songInput);
                form.appendChild(joinInput);
                form.appendChild(nameInput);
                form.appendChild(button);

                container.appendChild(main);
                container.appendChild(form);
                elements.results.appendChild(container);
            });
        }

        elements.pageInfo.textContent = `Pagina ${payload.meta.current_page} di ${payload.meta.last_page}`;
        elements.prevPage.disabled = payload.meta.current_page <= 1;
        elements.nextPage.disabled = payload.meta.current_page >= payload.meta.last_page;

        if ((payload.meta.total || 0) === 0) {
            elements.meta.textContent = 'Nessun risultato disponibile.';
        } else {
            elements.meta.textContent = `Trovate ${payload.meta.total} canzoni.`;
        }
    };

    const fetchSongs = async () => {
        const params = new URLSearchParams({
            q: state.query,
            page: String(state.page),
            per_page: String(state.perPage),
        });

        elements.meta.textContent = 'Caricamento canzoni...';

        let response;

        try {
            response = await fetch(`${searchUrl}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });
        } catch (error) {
            const message = 'Connessione non disponibile. Ricarica la pagina e riprova.';
            renderSearchEmpty(message);
            elements.pageInfo.textContent = '';
            elements.prevPage.disabled = true;
            elements.nextPage.disabled = true;
            elements.meta.textContent = message;
            return;
        }

        if (!response.ok) {
            const message = await extractErrorMessage(response, 'Impossibile caricare le canzoni adesso.');
            renderSearchEmpty(message);
            elements.pageInfo.textContent = '';
            elements.prevPage.disabled = true;
            elements.nextPage.disabled = true;
            elements.meta.textContent = message;
            return;
        }

        const payload = await response.json();
        state.lastPage = payload.meta?.last_page || 1;
        renderResults(payload);
    };

    const scheduleSearch = () => {
        if (state.pendingSearch) {
            window.clearTimeout(state.pendingSearch);
        }

        state.pendingSearch = window.setTimeout(() => {
            state.page = 1;
            fetchSongs();
        }, 250);
    };

    const renderMyRequests = (payload) => {
        const list = Array.isArray(payload?.data) ? payload.data : [];
        const latestList = [...list]
            .sort((a, b) => {
                const aTimestamp = Date.parse(a?.requested_at || '');
                const bTimestamp = Date.parse(b?.requested_at || '');

                if (Number.isNaN(aTimestamp) && Number.isNaN(bTimestamp)) {
                    return 0;
                }

                if (Number.isNaN(aTimestamp)) {
                    return 1;
                }

                if (Number.isNaN(bTimestamp)) {
                    return -1;
                }

                return bTimestamp - aTimestamp;
            })
            .slice(0, 3);
        const meta = payload?.meta || {};

        elements.myRequestsList.innerHTML = '';

        if (latestList.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.textContent = 'Non hai ancora prenotazioni personali.';
            elements.myRequestsList.appendChild(empty);
        } else {
            latestList.forEach((item) => {
                const requestItem = document.createElement('article');
                requestItem.className = `request-item request-item--${item.status || 'queued'}`;

                const top = document.createElement('div');
                top.className = 'request-top';

                const left = document.createElement('div');

                const title = document.createElement('h3');
                title.className = 'request-title';
                title.textContent = item.title || 'Brano';

                const artist = document.createElement('p');
                artist.className = 'request-artist';
                artist.textContent = item.artist || 'Artista sconosciuto';

                left.appendChild(title);
                left.appendChild(artist);

                const badge = document.createElement('span');
                badge.className = `request-badge request-badge--${item.status || 'queued'}`;
                badge.textContent = item.status_label || 'In coda';

                top.appendChild(left);
                top.appendChild(badge);

                const dataLine = document.createElement('p');
                dataLine.className = 'request-data';

                const infoParts = [];

                if (item.queue_position) {
                    infoParts.push(`Posizione in coda: #${item.queue_position}`);
                }

                if (item.requested_at_label) {
                    infoParts.push(`Prenotata alle ${item.requested_at_label}`);
                }

                if (item.scheduled_at_label && item.status === 'queued') {
                    infoParts.push(`Turno stimato: ${item.scheduled_at_label}`);
                }

                if (item.played_at_label && (item.status === 'played' || item.status === 'skipped' || item.status === 'canceled')) {
                    infoParts.push(`Aggiornata alle ${item.played_at_label}`);
                }

                dataLine.textContent = infoParts.length > 0 ? infoParts.join(' • ') : 'Informazioni in aggiornamento';

                const note = document.createElement('p');
                note.className = 'request-note';
                note.textContent = item.timeline_note || 'Stato in aggiornamento.';

                if (item.status === 'played' || item.status === 'skipped' || item.status === 'canceled') {
                    note.classList.add('request-note--muted');
                }

                requestItem.appendChild(top);
                requestItem.appendChild(dataLine);
                requestItem.appendChild(note);

                elements.myRequestsList.appendChild(requestItem);
            });
        }

        const statusLabel = playbackLabelMap[meta.playback_state] || 'In aggiornamento';
        const updatedAtLabel = formatClientTime(meta.updated_at);
        const suffix = updatedAtLabel ? ` • Aggiornato alle ${updatedAtLabel}` : '';
        elements.myRequestsMeta.textContent = `Stato serata: ${statusLabel}${suffix}`;
    };

    const fetchMyRequests = async ({ silent = false } = {}) => {
        const params = new URLSearchParams({
            join_token: joinToken,
        });

        if (!silent) {
            elements.myRequestsMeta.textContent = 'Aggiornamento prenotazioni personali...';
        }

        let response;

        try {
            response = await fetch(`${myRequestsUrl}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });
        } catch (error) {
            if (!silent) {
                const empty = document.createElement('div');
                empty.className = 'empty-state';
                empty.textContent = 'Connessione non disponibile. Ricarica la pagina per aggiornare le tue prenotazioni.';
                elements.myRequestsList.innerHTML = '';
                elements.myRequestsList.appendChild(empty);
                elements.myRequestsMeta.textContent = 'Aggiornamento non disponibile';
            }

            return;
        }

        if (!response.ok) {
            const message = await extractErrorMessage(response, 'Impossibile aggiornare le tue prenotazioni adesso.');

            if (!silent) {
                const empty = document.createElement('div');
                empty.className = 'empty-state';
                empty.textContent = `${message} Ricarica la pagina per riprovare.`;
                elements.myRequestsList.innerHTML = '';
                elements.myRequestsList.appendChild(empty);
                elements.myRequestsMeta.textContent = 'Aggiornamento non disponibile';
            }

            return;
        }

        const payload = await response.json();
        renderMyRequests(payload);
    };

    elements.displayNameInput.addEventListener('input', () => {
        clearClientMessage();
        localStorage.setItem(storageKeys.displayName, getDisplayName());
    });

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
        clearClientMessage();

        const form = event.target;
        const button = form.querySelector('button[type="submit"]');
        const hiddenNameInput = form.querySelector('input[name="display_name"]');
        const displayName = getDisplayName();

        if (displayName.length < 2) {
            setClientMessage('error', 'Nome mancante', 'Inserisci il tuo nome (almeno 2 caratteri) prima di prenotare.');
            elements.displayNameInput.focus();
            return;
        }

        hiddenNameInput.value = displayName;

        if (button) {
            button.disabled = true;
            button.textContent = 'Controllo attesa...';
        }

        let shouldSubmit = false;

        try {
            const response = await fetch(`${etaUrl}?join_token=${encodeURIComponent(joinToken)}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                const message = await extractErrorMessage(response, 'Impossibile calcolare l\'attesa in questo momento.');
                shouldSubmit = confirm(`${message}\n\nVuoi comunque confermare la prenotazione?`);
            } else {
                const payload = await response.json();
                const songTitle = form.dataset.songTitle || 'questo brano';
                shouldSubmit = confirm(`Attesa stimata prima del tuo turno: ${payload.eta_label}.\n\nConfermi la prenotazione di "${songTitle}" a nome "${displayName}"?`);
            }
        } catch (error) {
            shouldSubmit = confirm('Connessione momentaneamente instabile. Vuoi confermare comunque la prenotazione?');
        }

        if (shouldSubmit) {
            form.submit();
            return;
        }

        if (button) {
            button.disabled = false;
            button.textContent = 'Prenota';
        }
    });

    const hydrateDisplayName = () => {
        const current = getDisplayName();

        if (current.length >= 2) {
            localStorage.setItem(storageKeys.displayName, current);
            return;
        }

        const savedName = (localStorage.getItem(storageKeys.displayName) || '').trim();

        if (savedName.length >= 2) {
            elements.displayNameInput.value = savedName;
        }
    };

    const startMyRequestsPolling = () => {
        if (state.myRequestsPollTimer) {
            window.clearInterval(state.myRequestsPollTimer);
        }

        state.myRequestsPollTimer = window.setInterval(() => {
            fetchMyRequests({ silent: true });
        }, 20000);
    };

    hydrateDisplayName();
    renderMyRequests(initialMyRequestsPayload);
    fetchMyRequests({ silent: true });
    startMyRequestsPolling();
    fetchSongs();
})();
