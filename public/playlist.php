<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireLogin();

$playlistId = request_int('id');
$playlist = $playlistId > 0 ? LibraryRepository::playlist($playlistId, Auth::id()) : null;
if (!$playlist) {
    flash('error', 'That playlist does not exist or does not belong to you.');
    redirect('library.php');
}

$tracks = LibraryRepository::playlistTracks($playlistId, Auth::id());
$trackIds = implode(',', array_column($tracks, 'id'));
$availableTracks = LibraryRepository::availableTracks($playlistId, Auth::id());
$pageTitle = $playlist['name'];
$activeNav = 'library';
require APP_PATH . '/Views/partials/header-v3.php';
?>

<section class="page-hero section-shell playlist-hero">
    <div class="playlist-cover-stack">
        <?php foreach (array_slice($tracks, 0, 4) as $track): ?>
            <img src="<?= e(media_url($track['cover_path'])) ?>" alt="">
        <?php endforeach; ?>
        <?php if (!$tracks): ?><span>♫</span><?php endif; ?>
    </div>
    <div>
        <span class="eyebrow">Playlist</span>
        <h1><?= e($playlist['name']) ?></h1>
        <p><?= count($tracks) ?> tracks · Created <?= e(date('M Y', strtotime($playlist['created_at']))) ?></p>
        <div class="hero-actions" data-play-collection="<?= e($trackIds) ?>" data-queue-label="<?= e($playlist['name']) ?>">
            <?php if ($tracks): ?>
                <button class="button button-primary" type="button" data-play-track="<?= (int) $tracks[0]['id'] ?>">Play playlist</button>
            <?php endif; ?>
            <button class="button button-ghost" type="button" data-dialog-open="edit-playlist">Edit</button>
        </div>
    </div>
</section>

<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow">In this playlist</span>
            <h2>Tracks</h2>
        </div>
    </div>
    <?php if ($tracks): ?>
        <div class="playlist-track-list" data-play-collection="<?= e($trackIds) ?>" data-queue-label="<?= e($playlist['name']) ?>">
            <?php foreach ($tracks as $index => $track): ?>
                <div class="playlist-track-row">
                    <button type="button" class="row-play" data-play-track="<?= (int) $track['id'] ?>" aria-label="Play <?= e($track['title']) ?>"><?= icon('play') ?></button>
                    <span class="track-number"><?= $index + 1 ?></span>
                    <img src="<?= e(media_url($track['cover_path'])) ?>" alt="">
                    <div>
                        <strong><?= e($track['title']) ?></strong>
                        <a href="<?= e(url('artist.php?id=' . (int) $track['artist_id'])) ?>"><?= e($track['artist_name']) ?></a>
                    </div>
                    <span><?= format_count($track['play_count']) ?> plays</span>
                    <form method="post" action="<?= e(url('actions/playlist.php')) ?>">
                        <?= csrf_input() ?>
                        <input type="hidden" name="operation" value="remove">
                        <input type="hidden" name="playlist_id" value="<?= $playlistId ?>">
                        <input type="hidden" name="track_id" value="<?= (int) $track['id'] ?>">
                        <button class="icon-button" type="submit" aria-label="Remove <?= e($track['title']) ?>"><?= icon('close') ?></button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-panel"><p>This playlist is empty. Add a track below.</p></div>
    <?php endif; ?>
</section>

<?php if ($availableTracks): ?>
<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow">Grow the collection</span>
            <h2>Add a track</h2>
        </div>
    </div>
    <form class="add-track-form" method="post" action="<?= e(url('actions/playlist.php')) ?>">
        <?= csrf_input() ?>
        <input type="hidden" name="operation" value="add">
        <input type="hidden" name="playlist_id" value="<?= $playlistId ?>">
        <label class="track-picker-search">
            <span>Find a song</span>
            <input type="search" data-select-filter="playlist-track-select" placeholder="Artist or track">
        </label>
        <label>
            <span>Choose a track</span>
            <select id="playlist-track-select" name="track_id" required>
                <?php foreach ($availableTracks as $track): ?>
                    <option value="<?= (int) $track['id'] ?>"><?= e($track['artist_name'] . ' — ' . $track['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button button-primary" type="submit">Add to playlist</button>
    </form>
</section>
<?php endif; ?>

<dialog class="app-dialog" data-dialog="edit-playlist">
    <button class="dialog-close icon-button" type="button" data-dialog-close aria-label="Close"><?= icon('close') ?></button>
    <span class="eyebrow">Playlist settings</span>
    <h2>Edit playlist</h2>
    <form method="post" action="<?= e(url('actions/playlist.php')) ?>" class="stack-form">
        <?= csrf_input() ?>
        <input type="hidden" name="operation" value="rename">
        <input type="hidden" name="playlist_id" value="<?= $playlistId ?>">
        <label><span>Name</span><input type="text" name="name" value="<?= e($playlist['name']) ?>" maxlength="80" required></label>
        <button class="button button-primary" type="submit">Save name</button>
    </form>
    <form method="post" action="<?= e(url('actions/playlist.php')) ?>" class="danger-zone" data-confirm="Delete this playlist? This cannot be undone.">
        <?= csrf_input() ?>
        <input type="hidden" name="operation" value="delete">
        <input type="hidden" name="playlist_id" value="<?= $playlistId ?>">
        <button class="button button-danger" type="submit">Delete playlist</button>
    </form>
</dialog>

<?php require APP_PATH . '/Views/partials/footer-v3.php'; ?>

