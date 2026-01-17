(function () {
  const STORAGE_KEY = 'stc_cookie_banner_ack_v1';

  function hasConsent() {
    try {
      return localStorage.getItem(STORAGE_KEY) === '1';
    } catch (_) {
      return false;
    }
  }

  function setConsent() {
    try {
      localStorage.setItem(STORAGE_KEY, '1');
    } catch (_) {
      // Ignore storage errors; banner just won't persist.
    }
  }

  function renderBanner() {
    if (hasConsent()) return;

    const banner = document.createElement('div');
    banner.className = 'cookie-banner';
    banner.setAttribute('role', 'region');
    banner.setAttribute('aria-label', 'Cookie and data notice');

    const text = document.createElement('div');
    text.className = 'cookie-banner__text';
    text.innerHTML = 'We don’t use tracking cookies. Donations happen on Tiltify. We only store this preference in your browser. See <a class="text-link" href="./privacy.html">Privacy</a>.';
    banner.appendChild(text);

    const actions = document.createElement('div');
    actions.className = 'cookie-banner__actions';

    const accept = document.createElement('button');
    accept.type = 'button';
    accept.className = 'cookie-banner__button';
    accept.textContent = 'OK';
    accept.addEventListener('click', () => {
      setConsent();
      banner.remove();
    });

    actions.appendChild(accept);
    banner.appendChild(actions);

    document.body.appendChild(banner);
  }

  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    renderBanner();
  } else {
    document.addEventListener('DOMContentLoaded', renderBanner, { once: true });
  }
})();
