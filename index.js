'use strict';

const sidebarLayout = window.matchMedia('(min-width: 800px)');

function syncSidebarState() {
  const menuOpen = document.getElementById('toc').classList.contains('toc-open');
  const settings = document.getElementById('settings-panel');
  const settingsOpen = settings.classList.contains('settings-panel-open');
  settings.hidden = !settingsOpen;
  document.getElementById('toggle-menu-button').setAttribute('aria-expanded', String(menuOpen));
  document.getElementById('toggle-settings-panel-button').setAttribute('aria-expanded', String(settingsOpen));
  document.body.classList.toggle('toc-sidebar-open', menuOpen);
  document.body.classList.toggle('settings-sidebar-open', settingsOpen);
  document.body.classList.toggle('toc-lock', menuOpen && !sidebarLayout.matches);
  document.body.classList.toggle('settings-lock', settingsOpen && !sidebarLayout.matches);
  if (sidebarLayout.matches) {
    localStorage.setItem('global.toc_sidebar_open', String(menuOpen));
    localStorage.setItem('global.settings_sidebar_open', String(settingsOpen));
  }
}

function restoreSidebarState() {
  const savedMenu = localStorage.getItem('global.toc_sidebar_open');
  const savedSettings = localStorage.getItem('global.settings_sidebar_open');
  document.getElementById('toc').classList.toggle('toc-open', sidebarLayout.matches && savedMenu !== 'false');
  document.getElementById('settings-panel').classList.toggle('settings-panel-open', sidebarLayout.matches && savedSettings !== 'false');
  syncSidebarState();
}

function closeMobileSidebars() {
  if (sidebarLayout.matches) return;
  document.getElementById('toc').classList.remove('toc-open');
  document.getElementById('settings-panel').classList.remove('settings-panel-open');
  syncSidebarState();
}

function toggleMenu() {
  if (!sidebarLayout.matches) document.getElementById('settings-panel').classList.remove('settings-panel-open');
  document.getElementById('toc').classList.toggle('toc-open');
  syncSidebarState();
}

function toggleSettingsPanel() {
  if (!sidebarLayout.matches) document.getElementById('toc').classList.remove('toc-open');
  document.getElementById('settings-panel').classList.toggle('settings-panel-open');
  syncSidebarState();
}

function updateModeButtonState() {
  const active = !document.documentElement.classList.contains('invert-colors');
  const button = document.getElementById('toggle-mode-button');
  button.classList.toggle('button-on', active);
  button.setAttribute('aria-pressed', String(active));
}

function toggleMode() {
  const lightMode = document.documentElement.classList.toggle('invert-colors');
  localStorage.setItem('mode', lightMode ? 'dark' : 'light');
  updateModeButtonState();
}

function updateFullscreenButtonState() {
  const active = Boolean(document.fullscreenElement);
  const button = document.getElementById('toggle-fullscreen-button');
  button.classList.toggle('button-on', active);
  button.setAttribute('aria-pressed', String(active));
}

function showSettingsFeedback(message) {
  const feedback = document.getElementById('settings-feedback');
  feedback.textContent = message;
  feedback.hidden = false;
}

async function toggleFullscreen() {
  try {
    if (document.fullscreenElement) await document.exitFullscreen();
    else await document.documentElement.requestFullscreen();
  } catch (error) {
    console.error('Unable to change fullscreen mode', error);
    showSettingsFeedback('Unable to change fullscreen mode.');
  }
}

async function shareCurrentPage() {
  const url = window.location.href;
  if (navigator.share) {
    try {
      await navigator.share({ title: document.title, url });
      return;
    } catch (error) {
      if (error.name === 'AbortError') return;
      console.warn('Unable to share page', error);
    }
  }
  if (navigator.clipboard?.writeText) {
    try {
      await navigator.clipboard.writeText(url);
      showSettingsFeedback('Link copied.');
      return;
    } catch (error) {
      console.warn('Unable to copy share URL', error);
    }
  }
  window.prompt('Copy this link:', url);
}

function resetSettings() {
  localStorage.clear();
  window.location.reload();
}

document.addEventListener('DOMContentLoaded', () => {
  const actions = {
    'toggle-menu-button': toggleMenu,
    'toggle-settings-panel-button': toggleSettingsPanel,
    'toggle-menu-close-button': () => {
      document.getElementById('toc').classList.remove('toc-open');
      syncSidebarState();
      document.getElementById('toggle-menu-button').focus();
    },
    'toggle-settings-panel-close-button': () => {
      document.getElementById('settings-panel').classList.remove('settings-panel-open');
      syncSidebarState();
      document.getElementById('toggle-settings-panel-button').focus();
    },
    'toggle-mode-button': toggleMode,
    'toggle-fullscreen-button': toggleFullscreen,
    'share-settings-button': shareCurrentPage,
    'reset-settings-button': resetSettings
  };
  for (const [id, action] of Object.entries(actions)) {
    document.getElementById(id).addEventListener('click', action);
  }
  document.documentElement.classList.toggle('invert-colors', localStorage.getItem('mode') === 'dark');
  document.getElementById('toggle-fullscreen-button').disabled = !document.documentElement.requestFullscreen || document.fullscreenEnabled === false;
  updateModeButtonState();
  updateFullscreenButtonState();
  restoreSidebarState();
});

sidebarLayout.addEventListener('change', restoreSidebarState);
window.addEventListener('pagehide', closeMobileSidebars);
window.addEventListener('pageshow', closeMobileSidebars);
document.addEventListener('fullscreenchange', updateFullscreenButtonState);
document.addEventListener('click', event => {
  if (sidebarLayout.matches) return;
  if (event.target.closest('#toc a')) {
    closeMobileSidebars();
  } else if (!event.target.closest('#burger-container, #settings-menu-container')) {
    closeMobileSidebars();
  }
});
document.addEventListener('keydown', event => {
  if (event.key !== 'Escape' || sidebarLayout.matches) return;
  const settingsOpen = !document.getElementById('settings-panel').hidden;
  const menuOpen = document.getElementById('toc').classList.contains('toc-open');
  closeMobileSidebars();
  if (settingsOpen) document.getElementById('toggle-settings-panel-button').focus();
  else if (menuOpen) document.getElementById('toggle-menu-button').focus();
});
