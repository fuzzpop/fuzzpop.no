<div class="top-line">
  <svg class="icons" aria-hidden="true"><use href="/icons.svg#lp"></use></svg>
  <h1>Releases</h1>
  <nav class="view-links" aria-label="Release views">
    <?php foreach (['grid' => 'Grid', 'details' => 'Details', 'table' => 'Table'] as $view => $label):
        $query = ['view' => $view];
        if (isset($artistFilter) && $artistFilter !== '') { $query['artist'] = $artistFilter; }
    ?>
    <a href="/releases?<?php echo escapeHtml(http_build_query($query, '', '&', PHP_QUERY_RFC3986)); ?>" title="<?php echo $label; ?>" aria-label="<?php echo $label; ?>"<?php if (($releaseView ?? '') === $view): ?> aria-current="page"<?php endif; ?>><svg class="icons" aria-hidden="true"><use href="/icons.svg#<?php echo 'view_' . ($view === 'details' ? 'list' : $view); ?>"></use></svg></a>
    <?php endforeach; ?>
  </nav>
</div>
