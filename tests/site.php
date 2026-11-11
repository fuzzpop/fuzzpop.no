<?php
require __DIR__ . '/../functions.php';

$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}

$releases = loadReleases();
$style = file_get_contents(__DIR__ . '/../style.css');
check(count($releases) === count(glob(__DIR__ . '/../disco/*.txt')), 'All original releases must be retained');
foreach ($releases as $release) {
    foreach (['thumbnail', 'cover'] as $field) {
        check(is_file(__DIR__ . '/..' . $release[$field]), "Missing $field for {$release['slug']}");
    }
}
$artists = loadArtists();
check(array_column($artists, 'name') === ['Arctica / Antarctica', 'Epileptic Passion', 'MC Peko', 'Silentlab'], 'Retain the verified artists');
check($artists[0]['url'] === 'https://arcticaantarctica.com' && $artists[0]['soundcloud'] === 'https://soundcloud.com/mc-peko/sets/arctica-antarctica' && $artists[0]['image'] === '/img/arctica_antarctica.svg', 'Use the A/A website, SoundCloud and vector logo');
check($artists[1]['image'] === '/img/epileptic_passion.svg' && $artists[1]['url'] === null, 'Epileptic Passion logo has no invented website');
check($artists[2]['image'] === '/img/mc_peko.svg' && $artists[2]['url'] === 'https://peko.net', 'Use the MC Peko vector logo and website');
check($artists[3]['image'] === '/img/silentlab.svg' && $artists[3]['url'] === 'https://silentlab.net', 'Use the white Silentlab logo');
foreach ($artists as $artist) {
    if ($artist['image'] !== null) {
        check(is_file(__DIR__ . '/..' . $artist['image']), "Missing artist logo: {$artist['name']}");
    }
}
foreach ($releases as $release) {
    check(in_array($release['artist'], array_column($artists, 'name'), true), 'Every release must have an artist record');
}
check($releases['meant_to_be_shared']['description'] === '5" jewel case', 'Retain packaging');
check($releases['meant_to_be_shared']['spotify'] === '', 'Missing optional Spotify field must remain empty');

