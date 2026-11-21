'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const header = document.querySelector('.top-heading');
  if (!header) return;

  const links = Array.from(document.querySelectorAll('#toc a[href]'));
  const home = header.querySelector('.heading a[href="/"]');
  if (home && !links.some(link => new URL(link.href).pathname === '/')) links.unshift(home);
  if (links.length < 2) return;

  const normalize = path => path.replace(/\/index(?:\.php)?\/?$/, '/').replace(/\.php$/, '').replace(/\/$/, '') || '/';
  const aliases = {
    '/event': '/events',
    '/event-entry': '/events',
    '/release': '/releases',
    '/releases_details': '/releases',
    '/releases_grid': '/releases',
    '/disco': '/releases',
    '/disco_details': '/releases',
    '/disco_grid': '/releases',
    '/disco_table': '/releases'
  };
  const path = normalize(window.location.pathname);
  const currentPath = aliases[path] || path;
  const selected = links.findIndex(link => link.hasAttribute('aria-current'));
  const current = selected >= 0 ? selected : links.findIndex(link => {
    const url = new URL(link.href);
    if (normalize(url.pathname) !== currentPath) return false;
    for (const [key, value] of url.searchParams) {
      if (new URLSearchParams(window.location.search).get(key) !== value) return false;
    }
    return !url.search && (currentPath !== '/' || !new URLSearchParams(window.location.search).has('brand')) || !!url.search;
  });
  if (current < 0) return;

  let startX = null;
  let startY = null;
  let pointerId = null;
  let horizontal = false;
  let locked = false;
  let suppressClick = false;

  const reset = () => {
    startX = null;
    startY = null;
    pointerId = null;
    horizontal = false;
    locked = false;
  };
  header.style.touchAction = 'pan-y';
  header.addEventListener('dragstart', event => {
    if (event.target.closest('.heading a')) event.preventDefault();
  });
  header.addEventListener('pointerdown', event => {
    if (event.pointerType === 'mouse' && event.button !== 0) return;
    if (pointerId !== null) return;
    if (event.target.closest('#toc, #settings-panel, button, input, textarea, select, [role="button"], .no-swipe')) return;
    const link = event.target.closest('a');
    if (link && !link.matches('.heading a')) return;
    pointerId = event.pointerId;
    startX = event.clientX;
    startY = event.clientY;
    suppressClick = false;
  });
  header.addEventListener('pointermove', event => {
    if (startX === null || event.pointerId !== pointerId) return;
    const dx = event.clientX - startX;
    const dy = event.clientY - startY;
    if (!locked && (Math.abs(dx) >= 16 || Math.abs(dy) >= 16)) {
      locked = true;
      horizontal = Math.abs(dx) > Math.abs(dy);
      if (horizontal && typeof header.setPointerCapture === 'function') {
        try {
          header.setPointerCapture(pointerId);
        } catch (error) {
          if (error.name !== 'NotFoundError') throw error;
        }
      }
    }
    if (horizontal) event.preventDefault();
  });
  header.addEventListener('pointerup', event => {
    if (startX === null || event.pointerId !== pointerId) return;
    const dx = event.clientX - startX;
    const dy = event.clientY - startY;
    const committed = horizontal && Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 75;
    suppressClick = horizontal;
    reset();
    if (committed) {
      localStorage.removeItem('global.site_play_active');
      window.location.href = links[(current + (dx < 0 ? 1 : -1) + links.length) % links.length].href;
    }
  });
  header.addEventListener('click', event => {
    if (suppressClick && event.detail !== 0) {
      event.preventDefault();
      event.stopPropagation();
      suppressClick = false;
    }
  }, true);
  for (const name of ['pointercancel', 'lostpointercapture']) {
    header.addEventListener(name, event => { if (event.pointerId === pointerId) reset(); });
  }
});
