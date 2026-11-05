<?php $pageTitle = 'Artists'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require __DIR__ . '/head.php'; ?>
</head>
<body>
  <?php require __DIR__ . '/toc.php'; ?>
  <div class="top-line"><svg class="icons" aria-hidden="true"><use href="/icons.svg#person"></use></svg><h1>Artists</h1></div>
  <div class="artist-grid">
    <?php foreach (loadArtists() as $artist): ?>
    <article class="artist-card colored">
      <h2><?php echo escapeHtml($artist['name']); ?></h2>
      <?php if ($artist['image'] !== null): ?>
      <a class="artist-logo" href="<?php echo escapeHtml($artist['url']); ?>" title="<?php echo escapeHtml($artist['name']); ?>"><img src="<?php echo escapeHtml($artist['image']); ?>" alt="<?php echo escapeHtml($artist['name']); ?>"></a>
      <?php endif; ?>
      <a href="/releases?artist=<?php echo rawurlencode($artist['name']); ?>">Releases</a>
    </article>
    <?php endforeach; ?>
  </div>
  <?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
