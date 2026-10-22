(() => {
    'use strict';

    const config = window.PULSEWAVE || { baseUrl: '', csrfToken: '' };
    const baseUrl = String(config.baseUrl || '').replace(/\/$/, '');
    const endpoint = (path) => `${baseUrl}/${String(path).replace(/^\//, '')}`;
    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));
    const ICONS = {
        play: '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="m8 5 11 7-11 7V5Z"/></svg>',
        pause: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M8 5v14M16 5v14"/></svg>',
        heart: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>',
    };

    function showToast(message) {
        const toast = $('[data-toast]');
        if (!toast) return;
        toast.textContent = message;
        toast.hidden = false;
        requestAnimationFrame(() => toast.classList.add('visible'));
        clearTimeout(showToast.timer);
        showToast.timer = setTimeout(() => {
            toast.classList.remove('visible');
            setTimeout(() => { toast.hidden = true; }, 180);
        }, 3200);
    }

    function formatTime(seconds) {
        if (!Number.isFinite(seconds) || seconds < 0) return '0:00';
        const minutes = Math.floor(seconds / 60);
        const remainder = Math.floor(seconds % 60);
        return `${minutes}:${String(remainder).padStart(2, '0')}`;
    }

    function uniqueIds(ids) {
        return [...new Set(ids.map(Number).filter((id) => Number.isFinite(id) && id > 0))];
    }

    const player = $('[data-player]');
    const audio = $('[data-audio]');
    let tracks = [];
    let queueIds = [];
    let queuePosition = -1;
    let currentIndex = -1;
    let queueSource = 'PulseWave discovery';
    let shuffle = false;
    let repeat = false;
    let lastSavedSecond = -1;
    const recordedPlays = new Set();

    const elements = player ? {
        cover: $('[data-player-cover]', player),
        title: $('[data-player-title]', player),
        artist: $('[data-player-artist]', player),
        playPause: $('[data-play-pause]', player),
        previous: $('[data-previous]', player),
        next: $('[data-next]', player),
        shuffle: $('[data-shuffle]', player),
        repeat: $('[data-repeat]', player),
        timeline: $('[data-timeline]', player),
        currentTime: $('[data-current-time]', player),
        duration: $('[data-duration]', player),
        volume: $('[data-volume]', player),
        mute: $('[data-mute]', player),
        queueToggle: $('[data-queue-toggle]', player),
        deviceToggle: $('[data-device-toggle]', player),
        expand: $('[data-player-expand]', player),
    } : {};

    async function loadCatalog() {
        try {
            const response = await fetch(endpoint('api/tracks.php'), { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Catalog request failed');
            const payload = await response.json();
            tracks = Array.isArray(payload.tracks) ? payload.tracks : [];
            restorePlayer();
            return tracks;
        } catch (error) {
            console.error(error);
            showToast('The music catalog could not be loaded.');
            return [];
        }
    }

    const catalogReady = loadCatalog();
    const getTrack = (id) => tracks.find((track) => Number(track.id) === Number(id)) || null;
    const currentTrack = () => currentIndex >= 0 ? tracks[currentIndex] : null;
    const catalogQueue = () => tracks.map((track) => Number(track.id));

    function setPlayState(playing) {
        if (!elements.playPause) return;
        elements.playPause.innerHTML = playing ? ICONS.pause : ICONS.play;
        elements.playPause.setAttribute('aria-label', playing ? 'Pause' : 'Play');
        player?.classList.toggle('is-playing', playing);
    }

    function setQueue(ids, currentId, source = 'PulseWave discovery') {
        const available = new Set(tracks.map((track) => Number(track.id)));
        const normalized = uniqueIds(ids).filter((id) => available.has(id));
        const fallback = catalogQueue();
        queueIds = normalized.length ? normalized : fallback;
        if (currentId && !queueIds.includes(Number(currentId))) {
            queueIds.unshift(Number(currentId));
        }
        queueSource = source || 'PulseWave discovery';
        queuePosition = queueIds.findIndex((id) => Number(id) === Number(currentId));
        renderQueue();
    }

    function savePlayer() {
        const track = currentTrack();
        if (!track) return;
        try {
            localStorage.setItem('pulsewave.player.v3', JSON.stringify({
                trackId: track.id,
                currentTime: audio.currentTime || 0,
                volume: audio.volume,
                muted: audio.muted,
                shuffle,
                repeat,
                queueIds,
                queueSource,
            }));
        } catch (_) {
            // Playback remains available when storage is unavailable.
        }
    }

    function restorePlayer() {
        let state = null;
        try {
            state = JSON.parse(localStorage.getItem('pulsewave.player.v3') || localStorage.getItem('pulsewave.player') || 'null');
        } catch (_) {
            state = null;
        }
        if (!state) {
            audio.volume = 0.8;
            if (elements.volume) elements.volume.value = '0.8';
            return;
        }

        const trackId = Number(state.trackId);
        const track = getTrack(trackId);
        if (track) {
            setQueue(Array.isArray(state.queueIds) ? state.queueIds : catalogQueue(), trackId, state.queueSource);
            selectTrack(trackId, false, false);
            audio.addEventListener('loadedmetadata', () => {
                if (Number.isFinite(Number(state.currentTime)) && Number(state.currentTime) < audio.duration) {
                    audio.currentTime = Number(state.currentTime);
                }
            }, { once: true });
        }
        audio.volume = Math.min(1, Math.max(0, Number(state.volume ?? 0.8)));
        audio.muted = Boolean(state.muted);
        if (elements.volume) elements.volume.value = String(audio.volume);
        syncToggle(elements.mute, audio.muted);
        syncToggle(elements.shuffle, Boolean(state.shuffle));
        syncToggle(elements.repeat, Boolean(state.repeat));
        shuffle = Boolean(state.shuffle);
        repeat = Boolean(state.repeat);
    }

    function syncToggle(button, active) {
        if (!button) return;
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-pressed', String(active));
    }

    function updateMediaSession(track) {
        if (!('mediaSession' in navigator) || !track) return;
        try {
            navigator.mediaSession.metadata = new MediaMetadata({
                title: track.title,
                artist: track.artist,
                album: 'PulseWave',
                artwork: [{ src: track.cover_url, sizes: '512x512', type: 'image/jpeg' }],
            });
            navigator.mediaSession.setActionHandler('play', () => audio.play());
            navigator.mediaSession.setActionHandler('pause', () => audio.pause());
            navigator.mediaSession.setActionHandler('previoustrack', () => chooseAdjacent(-1));
            navigator.mediaSession.setActionHandler('nexttrack', () => chooseAdjacent(1));
        } catch (_) {
            // Media Session support is optional.
        }
    }

    function selectTrack(id, autoplay = true, persist = true) {
        const track = getTrack(id);
        if (!track || !audio) return;

        currentIndex = tracks.findIndex((item) => Number(item.id) === Number(track.id));
        queuePosition = queueIds.findIndex((queueId) => Number(queueId) === Number(track.id));
        audio.src = track.audio_url;
        elements.cover.src = track.cover_url;
        elements.title.textContent = track.title;
        elements.artist.textContent = track.artist;
        player.classList.add('has-track');
        renderQueue();
        syncCurrentCards(track.id);
        updateMediaSession(track);
        if (persist) savePlayer();

        if (autoplay) {
            audio.play().then(() => {
                recordPlay(track.id);
            }).catch(() => {
                showToast('This audio is not available on the current device.');
            });
        }
    }

    function syncCurrentCards(trackId) {
        $$('.is-current-track').forEach((item) => item.classList.remove('is-current-track'));
        $$(`[data-play-track="${Number(trackId)}"]`).forEach((button) => {
            button.closest('.track-card, .compact-track, .release-card, .playlist-track-row, .release-track')?.classList.add('is-current-track');
        });
    }

    async function playTrackById(id, autoplay = true, contextIds = null, source = '') {
        await catalogReady;
        const track = getTrack(id);
        if (!track) {
            showToast('That track is not available.');
            return;
        }

        if (Array.isArray(contextIds) && contextIds.length) {
            setQueue(contextIds, id, source);
        } else if (!queueIds.includes(Number(id))) {
            setQueue(catalogQueue(), id, source || 'PulseWave discovery');
        } else {
            queuePosition = queueIds.findIndex((queueId) => Number(queueId) === Number(id));
            queueSource = source || queueSource;
            renderQueue();
        }
        selectTrack(id, autoplay);
    }

    function chooseAdjacent(direction) {
        if (!queueIds.length) setQueue(catalogQueue(), currentTrack()?.id, queueSource);
        if (!queueIds.length) return;

        let nextPosition;
        if (shuffle && queueIds.length > 1) {
            do {
                nextPosition = Math.floor(Math.random() * queueIds.length);
            } while (nextPosition === queuePosition);
        } else {
            nextPosition = (queuePosition + direction + queueIds.length) % queueIds.length;
        }
        queuePosition = nextPosition;
        selectTrack(queueIds[nextPosition], true);
    }

    async function recordPlay(trackId) {
        if (recordedPlays.has(trackId)) return;
        recordedPlays.add(trackId);
        try {
            await fetch(endpoint('api/play.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': config.csrfToken,
                },
                body: JSON.stringify({ track_id: trackId }),
            });
        } catch (_) {
            // Analytics never interrupts playback.
        }
    }

    function renderQueue() {
        const list = $('[data-queue-list]');
        const source = $('[data-queue-source]');
        if (!list) return;
        if (source) source.textContent = queueSource;
        list.replaceChildren();

        if (!queueIds.length) {
            const empty = document.createElement('p');
            empty.className = 'queue-empty';
            empty.textContent = 'Choose a track to start building your queue.';
            list.append(empty);
            return;
        }

        const currentId = currentTrack()?.id;
        queueIds.forEach((id, index) => {
            const track = getTrack(id);
            if (!track) return;
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `queue-item${Number(id) === Number(currentId) ? ' is-current' : ''}`;
            button.dataset.queueTrack = String(id);

            const number = document.createElement('span');
            number.className = 'queue-number';
            number.textContent = Number(id) === Number(currentId) ? 'Now' : String(index + 1).padStart(2, '0');

            const image = document.createElement('img');
            image.src = track.cover_url;
            image.alt = '';
            image.loading = 'lazy';

            const copy = document.createElement('span');
            copy.className = 'queue-copy';
            const title = document.createElement('strong');
            title.textContent = track.title;
            const artist = document.createElement('small');
            artist.textContent = track.artist;
            copy.append(title, artist);

            const state = document.createElement('span');
            state.className = 'queue-state';
            state.innerHTML = Number(id) === Number(currentId) ? '<span class="playing-bars"><i></i><i></i><i></i></span>' : '';
            button.append(number, image, copy, state);
            list.append(button);
        });
    }

    function openQueue() {
        const panel = $('[data-queue-panel]');
        const scrim = $('[data-queue-close].queue-scrim');
        if (!panel) return;
        panel.hidden = false;
        scrim.hidden = false;
        document.body.classList.add('queue-open');
        requestAnimationFrame(() => panel.classList.add('open'));
        elements.queueToggle?.setAttribute('aria-expanded', 'true');
    }

    function closeQueue() {
        const panel = $('[data-queue-panel]');
        const scrim = $('[data-queue-close].queue-scrim');
        if (!panel) return;
        panel.classList.remove('open');
        document.body.classList.remove('queue-open');
        elements.queueToggle?.setAttribute('aria-expanded', 'false');
        setTimeout(() => {
            panel.hidden = true;
            if (scrim) scrim.hidden = true;
        }, 220);
    }

    function readPlaybackContext(button) {
        const container = button.closest('[data-play-collection]');
        if (!container) return { ids: null, source: '' };
        const ids = container.dataset.playCollection
            .split(',')
            .map((value) => Number(value.trim()))
            .filter(Boolean);
        return { ids, source: container.dataset.queueLabel || 'PulseWave discovery' };
    }

    document.addEventListener('click', async (event) => {
        const playButton = event.target.closest('[data-play-track]');
        if (playButton) {
            event.preventDefault();
            const context = readPlaybackContext(playButton);
            await playTrackById(playButton.dataset.playTrack, true, context.ids, context.source);
            if (playButton.closest('[data-search-panel]')) closeSearch(false);
        }

        const queueTrack = event.target.closest('[data-queue-track]');
        if (queueTrack) {
            queuePosition = queueIds.findIndex((id) => Number(id) === Number(queueTrack.dataset.queueTrack));
            selectTrack(queueTrack.dataset.queueTrack, true);
        }

        const shuffleArtist = event.target.closest('[data-shuffle-artist]');
        if (shuffleArtist) {
            await catalogReady;
            const artistTracks = tracks.filter((track) => Number(track.artist_id) === Number(shuffleArtist.dataset.shuffleArtist));
            if (artistTracks.length) {
                const track = artistTracks[Math.floor(Math.random() * artistTracks.length)];
                await playTrackById(track.id, true, artistTracks.map((item) => item.id), 'Artist shuffle');
            }
        }
    });

    if (player && audio) {
        elements.playPause.addEventListener('click', () => {
            if (currentIndex < 0) {
                if (tracks.length) playTrackById(tracks[0].id, true, catalogQueue(), 'PulseWave discovery');
                return;
            }
            if (audio.paused) {
                audio.play().then(() => recordPlay(currentTrack().id)).catch(() => {
                    showToast('This audio is not available on the current device.');
                });
            } else {
                audio.pause();
            }
        });
        elements.previous.addEventListener('click', () => {
            if (audio.currentTime > 3) {
                audio.currentTime = 0;
                audio.play().catch(() => {});
            } else {
                chooseAdjacent(-1);
            }
        });
        elements.next.addEventListener('click', () => chooseAdjacent(1));
        elements.shuffle.addEventListener('click', () => {
            shuffle = !shuffle;
            syncToggle(elements.shuffle, shuffle);
            savePlayer();
        });
        elements.repeat.addEventListener('click', () => {
            repeat = !repeat;
            syncToggle(elements.repeat, repeat);
            savePlayer();
        });
        elements.volume.addEventListener('input', () => {
            audio.volume = Number(elements.volume.value);
            audio.muted = false;
            syncToggle(elements.mute, audio.volume === 0);
            savePlayer();
        });
        elements.mute.addEventListener('click', () => {
            audio.muted = !audio.muted;
            syncToggle(elements.mute, audio.muted);
            savePlayer();
        });
        elements.timeline.addEventListener('input', () => {
            if (Number.isFinite(audio.duration)) {
                audio.currentTime = (Number(elements.timeline.value) / 100) * audio.duration;
            }
        });
        elements.queueToggle?.addEventListener('click', () => {
            const isOpen = elements.queueToggle.getAttribute('aria-expanded') === 'true';
            isOpen ? closeQueue() : openQueue();
        });
        $$('[data-queue-close]').forEach((button) => button.addEventListener('click', closeQueue));
        elements.deviceToggle?.addEventListener('click', (event) => {
            event.stopPropagation();
            const popover = $('[data-device-popover]');
            if (!popover) return;
            popover.hidden = !popover.hidden;
            elements.deviceToggle.setAttribute('aria-expanded', String(!popover.hidden));
        });
        document.addEventListener('click', (event) => {
            const popover = $('[data-device-popover]');
            if (popover && !popover.hidden && !event.target.closest('[data-device-popover], [data-device-toggle]')) {
                popover.hidden = true;
                elements.deviceToggle.setAttribute('aria-expanded', 'false');
            }
        });
        $$('[data-player-expand]').forEach((button) => {
            button.addEventListener('click', () => {
                if (!window.matchMedia('(max-width: 680px)').matches) return;
                player.classList.toggle('is-expanded');
                document.body.classList.toggle('player-expanded');
                const expanded = player.classList.contains('is-expanded');
                $$('[data-player-expand]').forEach((control) => control.setAttribute('aria-label', expanded ? 'Collapse player' : 'Expand player'));
            });
        });
        window.addEventListener('resize', () => {
            if (!window.matchMedia('(max-width: 680px)').matches) {
                player.classList.remove('is-expanded');
                document.body.classList.remove('player-expanded');
            }
        });

        audio.addEventListener('play', () => setPlayState(true));
        audio.addEventListener('pause', () => setPlayState(false));
        audio.addEventListener('loadedmetadata', () => {
            elements.duration.textContent = formatTime(audio.duration);
        });
        audio.addEventListener('timeupdate', () => {
            const progress = audio.duration ? (audio.currentTime / audio.duration) * 100 : 0;
            elements.timeline.value = String(progress);
            elements.currentTime.textContent = formatTime(audio.currentTime);
            const second = Math.floor(audio.currentTime);
            if (second !== lastSavedSecond && second % 5 === 0) {
                lastSavedSecond = second;
                savePlayer();
            }
            if ('mediaSession' in navigator && Number.isFinite(audio.duration) && audio.duration > 0) {
                try {
                    navigator.mediaSession.setPositionState({
                        duration: audio.duration,
                        playbackRate: audio.playbackRate,
                        position: Math.min(audio.currentTime, audio.duration),
                    });
                } catch (_) {}
            }
        });
        audio.addEventListener('ended', () => {
            if (repeat) {
                audio.currentTime = 0;
                audio.play().catch(() => {});
            } else {
                chooseAdjacent(1);
            }
        });
        audio.addEventListener('error', () => {
            setPlayState(false);
            showToast('Audio file unavailable on this device.');
        });
    }

    const searchPanel = $('[data-search-panel]');
    const searchInput = $('[data-search-input]');
    const searchResults = $('[data-search-results]');
    let searchTimer;
    let searchFocusReturn = null;
    let latestSearchTracks = [];

    function openSearch() {
        if (!searchPanel || !searchInput) return;
        searchFocusReturn = document.activeElement;
        searchPanel.hidden = false;
        document.body.classList.add('search-open');
        requestAnimationFrame(() => {
            searchPanel.classList.add('open');
            searchInput.focus();
        });
    }

    function closeSearch(restoreFocus = true) {
        if (!searchPanel) return;
        searchPanel.classList.remove('open');
        document.body.classList.remove('search-open');
        if (restoreFocus && searchFocusReturn instanceof HTMLElement) searchFocusReturn.focus();
        setTimeout(() => { searchPanel.hidden = true; }, 180);
    }

    $$('[data-search-open]').forEach((button) => button.addEventListener('click', openSearch));
    $$('[data-search-close]').forEach((button) => button.addEventListener('click', () => closeSearch(true)));
    searchPanel?.addEventListener('click', (event) => {
        if (event.target === searchPanel) closeSearch(true);
    });
    $$('[data-search-suggestion]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!searchInput) return;
            searchInput.value = button.dataset.searchSuggestion || '';
            searchInput.dispatchEvent(new Event('input', { bubbles: true }));
            searchInput.focus();
        });
    });

    document.addEventListener('keydown', (event) => {
        const target = event.target;
        const typing = target instanceof HTMLElement && (target.matches('input, textarea, select') || target.isContentEditable);
        const interactive = target instanceof HTMLElement && target.closest('button, a, [role="button"]');

        if (event.key === 'Escape') {
            if (searchPanel?.classList.contains('open')) closeSearch(true);
            const queuePanel = $('[data-queue-panel]');
            if (queuePanel?.classList.contains('open')) closeQueue();
            $('[data-device-popover]')?.setAttribute('hidden', '');
            if (player?.classList.contains('is-expanded')) {
                player.classList.remove('is-expanded');
                document.body.classList.remove('player-expanded');
            }
        }
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            openSearch();
        }
        if (!typing && !interactive && event.code === 'Space') {
            event.preventDefault();
            elements.playPause?.click();
        }
        if (!typing && event.key.toLowerCase() === 'q') {
            elements.queueToggle?.click();
        }
        if (!typing && event.key.toLowerCase() === 'm') {
            elements.mute?.click();
        }
        if (searchPanel?.classList.contains('open') && (event.key === 'ArrowDown' || event.key === 'ArrowUp')) {
            const results = $$('.search-result', searchPanel);
            if (!results.length) return;
            event.preventDefault();
            const index = results.indexOf(document.activeElement);
            const nextIndex = event.key === 'ArrowDown'
                ? Math.min(results.length - 1, index + 1)
                : Math.max(0, index < 0 ? results.length - 1 : index - 1);
            results[nextIndex].focus();
        }
        if (event.key === 'Tab' && searchPanel?.classList.contains('open')) {
            const focusable = $$('button:not([disabled]), input:not([disabled]), a[href]', searchPanel)
                .filter((node) => node.offsetParent !== null);
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    function makeElement(tag, className, text) {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (text !== undefined) element.textContent = text;
        return element;
    }

    function showSearchSkeleton() {
        searchResults.replaceChildren();
        const group = makeElement('div', 'search-skeleton');
        for (let index = 0; index < 5; index += 1) group.append(makeElement('span', 'skeleton-row'));
        searchResults.append(group);
    }

    function renderSearch(payload) {
        searchResults.replaceChildren();
        latestSearchTracks = payload.results.tracks || [];
        const collectionIds = latestSearchTracks.map((track) => track.id).join(',');
        const groups = [
            ['Releases', payload.results.releases || [], (release) => {
                const link = makeElement('a', 'search-result');
                link.href = release.url;
                const image = document.createElement('img');
                image.src = release.cover_url;
                image.alt = '';
                const copy = makeElement('span');
                copy.append(
                    makeElement('strong', '', release.title),
                    makeElement('small', '', `${release.artist_name}${release.release_year ? ` / ${release.release_year}` : ''}`)
                );
                link.append(image, copy, makeElement('span', 'search-result-action', 'Open'));
                return link;
            }],
            ['Songs', latestSearchTracks, (track) => {
                const button = makeElement('button', 'search-result');
                button.type = 'button';
                button.dataset.playTrack = String(track.id);
                button.dataset.playCollection = collectionIds;
                button.dataset.queueLabel = `Search: ${payload.query}`;
                const image = document.createElement('img');
                image.src = track.cover_url;
                image.alt = '';
                const copy = makeElement('span');
                copy.append(makeElement('strong', '', track.title), makeElement('small', '', track.artist_name));
                button.append(image, copy, makeElement('span', 'search-result-action', 'Play'));
                return button;
            }],
            ['Artists', payload.results.artists || [], (artist) => {
                const link = makeElement('a', 'search-result');
                link.href = artist.url;
                const image = document.createElement('img');
                image.src = artist.image_url;
                image.alt = '';
                const copy = makeElement('span');
                copy.append(makeElement('strong', '', artist.name), makeElement('small', '', 'Artist'));
                link.append(image, copy, makeElement('span', 'search-result-action', 'Open'));
                return link;
            }],
            ['Genres', payload.results.genres || [], (genre) => {
                const link = makeElement('a', 'search-result search-genre');
                link.href = genre.url;
                link.style.setProperty('--result-accent', genre.accent_color);
                link.append(makeElement('strong', '', genre.name), makeElement('span', 'search-result-action', 'Browse'));
                return link;
            }],
        ];

        let total = 0;
        groups.forEach(([title, items, renderer]) => {
            if (!items.length) return;
            total += items.length;
            const group = makeElement('section', 'search-group');
            group.append(makeElement('h3', '', title));
            const list = makeElement('div', 'search-result-list');
            items.forEach((item) => list.append(renderer(item)));
            group.append(list);
            searchResults.append(group);
        });

        if (!total) {
            const empty = makeElement('div', 'search-empty');
            empty.innerHTML = '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>';
            empty.append(makeElement('strong', '', `No results for "${payload.query}"`), makeElement('span', '', 'Try an artist, song or genre.'));
            searchResults.append(empty);
        }
    }

    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const query = searchInput.value.trim();
        const fullSearch = $('[data-search-all]');
        if (fullSearch) fullSearch.href = endpoint(`search.php?q=${encodeURIComponent(query)}`);
        $('[data-search-quick]')?.classList.toggle('is-hidden', query.length > 0);
        if (query.length < 2) {
            searchResults.replaceChildren();
            const empty = makeElement('div', 'search-empty');
            empty.append(makeElement('strong', '', 'Find your next favorite'), makeElement('span', '', 'Start typing to search the full catalog.'));
            searchResults.append(empty);
            return;
        }
        searchTimer = setTimeout(async () => {
            showSearchSkeleton();
            try {
                const response = await fetch(endpoint(`api/search.php?q=${encodeURIComponent(query)}`), {
                    headers: { Accept: 'application/json' },
                });
                if (!response.ok) throw new Error('Search failed');
                renderSearch(await response.json());
            } catch (_) {
                searchResults.replaceChildren(makeElement('div', 'search-empty', 'Search is temporarily unavailable.'));
            }
        }, 180);
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-like-form]');
        if (!form) return;
        event.preventDefault();
        const button = $('.like-button', form);
        if (!button || button.dataset.pending === 'true') return;

        const wasActive = button.getAttribute('aria-pressed') === 'true';
        const nextActive = !wasActive;
        button.dataset.pending = 'true';
        button.classList.toggle('is-active', nextActive);
        button.setAttribute('aria-pressed', String(nextActive));
        button.setAttribute('aria-label', nextActive ? 'Remove from liked songs' : 'Add to liked songs');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error('Like request failed');
            const payload = await response.json();
            button.classList.toggle('is-active', Boolean(payload.liked));
            button.setAttribute('aria-pressed', String(Boolean(payload.liked)));
            showToast(payload.message || 'Liked songs updated.');
        } catch (_) {
            button.classList.toggle('is-active', wasActive);
            button.setAttribute('aria-pressed', String(wasActive));
            showToast('Could not update liked songs.');
        } finally {
            delete button.dataset.pending;
        }
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-follow-form]');
        if (!form) return;
        event.preventDefault();
        const button = $('[data-follow-button]', form);
        if (!button || button.dataset.pending === 'true') return;
        const wasFollowing = button.getAttribute('aria-pressed') === 'true';
        button.dataset.pending = 'true';
        button.textContent = wasFollowing ? 'Follow' : 'Following';
        button.classList.toggle('is-active', !wasFollowing);
        button.setAttribute('aria-pressed', String(!wasFollowing));

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error('Follow request failed');
            const payload = await response.json();
            button.textContent = payload.following ? 'Following' : 'Follow';
            button.classList.toggle('is-active', Boolean(payload.following));
            button.setAttribute('aria-pressed', String(Boolean(payload.following)));
            showToast(payload.message || 'Artist updated.');
        } catch (_) {
            button.textContent = wasFollowing ? 'Following' : 'Follow';
            button.classList.toggle('is-active', wasFollowing);
            button.setAttribute('aria-pressed', String(wasFollowing));
            showToast('Could not update this artist.');
        } finally {
            delete button.dataset.pending;
        }
    });

    const menuToggle = $('[data-menu-toggle]');
    const mobileMenu = $('[data-mobile-menu]');
    const navScrim = $('[data-nav-scrim]');

    function closeMobileNav() {
        mobileMenu?.classList.remove('open');
        menuToggle?.setAttribute('aria-expanded', 'false');
        navScrim?.classList.remove('visible');
        document.body.classList.remove('nav-open');
    }

    menuToggle?.addEventListener('click', () => {
        const open = !mobileMenu?.classList.contains('open');
        mobileMenu?.classList.toggle('open', open);
        navScrim?.classList.toggle('visible', open);
        document.body.classList.toggle('nav-open', open);
        menuToggle.setAttribute('aria-expanded', String(open));
    });
    navScrim?.addEventListener('click', closeMobileNav);
    $$('.rail-nav a, .rail-library a, .rail-bottom a').forEach((link) => link.addEventListener('click', closeMobileNav));

    $$('[data-auth-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const mode = tab.dataset.authTab;
            $$('[data-auth-tab]').forEach((item) => item.setAttribute('aria-selected', String(item === tab)));
            $$('[data-auth-panel]').forEach((panel) => {
                panel.hidden = panel.dataset.authPanel !== mode;
            });
            $('[data-auth-shell]')?.setAttribute('data-mode', mode);
        });
    });

    $$('[data-password-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const input = toggle.parentElement.querySelector('input');
            const reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            toggle.textContent = reveal ? 'Hide' : 'Show';
            toggle.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
        });
    });

    $$('[data-dialog-open]').forEach((button) => {
        button.addEventListener('click', () => {
            const dialog = $(`[data-dialog="${button.dataset.dialogOpen}"]`);
            dialog?.showModal();
        });
    });
    $$('[data-dialog-close]').forEach((button) => {
        button.addEventListener('click', () => button.closest('dialog')?.close());
    });

    $$('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });

    $$('[data-dismiss-flash]').forEach((button) => {
        button.addEventListener('click', () => button.closest('.flash')?.remove());
    });

    $$('.file-drop input[type="file"]').forEach((input) => {
        input.addEventListener('change', () => {
            const label = input.closest('.file-drop')?.querySelector('[data-file-name]');
            if (label && input.files?.[0]) label.textContent = input.files[0].name;
        });
    });

    $$('[data-select-filter]').forEach((input) => {
        input.addEventListener('input', () => {
            const select = document.getElementById(input.dataset.selectFilter);
            if (!select) return;
            const query = input.value.trim().toLowerCase();
            $$('option', select).forEach((option) => {
                option.hidden = query.length > 0 && !option.textContent.toLowerCase().includes(query);
            });
            const firstMatch = $('option:not([hidden])', select);
            if (firstMatch) select.value = firstMatch.value;
        });
    });
})();
