<?php
require_once __DIR__ . '/functions.php';
$canonicalPath = preg_replace('~\.php(?=\?|$)~', '', $_SERVER['REQUEST_URI'] ?? '/');
$canonicalPath = preg_replace('~^/index(?=\?|$)~', '/', $canonicalPath);
$canonicalUrl = $canonicalUrl ?? ('https://fuzzpop.no' . $canonicalPath);
$pageTitle = isset($pageTitle) ? 'Fuzz Pop - ' . $pageTitle : 'Fuzz Pop';
$ogImageSize = getimagesize(__DIR__ . '/img/logo.png');
?>
<title><?php echo escapeHtml($pageTitle); ?></title>
<meta charset="utf-8">
<link rel="icon" href="/favicon.svg?v=<?php echo filemtime(__DIR__ . '/favicon.svg'); ?>" type="image/svg+xml">
<link rel="stylesheet" href="/style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
<script src="/index.js?v=<?php echo filemtime(__DIR__ . '/index.js'); ?>" defer></script>
<script src="/swipe.js?v=<?php echo filemtime(__DIR__ . '/swipe.js'); ?>" defer></script>
<meta property="og:image" content="https://fuzzpop.no/img/logo.png">
<meta property="og:image:type" content="image/png">
<?php if ($ogImageSize !== false): ?>
<meta property="og:image:width" content="<?php echo $ogImageSize[0]; ?>">
<meta property="og:image:height" content="<?php echo $ogImageSize[1]; ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:description" content="This is the official website for the record label Fuzz Pop.">
<meta property="og:title" content="<?php echo escapeHtml($pageTitle); ?>">
<meta property="og:url" content="<?php echo escapeHtml($canonicalUrl); ?>">
<meta name="keywords" content="Fuzz Pop, record label, record labels">
<meta name="description" content="This is the official website for the record label Fuzz Pop.">
<link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">  
