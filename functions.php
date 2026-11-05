<?php

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function loadArtists(): array
{
    return json_decode(file_get_contents(__DIR__ . '/data/artists.json'), true, 512, JSON_THROW_ON_ERROR);
}

function loadReleases(): array
{
    $releases = [];
    foreach (glob(__DIR__ . '/disco/*.txt') as $path) {
        $lines = array_map('trim', file($path, FILE_IGNORE_NEW_LINES));
        $slug = basename($path, '.txt');
        $releases[$slug] = [
            'slug' => $slug,
            'artist' => $lines[0],
            'artistUrl' => $lines[1],
            'title' => $lines[2],
            'catalogue' => $lines[3],
            'format' => $lines[4],
            'description' => $lines[5] ?? '',
            'tracks' => $lines[6] ?? '',
            'duration' => $lines[7] ?? '',
            'date' => $lines[8] ?? '',
            'spotify' => $lines[9] ?? '',
            'thumbnail' => is_file(__DIR__ . "/disco_photos_small/$slug.png") ? "/disco_photos_small/$slug.png" : '/img/lp.png',
            'cover' => is_file(__DIR__ . "/disco_photos/$slug.png") ? "/disco_photos/$slug.png" : '/img/lp.png',
        ];
    }
    return $releases;
}
