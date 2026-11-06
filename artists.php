<?php
$pageTitle = 'Artists';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require __DIR__ . '/head.php'; ?>
</head>
<body>
  <?php require __DIR__ . '/toc.php'; ?>
  <?php
  $artists = loadArtists();
  $releaseArtists = array_column(loadReleases(), 'artist');
  ?>
  <div class="top-line"><svg class="icons" aria-hidden="true"><use href="/icons.svg#person"></use></svg><h1>Artists</h1></div>
  <div class="artist-grid">
    <?php foreach ($artists as $artist): ?>
    <?php $logoUrl = $artist['image'] === null ? null : $artist['image'] . '?v=' . filemtime(__DIR__ . $artist['image']); ?>
    <article class="artist-card colored">
      <?php if ($artist['image'] !== null): ?>
      <?php if ($artist['url'] !== null): ?>
      <a class="artist-logo" href="<?php echo escapeHtml($artist['url']); ?>" title="<?php echo escapeHtml($artist['name']); ?>"><img src="<?php echo escapeHtml($logoUrl); ?>" alt="<?php echo escapeHtml($artist['name']); ?>"></a>
      <?php else: ?>
      <div class="artist-logo"><img src="<?php echo escapeHtml($logoUrl); ?>" alt=""></div>
      <?php endif; ?>
      <?php endif; ?>
      <?php if (in_array($artist['name'], $releaseArtists, true)): ?>
      <a href="/releases?artist=<?php echo rawurlencode($artist['name']); ?>">Releases</a>
      <?php endif; ?>
    </article>
    <?php endforeach; ?>
  </div>
  <?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
