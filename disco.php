<?php
require_once __DIR__ . '/functions.php';
$releases = loadReleases();
$slug = $_GET['a'] ?? '';
$release = is_string($slug) ? ($releases[$slug] ?? null) : null;
if ($release === null) {
    http_response_code(404);
    $pageTitle = 'Release not found';
} else {
    $pageTitle = $release['artist'] . ' - ' . $release['title'];
    $slugs = array_keys($releases);
    $index = array_search($slug, $slugs, true);
    $previous = $slugs[($index - 1 + count($slugs)) % count($slugs)];
    $next = $slugs[($index + 1) % count($slugs)];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require __DIR__ . '/head.php'; ?>
</head>
<body>
  <?php require __DIR__ . '/toc.php'; ?>
  <?php if ($release === null): ?>
  <section class="standard colored prose"><h1>Release not found</h1><p><a href="/releases">Show releases</a></p></section>
  <?php else: ?>
  <?php require __DIR__ . '/releases_toc.php'; ?>
  <nav class="release-navigation" aria-label="Browse releases">
    <a href="/disco?a=<?php echo escapeHtml($previous); ?>" title="Previous release" aria-label="Previous release"><svg class="icons" aria-hidden="true"><use href="/icons.svg#skip_left"></use></svg></a>
    <a href="/disco?a=<?php echo escapeHtml($next); ?>" title="Next release" aria-label="Next release"><svg class="icons" aria-hidden="true"><use href="/icons.svg#skip_right"></use></svg></a>
  </nav>
  <article class="release-page colored">
    <div class="entry-text">
      <h1><?php echo escapeHtml($release['title']); ?></h1>
      <p><?php if ($release['artistUrl'] !== ''): ?><a href="<?php echo escapeHtml($release['artistUrl']); ?>"><?php endif; ?><?php echo escapeHtml($release['artist']); ?><?php if ($release['artistUrl'] !== ''): ?></a><?php endif; ?></p>
    </div>
    <?php if ($release['spotify'] !== ''): ?><a class="release-cover" href="<?php echo escapeHtml($release['spotify']); ?>" title="Spotify"><?php endif; ?>
    <img class="full" src="<?php echo escapeHtml($release['cover']); ?>" alt="<?php echo escapeHtml($release['title']); ?>">
    <?php if ($release['spotify'] !== ''): ?></a><?php endif; ?>
    <div class="entry-text">
      <dl>
        <?php foreach (['catalogue' => ['Catalogue number', 'hash'], 'format' => ['Format', 'tag'], 'description' => ['Description', 'text'], 'tracks' => ['Tracks', 'hash'], 'duration' => ['Duration', 'clock'], 'date' => ['Release date', 'calendar']] as $field => [$label, $icon]): if ($release[$field] === '') { continue; } ?>
        <div><dt><svg class="entry-metadata-icon" aria-hidden="true"><use href="/icons.svg#<?php echo $icon; ?>"></use></svg><?php echo $label; ?></dt><dd><?php echo escapeHtml($release[$field]); ?></dd></div>
        <?php endforeach; ?>
      </dl>
      <?php if (is_file(__DIR__ . "/tracklists/$slug.txt")): ?>
      <h2>Tracklist</h2>
      <ol class="tracklist">
        <?php foreach (file(__DIR__ . "/tracklists/$slug.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $track): ?>
        <li><?php echo escapeHtml($track); ?></li>
        <?php endforeach; ?>
      </ol>
      <?php endif; ?>
    </div>
  </article>
  <?php endif; ?>
  <?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
