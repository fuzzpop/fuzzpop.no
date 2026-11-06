<header class="top-heading colored">
  <div id="burger-container" class="menu-container">
    <button id="toggle-menu-button" class="settings-toggle" type="button" title="Menu" aria-label="Menu" aria-controls="toc" aria-expanded="false">
      <svg class="icons" aria-hidden="true"><use href="/icons.svg#burger"></use></svg>
    </button>
    <nav id="toc" class="toc-container" aria-label="Main navigation">
      <ul>
        <?php
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $currentSection = str_starts_with($currentPath, '/disco') ? '/releases' : preg_replace('~\.php$~', '', $currentPath);
        foreach ([
            ['/', 'Home', 'home'],
            ['/releases', 'Releases', 'lp'],
            ['/artists', 'Artists', 'person'],
            ['/about', 'About', 'about'],
        ] as [$url, $label, $icon]):
        ?>
        <li><a href="<?php echo $url; ?>"<?php if ($currentSection === $url): ?> aria-current="page"<?php endif; ?>><svg class="icons" aria-hidden="true"><use href="/icons.svg#<?php echo $icon; ?>"></use></svg><?php echo $label; ?></a></li>
        <?php endforeach; ?>
      </ul>
      <div class="toc-close-wrap">
        <button id="toggle-menu-close-button" class="settings-toggle" type="button" title="Close" aria-label="Close menu">
          <svg class="icons" aria-hidden="true"><use href="/icons.svg#close"></use></svg>
        </button>
      </div>
    </nav>
  </div>

  <div class="heading"><a href="/"><img src="/img/fuzz_pop.svg?v=<?php echo filemtime(__DIR__ . '/img/fuzz_pop.svg'); ?>" alt="Fuzz Pop"></a></div>

  <div id="settings-menu-container">
    <button id="toggle-settings-panel-button" class="settings-toggle" type="button" title="Settings" aria-label="Settings" aria-controls="settings-panel" aria-expanded="false">
      <svg class="icons" aria-hidden="true"><use href="/icons.svg#settings"></use></svg>
    </button>
    <div id="settings-panel" class="settings-panel" role="region" aria-label="Settings" hidden>
      <div class="settings-panel-scroll">
        <button id="toggle-mode-button" class="toc-button button-on" type="button" title="Toggle dark mode" aria-pressed="true">
          <svg class="icons" aria-hidden="true"><use href="/icons.svg#moon"></use></svg><span>Dark mode</span>
        </button>
        <button id="toggle-fullscreen-button" class="toc-button" type="button" title="Toggle full screen" aria-pressed="false">
          <svg class="icons" aria-hidden="true"><use href="/icons.svg#full_screen"></use></svg><span>Fullscreen</span>
        </button>
        <button id="share-settings-button" class="toc-button" type="button" title="Share">
          <svg class="icons" aria-hidden="true"><use href="/icons.svg#share"></use></svg><span>Share</span>
        </button>
        <button id="reset-settings-button" class="toc-button" type="button" title="Reset to default">
          <svg class="icons" aria-hidden="true"><use href="/icons.svg#reset"></use></svg><span>Reset</span>
        </button>
        <p id="settings-feedback" role="status" hidden></p>
      </div>
      <div class="toc-close-wrap">
        <button id="toggle-settings-panel-close-button" class="settings-toggle" type="button" title="Close" aria-label="Close settings">
          <svg class="icons" aria-hidden="true"><use href="/icons.svg#close"></use></svg>
        </button>
      </div>
    </div>
  </div>
</header>
<main id="main">
