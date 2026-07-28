<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$tracks = array_map(static function (array $track): array {
    return [
        'id' => (int) $track['id'],
        'artist_id' => (int) $track['artist_id'],
        'title' => (string) $track['title'],
        'artist' => (string) $track['artist_name'],
        'audio_url' => media_url($track['audio_path'], ''),
        'cover_url' => media_url($track['cover_path']),
    ];
}, CatalogRepository::allTracks());

json_response(['tracks' => $tracks]);

