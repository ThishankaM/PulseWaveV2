(() => {
    'use strict';

    const config = window.PULSEWAVE || { baseUrl: '', csrfToken: '' };
    const baseUrl = String(config.baseUrl || '').replace(/\/$/, '');
    const endpoint = (path) => `${baseUrl}/${String(path).replace(/^\//, '')}`;
    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

    function showToast(message) {
        const toast = $('[data-toast]');
        if (!toast) return;
        toast.textContent = message;
        toast.hidden = false;
        toast.classList.add('visible');
        clearTimeout(showToast.timer);
        showToast.timer = setTimeout(() => {
            toast.classList.remove('visible');
            setTimeout(() => { toast.hidden = true; }, 200);
        }, 3600);
    }

    function formatTime(seconds) {
        if (!Number.isFinite(seconds) || seconds < 0) return '0:00';
        const minutes = Math.floor(seconds / 60);
        const remainder = Math.floor(seconds % 60);
        return `${minutes}:${String(remainder).padStart(2, '0')}`;
    }

    const player = $('[data-player]');
    const audio = $('[data-audio]');
    let tracks = [];
    let currentIndex = -1;
    let shuffle = false;
    let repeat = false;
    let lastSavedSecond = -1;
    const recordedPlays = new Set();

    const playerElements = player ? {
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

    function savePlayer() {
        if (currentIndex < 0 || !tracks[currentIndex]) return;
        try {
            localStorage.setItem('pulsewave.player', JSON.stringify({
                trackId: tracks[currentIndex].id,
                currentTime: audio.currentTime || 0,
                volume: audio.volume,
                shuffle,
                repeat,
            }));
        } catch (_) {
            // Playback remains functional when storage is unavailable.
        }
    }

    function restorePlayer() {
        let state = null;
        try {
            state = JSON.parse(localStorage.getItem('pulsewave.player') || 'null');
        } catch (_) {
            state = null;
        }
        if (!state) return;

        const index = tracks.findIndex((track) => Number(track.id) === Number(state.trackId));
        if (index >= 0) {
            selectTrack(index, false);
            audio.addEventListener('loadedmetadata', () => {
                if (Number.isFinite(Number(state.currentTime)) && Number(state.currentTime) < audio.duration) {
                    audio.currentTime = Number(state.currentTime);
                }
            }, { once: true });
        }
        audio.volume = Math.min(1, Math.max(0, Number(state.volume ?? 0.8)));
        playerElements.volume.value = String(audio.volume);
        shuffle = Boolean(state.shuffle);
        repeat = Boolean(state.repeat);
        playerElements.shuffle.classList.toggle('is-active', shuffle);
        playerElements.repeat.classList.toggle('is-active', repeat);
    }

    function selectTrack(index, autoplay = true) {
        const track = tracks[index];
        if (!track || !audio) return;

        currentIndex = index;
        audio.src = track.audio_url;
        playerElements.cover.src = track.cover_url;
        playerElements.title.textContent = track.title;
        playerElements.artist.textContent = track.artist;
        player.classList.add('has-track');
        document.title = `${track.title} · PulseWave`;
        savePlayer();

        if (autoplay) {
            audio.play().then(() => {
                playerElements.playPause.textContent = '❚❚';
                playerElements.playPause.setAttribute('aria-label', 'Pause');
                recordPlay(track.id);
            }).catch(() => {
                showToast('Audio is unavailable. Copy the original file into public/uploads/audio.');
            });
        }
    }

    async function playTrackById(id, autoplay = true) {
        await catalogReady;
        const index = tracks.findIndex((track) => Number(track.id) === Number(id));
        if (index < 0) {
            showToast('That track is not available.');
            return;
        }
        selectTrack(index, autoplay);
    }

    function chooseAdjacent(direction) {
        if (!tracks.length) return;
        let nextIndex;
        if (shuffle && tracks.length > 1) {
            do {
                nextIndex = Math.floor(Math.random() * tracks.length);
            } while (nextIndex === currentIndex);
        } else {
            nextIndex = (currentIndex + direction + tracks.length) % tracks.length;
        }
        selectTrack(nextIndex, true);
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
            // Analytics must never interrupt playback.
        }
    }

    document.addEventListener('click', async (event) => {
        const playButton = event.target.closest('[data-play-track]');
        if (playButton) {
            event.preventDefault();
            await playTrackById(playButton.dataset.playTrack);
            if (playButton.closest('[data-search-panel]')) closeSearch();
        }

        const shuffleArtist = event.target.closest('[data-shuffle-artist]');
        if (shuffleArtist) {
            await catalogReady;
            const artistTracks = tracks.filter((track) => Number(track.artist_id) === Number(shuffleArtist.dataset.shuffleArtist));
            if (artistTracks.length) {
                const track = artistTracks[Math.floor(Math.random() * artistTracks.length)];
                await playTrackById(track.id);
            }
        }
    });

    if (player && audio) {
        playerElements.playPause.addEventListener('click', () => {
            if (currentIndex < 0) {
                if (tracks.length) selectTrack(0, true);
                return;
            }
            if (audio.paused) {
                audio.play().then(() => recordPlay(tracks[currentIndex].id)).catch(() => {
                    showToast('This audio file is not available in the package.');
                });
            } else {
                audio.pause();
            }
        });
        playerElements.previous.addEventListener('click', () => chooseAdjacent(-1));
        playerElements.next.addEventListener('click', () => chooseAdjacent(1));
        playerElements.shuffle.addEventListener('click', () => {
            shuffle = !shuffle;
            playerElements.shuffle.classList.toggle('is-active', shuffle);
            savePlayer();
        });
        playerElements.repeat.addEventListener('click', () => {
            repeat = !repeat;
            playerElements.repeat.classList.toggle('is-active', repeat);
            savePlayer();
        });
        playerElements.volume.addEventListener('input', () => {
            audio.volume = Number(playerElements.volume.value);
            audio.muted = false;
            playerElements.mute.classList.toggle('is-active', audio.volume === 0);
            savePlayer();
        });
        playerElements.mute.addEventListener('click', () => {
            audio.muted = !audio.muted;
            playerElements.mute.classList.toggle('is-active', audio.muted);
        });
        playerElements.timeline.addEventListener('input', () => {
            if (Number.isFinite(audio.duration)) {
                audio.currentTime = (Number(playerElements.timeline.value) / 100) * audio.duration;
            }
        });
        audio.addEventListener('play', () => {
            playerElements.playPause.textContent = '❚❚';
            playerElements.playPause.setAttribute('aria-label', 'Pause');
        });
        audio.addEventListener('pause', () => {
            playerElements.playPause.textContent = '▶';
            playerElements.playPause.setAttribute('aria-label', 'Play');
        });
        audio.addEventListener('loadedmetadata', () => {
            playerElements.duration.textContent = formatTime(audio.duration);
        });
        audio.addEventListener('timeupdate', () => {
            const progress = audio.duration ? (audio.currentTime / audio.duration) * 100 : 0;
            playerElements.timeline.value = String(progress);
            playerElements.currentTime.textContent = formatTime(audio.currentTime);
            const second = Math.floor(audio.currentTime);
            if (second !== lastSavedSecond && second % 5 === 0) {
                lastSavedSecond = second;
                savePlayer();
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
            playerElements.playPause.textContent = '▶';
            showToast('Audio file unavailable. See public/uploads/audio/README.md.');
        });
    }

    const searchPanel = $('[data-search-panel]');
    const searchInput = $('[data-search-input]');
    const searchResults = $('[data-search-results]');
    let searchTimer;

    function openSearch() {
        if (!searchPanel) return;
        searchPanel.hidden = false;
        requestAnimationFrame(() => searchPanel.classList.add('open'));
        searchInput.focus();
    }

    function closeSearch() {
        if (!searchPanel) return;
        searchPanel.classList.remove('open');
        setTimeout(() => { searchPanel.hidden = true; }, 180);
    }

    $('[data-search-open]')?.addEventListener('click', openSearch);
    $('[data-search-close]')?.addEventListener('click', closeSearch);
    searchPanel?.addEventListener('click', (event) => {
        if (event.target === searchPanel) closeSearch();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeSearch();
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            openSearch();
        }
    });

    function emptyElement(tag, className, text) {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (text !== undefined) element.textContent = text;
        return element;
    }

    function renderSearch(payload) {
        searchResults.replaceChildren();
        const groups = [
            ['Tracks', payload.results.tracks, (track) => {
                const button = emptyElement('button', 'search-result');
                button.type = 'button';
                button.dataset.playTrack = track.id;
                const image = document.createElement('img');
                image.src = track.cover_url;
                image.alt = '';
                const copy = emptyElement('span');
                copy.append(emptyElement('strong', '', track.title), emptyElement('small', '', track.artist_name));
                button.append(image, copy, emptyElement('span', 'search-result-action', 'Play'));
                return button;
            }],
            ['Artists', payload.results.artists, (artist) => {
                const link = emptyElement('a', 'search-result');
                link.href = artist.url;
                const image = document.createElement('img');
                image.src = artist.image_url;
                image.alt = '';
                const copy = emptyElement('span');
                copy.append(emptyElement('strong', '', artist.name), emptyElement('small', '', 'Artist'));
                link.append(image, copy, emptyElement('span', 'search-result-action', 'Open'));
                return link;
            }],
            ['Genres', payload.results.genres, (genre) => {
                const link = emptyElement('a', 'search-result search-genre');
                link.href = genre.url;
                link.style.setProperty('--result-accent', genre.accent_color);
                link.append(emptyElement('strong', '', genre.name), emptyElement('span', 'search-result-action', 'Browse'));
                return link;
            }],
        ];

        let total = 0;
        groups.forEach(([title, items, renderer]) => {
            if (!items.length) return;
            total += items.length;
            const group = emptyElement('section', 'search-group');
            group.append(emptyElement('h3', '', title));
            const list = emptyElement('div', 'search-result-list');
            items.forEach((item) => list.append(renderer(item)));
            group.append(list);
            searchResults.append(group);
        });

        if (!total) {
            searchResults.append(emptyElement('p', 'empty-copy', `No results for “${payload.query}”.`));
        }
    }

    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const query = searchInput.value.trim();
        if (query.length < 2) {
            searchResults.replaceChildren(emptyElement('p', 'empty-copy', 'Enter at least two characters.'));
            return;
        }
        searchTimer = setTimeout(async () => {
            searchResults.replaceChildren(emptyElement('p', 'empty-copy', 'Searching…'));
            try {
                const response = await fetch(endpoint(`api/search.php?q=${encodeURIComponent(query)}`), {
                    headers: { Accept: 'application/json' },
                });
                if (!response.ok) throw new Error('Search failed');
                renderSearch(await response.json());
            } catch (_) {
                searchResults.replaceChildren(emptyElement('p', 'empty-copy', 'Search is temporarily unavailable.'));
            }
        }, 220);
    });

    const menuToggle = $('[data-menu-toggle]');
    const mobileMenu = $('[data-mobile-menu]');
    menuToggle?.addEventListener('click', () => {
        const open = mobileMenu.classList.toggle('open');
        menuToggle.setAttribute('aria-expanded', String(open));
    });

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
})();
