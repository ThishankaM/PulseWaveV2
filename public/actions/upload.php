<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
Auth::requireRole(['artist', 'admin']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('upload.php');
}
verify_csrf();

$title = trim((string) ($_POST['title'] ?? ''));
$artistId = post_int('artist_id');
$genreId = post_int('genre_id');
$releaseYear = post_int('release_year', (int) date('Y'));

if ($title === '' || mb_strlen($title) > 150) {
    flash('error', 'Track title must contain between 1 and 150 characters.');
    redirect('upload.php');
}
if ($releaseYear < 1900 || $releaseYear > (int) date('Y') + 1) {
    flash('error', 'Enter a valid release year.');
    redirect('upload.php');
}

$artistCheck = db()->prepare('SELECT 1 FROM artists WHERE id = ?');
$artistCheck->execute([$artistId]);
$genreCheck = db()->prepare('SELECT 1 FROM genres WHERE id = ?');
$genreCheck->execute([$genreId]);
if (!$artistCheck->fetchColumn() || !$genreCheck->fetchColumn()) {
    flash('error', 'Choose a valid artist and genre.');
    redirect('upload.php');
}

function save_upload(array $file, string $type): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException($type === 'audio' ? 'An audio file is required.' : 'Artwork upload failed.');
    }

    $maximumMb = (int) env($type === 'audio' ? 'MAX_AUDIO_SIZE_MB' : 'MAX_IMAGE_SIZE_MB', $type === 'audio' ? 50 : 5);
    if ((int) $file['size'] > $maximumMb * 1024 * 1024) {
        throw new RuntimeException(ucfirst($type) . " file must be smaller than {$maximumMb} MB.");
    }

    $extension = mb_strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']) ?: '';
    $allowed = $type === 'audio'
        ? [
            'mp3' => ['audio/mpeg', 'audio/mp3'],
            'wav' => ['audio/wav', 'audio/x-wav', 'audio/wave'],
            'flac' => ['audio/flac', 'audio/x-flac', 'application/octet-stream'],
            'ogg' => ['audio/ogg', 'application/ogg'],
            'opus' => ['audio/opus', 'audio/ogg', 'application/ogg', 'application/octet-stream'],
        ]
        : [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'webp' => ['image/webp'],
        ];

    if (!isset($allowed[$extension]) || !in_array($mime, $allowed[$extension], true)) {
        throw new RuntimeException('Unsupported or invalid ' . $type . ' file.');
    }

    $directory = PUBLIC_PATH . '/uploads/' . ($type === 'audio' ? 'audio' : 'album_art');
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Upload directory could not be created.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . ($extension === 'jpeg' ? 'jpg' : $extension);
    if (!move_uploaded_file((string) $file['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('Uploaded file could not be stored.');
    }
    return 'uploads/' . ($type === 'audio' ? 'audio' : 'album_art') . '/' . $filename;
}

$audioPath = '';
$coverPath = 'uploads/album_art/default-album-art.jpg';
try {
    $audioPath = save_upload($_FILES['audio_file'] ?? [], 'audio');
    if (isset($_FILES['cover_file']) && ($_FILES['cover_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $coverPath = save_upload($_FILES['cover_file'], 'image');
    }

    $connection = db();
    $connection->beginTransaction();
    $track = $connection->prepare(
        'INSERT INTO tracks (artist_id, uploaded_by, title, audio_path, cover_path, release_year)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $track->execute([$artistId, Auth::id(), $title, $audioPath, $coverPath, $releaseYear]);
    $trackId = (int) $connection->lastInsertId();
    $genre = $connection->prepare('INSERT INTO track_genres (track_id, genre_id) VALUES (?, ?)');
    $genre->execute([$trackId, $genreId]);
    $connection->commit();

    flash('success', 'Track uploaded and added to the catalog.');
    redirect('dashboard.php');
} catch (Throwable $exception) {
    if (isset($connection) && $connection->inTransaction()) {
        $connection->rollBack();
    }
    foreach ([$audioPath, $coverPath] as $storedPath) {
        if ($storedPath !== '' && $storedPath !== 'uploads/album_art/default-album-art.jpg') {
            $fullPath = PUBLIC_PATH . '/' . $storedPath;
            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }
    }
    flash('error', $exception instanceof RuntimeException ? $exception->getMessage() : 'Upload could not be completed.');
    redirect('upload.php');
}

