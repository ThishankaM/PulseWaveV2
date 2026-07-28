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
flash('success', $liked ? 'Added to liked songs.' : 'Removed from liked songs.');
redirect_back();

