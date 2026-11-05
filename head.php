<?php
$canonicalPath = preg_replace('~\.php(?=\?|$)~', '', $_SERVER['REQUEST_URI'] ?? '/');
$canonicalPath = preg_replace('~^/index(?=\?|$)~', '/', $canonicalPath);
$canonicalUrl = 'https://fuzzpop.no' . $canonicalPath;
?>
<title>Fuzz Pop</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="icon" href="/favicon.svg?v=<?php echo filemtime(__DIR__ . '/favicon.svg'); ?>" type="image/svg+xml">
<link rel="stylesheet" type="text/css" href="style.css">
<meta property="og:image" content="img/logo.png">
<meta name="keywords" content="Fuzz Pop, record label, record labels">
<meta name="description" content="This is the official website for the record label Fuzz Pop.">
<link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">