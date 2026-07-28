<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
Auth::requireRole(['artist', 'admin']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}
verify_csrf();

$trackId = post_int('track_id');
$statement = db()->prepare('SELECT id, uploaded_by FROM tracks WHERE id = ? LIMIT 1');
$statement->execute([$trackId]);
$track = $statement->fetch();
if (!$track) {
    flash('error', 'Track not found.');
    redirect('dashboard.php');
}

if (Auth::role() !== 'admin' && (int) $track['uploaded_by'] !== Auth::id()) {
    flash('error', 'You can only remove tracks uploaded by your account.');
    redirect('dashboard.php');
}

$delete = db()->prepare('DELETE FROM tracks WHERE id = ?');
$delete->execute([$trackId]);
flash('success', 'Track removed from the catalog. Media files were retained for recovery.');
redirect('dashboard.php');