if (($argv[1] ?? '') === '--http') {
    $base = rtrim($argv[2], '/');
    function response(string $path): array
    {
        global $base;
        $context = stream_context_create(['http' => ['ignore_errors' => true, 'follow_location' => false, 'timeout' => 15]]);
        $body = file_get_contents($base . $path, false, $context);
        check($body !== false, "Request failed: $path");
        $headers = http_get_last_response_headers();
        preg_match('~HTTP/\S+ (\d+)~', $headers[0], $status);
        return [(int) $status[1], $body, $headers];
    }
    $sprite = file_get_contents(__DIR__ . '/../icons.svg');
    foreach (['/', '/releases', '/releases?view=details', '/releases?view=table', '/artists', '/about', '/disco_grid', '/disco_details', '/disco_table'] as $path) {
        [$status, $html] = response($path);
        check($status === 200, "Expected HTTP 200: $path");
        check(!preg_match('~(?:Warning|Fatal error|Deprecated):~', $html), "PHP diagnostics on $path");
        preg_match('~<nav id="toc".*?</nav>~s', $html, $nav);
        preg_match_all('~<a href="([^"]+)"[^>]*>.*?</svg>([^<]+)</a>~s', $nav[0], $links, PREG_SET_ORDER);
        check(array_column($links, 1) === ['/', '/releases', '/artists', '/about'], "TOC destinations: $path");
        check(array_column($links, 2) === ['Home', 'Releases', 'Artists', 'About'], "TOC order: $path");
        check(substr_count($html, '<main ') === 1 && substr_count($html, '</main>') === 1, "Main landmark: $path");
        if (in_array($path, ['/releases', '/artists', '/about'], true)) {
            preg_match('~<div class="top-line">(.*?)</div>~s', $html, $heading);
            $headingContent = $heading[1] ?? '';
            preg_match('~<h1>([^<]+)</h1>~', $headingContent, $title);
            preg_match_all('~<use href="/icons\.svg#([^"]+)"></use>~', $headingContent, $sideIcons);
            check(isset($title[1]) && count($sideIcons[1]) === 2 && $sideIcons[1][0] === $sideIcons[1][1], "Centered heading with matching side icons: $path");
            check((bool) preg_match('~\.top-line h1\s*\{\s*flex:\s*1;\s*text-align:\s*center;~', $style), "Center heading text: $path");
        }
        if ($path === '/releases') {
            check((bool) preg_match('~<div class="release-summary">\s*<p class="page-summary">.*?</p>\s*<nav class="view-links"~s', $html), 'Release count and view controls share a container');
            check(substr_count($html, '<nav class="view-links"') === 1 && substr_count($html, '<a href="/releases?view=') === 3, 'Render exactly three release view controls');
        }
        preg_match_all('~icons\.svg#([^"]+)~', $html, $icons);
        foreach (array_unique($icons[1]) as $id) {
            check(str_contains($sprite, 'id="' . $id . '"'), "Unknown rendered icon $id on $path");
        }
        preg_match('~<footer>.*?</footer>~s', $html, $footer);
        preg_match_all('~<a\b([^>]*)>(.*?)</a>~s', $footer[0], $footerLinks, PREG_SET_ORDER);
        foreach ($footerLinks as $link) {
            check((bool) preg_match('~\btitle=~', $link[1]) === (bool) preg_match('~<(?:img|svg)\b~', $link[2]), "Footer tooltip policy: $path");
        }
        foreach (['/style.css', '/index.js'] as $asset) {
            check(str_contains($html, $asset . '?v='), "Versioned asset $asset on $path");
        }
    }
    foreach ($releases as $slug => $release) {
        [$status, $html] = response('/disco?a=' . rawurlencode($slug));
        check($status === 200, "Release failed: $slug");
        check(!preg_match('~(?:Warning|Fatal error|Deprecated):~', $html), "Release diagnostics: $slug");
        check(str_contains($html, escapeHtml($release['title'])), "Missing release title: $slug");
    }
    foreach ($artists as $artist) {
        [, $html] = response('/releases?artist=' . rawurlencode($artist['name']));
        check(substr_count($html, '<article class="release-card') === count(array_filter($releases, fn($release) => $release['artist'] === $artist['name'])), 'Artist filter mismatch');
    }
    [, $artistsPage] = response('/artists');
    check(!preg_match('~<h2\b~', $artistsPage), 'Do not render artist names as text headings');
    check((bool) preg_match('~<a class="artist-logo"[^>]*href="https://arcticaantarctica\.com"[^>]*><img src="/img/arctica_antarctica\.svg\?v=\d+" alt="Arctica / Antarctica">~', $artistsPage), 'Link the A/A vector logo to its website');
    check((bool) preg_match('~<a class="artist-link" href="https://soundcloud\.com/mc-peko/sets/arctica-antarctica" title="SoundCloud"><svg class="icons" aria-hidden="true"><use href="/icons\.svg#soundcloud"></use></svg>SoundCloud</a>~', $artistsPage), 'Link A/A to its SoundCloud set');
    check(!str_contains($artistsPage, '<a href="/releases?artist=Arctica%20%2F%20Antarctica">Releases</a>'), 'Do not show an empty A/A releases link');
    [$status, $logo] = response('/img/arctica_antarctica.svg');
    check($status === 200 && str_contains($logo, '<svg ') && str_contains($logo, 'fill="#fff"') && !str_contains($logo, '<image') && !str_contains($logo, 'gradient'), 'Serve the white transparent A/A vector logo');
    check(str_contains($logo, 'a342 342 0 1 1 0 684') && str_contains($logo, 'M231 127Q384 212 541 127'), 'Fill the logo ring and both polar regions');
    check(str_contains($logo, 'rotate(180 384 384)') && str_contains($logo, 'M141 279h47l18 48h84l17-48h44'), 'Invert the first A and upper wordmark');
    check((bool) preg_match('~<a class="artist-logo"[^>]*href="https://peko\.net"[^>]*><img src="/img/mc_peko\.svg\?v=\d+" alt="MC Peko">~', $artistsPage), 'Render the linked MC Peko vector logo');
    check((bool) preg_match('~<a class="artist-logo"[^>]*href="https://silentlab\.net"[^>]*><img src="/img/silentlab\.svg\?v=\d+" alt="Silentlab">~', $artistsPage), 'Link the Silentlab logo to its website');
    [$status, $logo] = response('/img/mc_peko.svg');
    check($status === 200 && str_contains($logo, 'viewBox="0 0 637.9865 120.2752"') && str_contains($logo, 'fill: #fff'), 'Serve the supplied MC Peko landscape SVG');
    check((bool) preg_match('~<div class="artist-logo"><img src="/img/epileptic_passion\.svg\?v=\d+" alt=""></div>~', $artistsPage), 'Render the Epileptic Passion logo without a link');
    check((bool) preg_match('~<a class="artist-logo"[^>]*><img src="/img/silentlab\.svg\?v=\d+" alt="Silentlab">~', $artistsPage), 'Render the white Silentlab logo');
    [$status, $logo] = response('/img/epileptic_passion.svg');
    check($status === 200 && str_contains($logo, '<svg ') && str_contains($logo, 'fill="#fff"'), 'Serve the white Epileptic Passion SVG');
    [$status, $logo] = response('/img/silentlab.svg');
    check($status === 200 && str_contains($logo, '<path fill="#fff"') && str_contains($logo, 'fill-rule="evenodd"') && !str_contains($logo, 'data:image/'), 'Serve the vector white Silentlab SVG');
    foreach (['releases', 'artists', 'about', 'disco', 'disco_grid', 'disco_table', 'disco_details'] as $page) {
        foreach (['.php', '/'] as $suffix) {
            [$status, , $headers] = response("/$page$suffix?keep=1%202");
            check($status === 301, "Permanent redirect: $page$suffix");
            $locations = array_values(array_filter($headers, fn($header) => str_starts_with(strtolower($header), 'location:')));
            $location = isset($locations[0]) ? trim(substr($locations[0], 9)) : '';
            $expectedLocation = "/$page?keep=1%202";
            check($location === $expectedLocation || $location === $base . $expectedLocation, "Preserve redirect query: $page$suffix");
        }
    }
    foreach (['/disco?a=missing', '/disco?a[]=bad', '/missing-fuzz-pop-page'] as $path) {
        check(response($path)[0] === 404, "Expected HTTP 404: $path");
    }
    foreach (['/functions.php', '/releases_toc.php', '/data/artists.json', '/tests/site.php'] as $path) {
        check(in_array(response($path)[0], [403, 404], true), "Private resource must be inaccessible: $path");
    }
    foreach (['/releases?view=bad', '/releases?view[]=grid', '/releases?artist[]=bad'] as $path) {
        [$status, $html] = response($path);
        check($status === 400 && !preg_match('~(?:Warning|Fatal error):~', $html), "Expected clean HTTP 400: $path");
    }
    foreach (['/releases', '/artists', '/about'] as $path) {
        [, $html] = response($path);
        check(str_contains($html, '<link rel="canonical" href="https://fuzzpop.no' . $path . '">'), "Production canonical: $path");
    }
}
echo "$checks checks passed.\n";
