<?php
require_once __DIR__ . '/functions.php';
$releaseView = $releaseView ?? ($_GET['view'] ?? 'grid');
$artistFilter = $_GET['artist'] ?? '';
if (!is_string($releaseView) || !in_array($releaseView, ['grid', 'details', 'table'], true) || !is_string($artistFilter)) {
    http_response_code(400);
    $pageTitle = 'Invalid release view';
} else {
    $pageTitle = 'Releases';
}
$releases = loadReleases();
if ($artistFilter !== '' && is_string($artistFilter)) {
    $releases = array_filter($releases, fn(array $release): bool => $release['artist'] === $artistFilter);
}
if ($releaseView === 'details') {
    uasort($releases, function (array $a, array $b): int {
        $dateKey = fn(string $date): string => implode('-', array_reverse(explode('-', $date)));
        return strcmp($dateKey($b['date']), $dateKey($a['date']));
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require __DIR__ . '/head.php'; ?>
  <?php if ($releaseView === 'table'): ?><script src="/sort_table.js" defer></script><?php endif; ?>
</head>
<body>
  <?php require __DIR__ . '/toc.php'; ?>
  <?php if (http_response_code() === 400): ?>
  <section class="standard colored prose"><h1>Invalid release view</h1><p><a href="/releases">Show releases</a></p></section>
  <?php else: ?>
  <?php require __DIR__ . '/releases_toc.php'; ?>
  <div class="release-summary">
    <p class="page-summary"><?php echo count($releases); ?> releases<?php if ($artistFilter !== ''): ?> by <?php echo escapeHtml($artistFilter); ?>. <a href="/releases">Show all releases</a><?php endif; ?></p>
    <nav class="view-links" aria-label="Release views">
      <?php foreach (['grid' => 'Grid', 'details' => 'Details', 'table' => 'Table'] as $view => $label):
          $query = ['view' => $view];
          if ($artistFilter !== '') { $query['artist'] = $artistFilter; }
      ?>
      <a href="/releases?<?php echo escapeHtml(http_build_query($query, '', '&', PHP_QUERY_RFC3986)); ?>" title="<?php echo $label; ?>" aria-label="<?php echo $label; ?>"<?php if (($releaseView ?? '') === $view): ?> aria-current="page"<?php endif; ?>><svg class="icons" aria-hidden="true"><use href="/icons.svg#<?php echo 'view_' . ($view === 'details' ? 'list' : $view); ?>"></use></svg></a>
      <?php endforeach; ?>
    </nav>
  </div>
  <?php if ($releaseView === 'table'): ?>
  <div class="table-scroll">
    <table class="sortable release-table">
      <thead><tr><th>Cover</th><th>Artist</th><th>Title</th><th>Catalogue number</th><th>Format</th><th>Release date</th><th>Spotify</th></tr></thead>
      <tbody>
      <?php foreach ($releases as $release): ?>
      <tr>
        <td><a href="/disco?a=<?php echo escapeHtml($release['slug']); ?>"><img class="table-cover" src="<?php echo escapeHtml($release['thumbnail']); ?>" alt="<?php echo escapeHtml($release['title']); ?>"></a></td>
        <td><?php echo escapeHtml($release['artist']); ?></td>
        <td><a href="/disco?a=<?php echo escapeHtml($release['slug']); ?>"><?php echo escapeHtml($release['title']); ?></a></td>
        <td><?php echo escapeHtml($release['catalogue']); ?></td>
        <td><?php echo escapeHtml($release['format']); ?></td>
        <td><?php echo escapeHtml($release['date']); ?></td>
        <td><?php if ($release['spotify'] !== ''): ?><a href="<?php echo escapeHtml($release['spotify']); ?>" title="Spotify" aria-label="Spotify"><svg class="icons" aria-hidden="true"><use href="/icons.svg#spotify"></use></svg></a><?php endif; ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="release-grid<?php if ($releaseView === 'details'): ?> release-details<?php endif; ?>">
    <?php foreach ($releases as $release): ?>
    <article class="release-card colored">
      <a class="release-cover" href="/disco?a=<?php echo escapeHtml($release['slug']); ?>"><picture><source media="(max-width: 799px)" srcset="<?php echo escapeHtml($release['cover']); ?>"><img src="<?php echo escapeHtml($release['thumbnail']); ?>" alt="<?php echo escapeHtml($release['title']); ?>"></picture></a>
      <div class="entry-text">
        <h2><a href="/disco?a=<?php echo escapeHtml($release['slug']); ?>"><?php echo escapeHtml($release['title']); ?></a></h2>
        <p><?php if ($release['artistUrl'] !== ''): ?><a href="<?php echo escapeHtml($release['artistUrl']); ?>"><?php endif; ?><?php echo escapeHtml($release['artist']); ?><?php if ($release['artistUrl'] !== ''): ?></a><?php endif; ?></p>
        <?php if ($releaseView === 'details'): ?>
        <dl>
          <?php foreach (['catalogue' => 'Catalogue number', 'format' => 'Format', 'duration' => 'Duration', 'date' => 'Release date'] as $field => $label): if ($release[$field] === '') { continue; } ?>
          <div><dt><?php echo $label; ?></dt><dd><?php echo escapeHtml($release[$field]); ?></dd></div>
          <?php endforeach; ?>
        </dl>
        <?php endif; ?>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php if ($releases === []): ?><p class="prose">No releases found.</p><?php endif; ?>
  <?php endif; ?>
  <?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
