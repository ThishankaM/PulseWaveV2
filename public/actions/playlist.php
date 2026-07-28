<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('library.php');
}
verify_csrf();

$operation = (string) ($_POST['operation'] ?? '');
$playlistId = post_int('playlist_id');
$trackId = post_int('track_id');
$name = trim((string) ($_POST['name'] ?? ''));
$userId = Auth::id();

switch ($operation) {
    case 'create':
        if ($name === '' || mb_strlen($name) > 80) {
            flash('error', 'Playlist name must contain between 1 and 80 characters.');
            redirect('library.php');
        }
        $newId = LibraryRepository::createPlaylist($userId, $name);
        flash('success', 'Playlist created.');
        redirect('playlist.php?id=' . $newId);

    case 'rename':
        if ($playlistId < 1 || $name === '' || mb_strlen($name) > 80) {
            flash('error', 'Enter a valid playlist name.');
            redirect('library.php');
        }
        $updated = LibraryRepository::renamePlaylist($playlistId, $userId, $name);
        flash($updated ? 'success' : 'error', $updated ? 'Playlist renamed.' : 'Playlist could not be updated.');
        redirect('playlist.php?id=' . $playlistId);

    case 'delete':
        $deleted = $playlistId > 0 && LibraryRepository::deletePlaylist($playlistId, $userId);
        flash($deleted ? 'success' : 'error', $deleted ? 'Playlist deleted.' : 'Playlist could not be deleted.');
        redirect('library.php');

    case 'add':
        $added = $playlistId > 0 && $trackId > 0 && LibraryRepository::addTrack($playlistId, $trackId, $userId);
        flash($added ? 'success' : 'warning', $added ? 'Track added to playlist.' : 'Track is already present or the playlist is unavailable.');
        redirect('playlist.php?id=' . $playlistId);

    case 'remove':
        $removed = $playlistId > 0 && $trackId > 0 && LibraryRepository::removeTrack($playlistId, $trackId, $userId);
        flash($removed ? 'success' : 'error', $removed ? 'Track removed from playlist.' : 'Track could not be removed.');
        redirect('playlist.php?id=' . $playlistId);

    default:
        flash('error', 'Invalid playlist request.');
        redirect('library.php');
}

