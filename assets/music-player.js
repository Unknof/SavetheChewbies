(function () {
  'use strict';

  const STORAGE_KEY = 'stc_music_player_v1';

  const tracks = [
    { file: 'Save the Chewbies_EN_Chill.mp3' },
    { file: 'Save the Chewbies_EN_Faster.mp3' },
    { file: 'Save the Chewbies_EN_Rock.mp3' },
    { file: 'Save_the_Chewbies_CN_1.mp3' },
    { file: 'Save_the_Chewbies_CN_2.mp3' },
  ];

  function safeJsonParse(raw) {
    try {
      return JSON.parse(raw);
    } catch {
      return null;
    }
  }

  function loadState() {
    const raw = localStorage.getItem(STORAGE_KEY);
    const parsed = raw ? safeJsonParse(raw) : null;
    if (!parsed || typeof parsed !== 'object') return {};
    return parsed;
  }

  function saveState(next) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
    } catch {
      // ignore
    }
  }

  function stripExt(name) {
    return name.replace(/\.[^.]+$/, '');
  }

  function isChinese(textOrFileName) {
    if (/[\u4E00-\u9FFF]/.test(textOrFileName)) return true;
    return /(^|[^a-z0-9])CN([^a-z0-9]|$)/i.test(textOrFileName) || /_CN_/i.test(textOrFileName);
  }

  function buildAssetUrl(fileName) {
    // Encode each segment safely, keeping the path separator.
    const base = './assets/music/';
    return base + encodeURIComponent(fileName);
  }

  function clamp(value, min, max) {
    return Math.max(min, Math.min(max, value));
  }

  function el(tag, attrs, children) {
    const node = document.createElement(tag);
    if (attrs) {
      for (const [k, v] of Object.entries(attrs)) {
        if (v === undefined || v === null) continue;
        if (k === 'class') node.className = String(v);
        else if (k === 'text') node.textContent = String(v);
        else if (k.startsWith('data-')) node.setAttribute(k, String(v));
        else if (k === 'html') node.innerHTML = String(v);
        else node.setAttribute(k, String(v));
      }
    }
    if (children && children.length) {
      for (const child of children) node.appendChild(child);
    }
    return node;
  }

  function ensureUi() {
    if (document.getElementById('stc-music-fab')) return;

    const fab = el('button', {
      id: 'stc-music-fab',
      class: 'stc-music-fab',
      type: 'button',
      title: 'Music player',
      'aria-haspopup': 'dialog',
      'aria-expanded': 'false',
      'aria-controls': 'stc-music-panel',
    }, [
      el('span', { class: 'stc-music-fab__glyph', 'aria-hidden': 'true', text: '♫' }),
      el('span', { class: 'sr-only', text: 'Open music player' }),
    ]);

    const panel = el('div', {
      id: 'stc-music-panel',
      class: 'stc-music-panel',
      role: 'dialog',
      'aria-modal': 'false',
      'aria-label': 'Music player',
      hidden: 'hidden',
    });

    const header = el('div', { class: 'stc-music-panel__header' }, [
      el('div', { class: 'stc-music-panel__title', text: 'Music' }),
      el('button', { type: 'button', class: 'stc-music-panel__close', title: 'Close', 'aria-label': 'Close' }, [
        el('span', { 'aria-hidden': 'true', text: '×' }),
      ]),
    ]);

    const nowPlaying = el('div', { class: 'stc-music-now', id: 'stc-music-now', text: 'Not playing' });

    const audio = el('audio', {
      id: 'stc-audio',
      class: 'stc-music-audio',
      controls: 'controls',
      preload: 'none',
    });

    const controls = el('div', { class: 'stc-music-controls' }, [
      el('button', { type: 'button', class: 'button button-secondary stc-music-btn', id: 'stc-music-random' }, [
        el('span', { text: 'Random' }),
      ]),
      el('button', { type: 'button', class: 'button button-secondary stc-music-btn', id: 'stc-music-stop' }, [
        el('span', { text: 'Stop' }),
      ]),
    ]);

    const listContainer = el('div', { class: 'stc-music-list', id: 'stc-music-list' });

    panel.appendChild(header);
    panel.appendChild(nowPlaying);
    panel.appendChild(audio);
    panel.appendChild(controls);
    panel.appendChild(listContainer);

    document.body.appendChild(fab);
    document.body.appendChild(panel);

    return { fab, panel, audio, nowPlaying, listContainer };
  }

  function getSharedElements() {
    const fab = document.getElementById('stc-music-fab');
    const panel = document.getElementById('stc-music-panel');
    const audio = document.getElementById('stc-audio');
    const nowPlaying = document.getElementById('stc-music-now');
    const listContainer = document.getElementById('stc-music-list');
    if (!fab || !panel || !audio || !nowPlaying || !listContainer) return null;
    return { fab, panel, audio, nowPlaying, listContainer };
  }

  function setNowPlaying(label) {
    const elNow = document.getElementById('stc-music-now');
    if (elNow) elNow.textContent = label;

    const mirrored = document.querySelectorAll('[data-stc-now-playing]');
    mirrored.forEach((n) => {
      n.textContent = label;
    });
  }

  function playTrack(audio, index, opts) {
    const track = tracks[index];
    if (!track) return;

    const url = buildAssetUrl(track.file);
    const title = stripExt(track.file);
    const suffix = isChinese(title) ? ' (CN)' : '';

    const shouldStart = !!(opts && opts.autoplay);

    if (audio.src !== url) {
      audio.removeAttribute('src');
      audio.load();
      audio.src = url;
    }

    setNowPlaying('Now playing: ' + title + suffix);

    const state = loadState();
    saveState({
      ...state,
      lastIndex: index,
    });

    if (shouldStart) {
      const p = audio.play();
      if (p && typeof p.catch === 'function') {
        p.catch(function () {
          // Autoplay will be blocked until user gesture.
        });
      }
    }
  }

  function stopPlayback(audio) {
    audio.pause();
    try {
      audio.currentTime = 0;
    } catch {
      // ignore
    }
    setNowPlaying('Stopped');
  }

  function renderTrackList(container, audio) {
    container.innerHTML = '';

    const ul = el('ul', { class: 'stc-music-ul' });

    tracks.forEach((track, idx) => {
      const title = stripExt(track.file);
      const cnBadge = isChinese(title) ? el('span', { class: 'stc-music-badge', text: 'CN' }) : null;

      const nameWrap = el('div', { class: 'stc-music-name' }, [
        el('span', { text: title }),
        cnBadge ? cnBadge : document.createTextNode(''),
      ]);

      const playBtn = el('button', { type: 'button', class: 'button button-secondary stc-music-play' }, [
        el('span', { text: 'Play' }),
      ]);
      playBtn.addEventListener('click', function () {
        playTrack(audio, idx, { autoplay: true });
      });

      const download = el('a', {
        class: 'text-link stc-music-download',
        href: buildAssetUrl(track.file),
        download: track.file,
      }, [el('span', { text: 'Download' })]);

      const row = el('li', { class: 'stc-music-row' }, [
        nameWrap,
        el('div', { class: 'stc-music-row__actions' }, [playBtn, download]),
      ]);

      ul.appendChild(row);
    });

    container.appendChild(ul);
  }

  function wireUp(ui) {
    const { fab, panel, audio, listContainer } = ui;

    function open() {
      panel.hidden = false;
      fab.setAttribute('aria-expanded', 'true');
      panel.classList.add('is-open');
    }

    function close() {
      panel.hidden = true;
      fab.setAttribute('aria-expanded', 'false');
      panel.classList.remove('is-open');
    }

    function togglePlayPause() {
      if (audio.paused) {
        if (!audio.src) {
          // First interaction: pick something and start.
          const next = Math.floor(Math.random() * tracks.length);
          playTrack(audio, next, { autoplay: true });
          return;
        }

        const p = audio.play();
        if (p && typeof p.catch === 'function') p.catch(function () {});
      } else {
        audio.pause();
      }
    }

    function playRandom() {
      const state = loadState();
      const last = typeof state.lastIndex === 'number' ? state.lastIndex : -1;
      if (!tracks.length) return;

      let next = Math.floor(Math.random() * tracks.length);
      if (tracks.length > 1 && next === last) {
        next = (next + 1) % tracks.length;
      }
      playTrack(audio, next, { autoplay: true });
    }

    fab.addEventListener('click', function () {
      if (panel.hidden) open();
      else close();
    });

    const closeBtn = panel.querySelector('.stc-music-panel__close');
    if (closeBtn) closeBtn.addEventListener('click', close);

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !panel.hidden) close();
    });

    renderTrackList(listContainer, audio);

    const randomBtn = document.getElementById('stc-music-random');
    if (randomBtn) {
      randomBtn.addEventListener('click', playRandom);
    }

    const stopBtn = document.getElementById('stc-music-stop');
    if (stopBtn) stopBtn.addEventListener('click', function () { stopPlayback(audio); });

    // Optional header controls (small play button).
    document.querySelectorAll('[data-stc-music-open]').forEach((btn) => {
      btn.addEventListener('click', open);
    });
    document.querySelectorAll('[data-stc-music-toggle]').forEach((btn) => {
      btn.addEventListener('click', togglePlayPause);
    });
    document.querySelectorAll('[data-stc-music-random]').forEach((btn) => {
      btn.addEventListener('click', playRandom);
    });
    document.querySelectorAll('[data-stc-music-stop]').forEach((btn) => {
      btn.addEventListener('click', function () { stopPlayback(audio); });
    });

    // Public API (for debugging / future integrations).
    window.STC_MUSIC = {
      open,
      close,
      toggle: togglePlayPause,
      random: playRandom,
      stop: function () { stopPlayback(audio); },
      setTrack: function (index) { playTrack(audio, index, { autoplay: true }); },
    };

    audio.addEventListener('volumechange', function () {
      const state = loadState();
      saveState({
        ...state,
        volume: clamp(Number(audio.volume || 1), 0, 1),
        muted: !!audio.muted,
      });
    });

    // Optional embed inside pages (e.g., Gallery “Music” section).
    // NOTE: We keep a single shared <audio> element inside the floating panel.
    // Embeds provide lightweight controls + the track list that drive that shared player.
    const embeds = document.querySelectorAll('[data-stc-music-embed]');
    embeds.forEach((host) => {
      const header = el('div', { class: 'stc-music-embed__header' }, [
        el('div', { class: 'stc-music-embed__now' }, [
          el('span', { class: 'note', text: 'Now: ' }),
          el('span', { 'data-stc-now-playing': 'true', class: 'stc-music-embed__nowText', text: 'Not playing' }),
        ]),
      ]);

      const controlsRow = el('div', { class: 'stc-music-embed__controls' }, [
        el('button', { type: 'button', class: 'button button-secondary stc-music-btn' }, [
          el('span', { text: 'Open player' }),
        ]),
        el('button', { type: 'button', class: 'button button-secondary stc-music-btn' }, [
          el('span', { text: 'Play/Pause' }),
        ]),
        el('button', { type: 'button', class: 'button button-secondary stc-music-btn' }, [
          el('span', { text: 'Random' }),
        ]),
        el('button', { type: 'button', class: 'button button-secondary stc-music-btn' }, [
          el('span', { text: 'Stop' }),
        ]),
      ]);

      const [openBtn, toggleBtn, randomBtn, stopBtn] = controlsRow.querySelectorAll('button');

      if (openBtn) {
        openBtn.addEventListener('click', function () {
          panel.hidden = false;
          fab.setAttribute('aria-expanded', 'true');
          panel.classList.add('is-open');
        });
      }

      if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
          togglePlayPause();
        });
      }

      if (randomBtn) {
        randomBtn.addEventListener('click', playRandom);
      }

      if (stopBtn) stopBtn.addEventListener('click', function () { stopPlayback(audio); });

      const listWrap = el('div', { class: 'stc-music-embed__list' });
      renderTrackList(listWrap, audio);

      host.innerHTML = '';
      host.appendChild(header);
      host.appendChild(controlsRow);
      host.appendChild(listWrap);
    });

    // Restore prior settings (no autoplay)
    const state = loadState();
    if (typeof state.volume === 'number') audio.volume = clamp(state.volume, 0, 1);
    if (typeof state.muted === 'boolean') audio.muted = state.muted;
    if (typeof state.lastIndex === 'number') playTrack(audio, state.lastIndex, { autoplay: false });
  }

  function init() {
    if (!tracks.length) return;
    ensureUi();
    const ui = getSharedElements();
    if (!ui) return;
    wireUp(ui);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
