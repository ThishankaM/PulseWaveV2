<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
Auth::requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
verify_csrf();

$trackId = post_int('track_id');
if ($trackId < 1 || !CatalogRepository::track($trackId)) {
    flash('error', 'Track not found.');
    redirect_back();
}

$liked = CatalogRepository::toggleLike($trackId, Auth::id());
$acceptsJson = str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
if ($acceptsJson) {
    $track = CatalogRepository::track($trackId);
    json_response([
        'liked' => $liked,
        'like_count' => (int) ($track['like_count'] ?? 0),
        'message' => $liked ? 'Added to liked songs.' : 'Removed from liked songs.',
    ]);
}
flash('success', $liked ? 'Added to liked songs.' : 'Removed from liked songs.');
redirect_back();

