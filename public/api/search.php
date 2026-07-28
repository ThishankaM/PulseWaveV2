<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$query = trim((string) ($_GET['q'] ?? ''));
$results = CatalogRepository::search($query);
foreach ($results['tracks'] as &$track) {
    $track['url'] = url('artist.php?id=' . (int) $track['artist_id']);
    $track['cover_url'] = media_url($track['cover_path']);
}
unset($track);
foreach ($results['artists'] as &$artist) {
    $artist['url'] = url('artist.php?id=' . (int) $artist['id']);
    $artist['image_url'] = media_url($artist['image_path']);
}
unset($artist);
foreach ($results['genres'] as &$genre) {
    $genre['url'] = url('explore.php?genre=' . (int) $genre['id']);
}
unset($genre);

json_response(['query' => $query, 'results' => $results]);

