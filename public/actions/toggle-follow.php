<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
Auth::requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('artists.php');
}
verify_csrf();

$artistId = post_int('artist_id');
if ($artistId < 1 || !CatalogRepository::artist($artistId)) {
    flash('error', 'Artist not found.');
    redirect('artists.php');
}

$following = CatalogRepository::toggleFollow($artistId, Auth::id());
flash('success', $following ? 'You are now following this artist.' : 'Artist removed from your follows.');
redirect('artist.php?id=' . $artistId);

