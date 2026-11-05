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
check(count($releases) === count(glob(__DIR__ . '/../disco/*.txt')), 'All original releases must be retained');
foreach ($releases as $release) {
    foreach (['thumbnail', 'cover'] as $field) {
        check(is_file(__DIR__ . '/..' . $release[$field]), "Missing $field for {$release['slug']}");
    }
}
$artists = loadArtists();
check(array_column($artists, 'name') === ['Epileptic Passion', 'MC Peko', 'Silentlab'], 'Retain the verified artists');
foreach ($artists as $artist) {
    check(in_array($artist['name'], array_column($releases, 'artist'), true), 'Every artist must have an existing release');
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
