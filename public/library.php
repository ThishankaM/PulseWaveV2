<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireLogin();

$playlists = LibraryRepository::playlists(Auth::id());
$likedTracks = LibraryRepository::likedTracks(Auth::id());
$pageTitle = 'Your Library';
$pageDescription = 'Your PulseWave playlists and liked music.';
$activeNav = 'library';
require APP_PATH . '/Views/partials/header.php';
?>

<section class="page-hero section-shell library-hero">
    <div>
        <span class="eyebrow">Made for <?= e(Auth::user()['name']) ?></span>
        <h1>Your Library</h1>
        <p>Keep playlists, favourites and personal discoveries together.</p>
    </div>
    <form class="inline-create" method="post" action="<?= e(url('actions/playlist.php')) ?>">
        <?= csrf_input() ?>
        <input type="hidden" name="operation" value="create">
        <label>
            <span class="sr-only">Playlist name</span>
            <input type="text" name="name" maxlength="80" placeholder="New playlist name" required>
        </label>
        <button class="button button-primary" type="submit">Create playlist</button>
    </form>
</section>

<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow"><?= count($playlists) ?> collections</span>
            <h2>Your playlists</h2>
        </div>
    </div>
    <?php if ($playlists): ?>
        <div class="playlist-grid">
            <?php foreach ($playlists as $playlist): ?>
                <a class="playlist-card" href="<?= e(url('playlist.php?id=' . (int) $playlist['id'])) ?>">
                    <img src="<?= e(media_url($playlist['cover_path'])) ?>" alt="" loading="lazy">
                    <div>
                        <h3><?= e($playlist['name']) ?></h3>
                        <p><?= (int) $playlist['track_count'] ?> tracks</p>
                    </div>
                    <span aria-hidden="true">↗</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-panel">
            <h3>Create your first playlist</h3>
            <p>Use the form above to start collecting tracks.</p>
        </div>
    <?php endif; ?>
</section>

<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow"><?= count($likedTracks) ?> favourites</span>
            <h2>Liked songs</h2>
        </div>
    </div>
    <?php if ($likedTracks): ?>
        <div class="track-grid">
            <?php foreach ($likedTracks as $track): ?>
                <?php render_track_card($track); ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-panel"><p>Select the heart on any track to keep it here.</p></div>
    <?php endif; ?>
</section>

<?php require APP_PATH . '/Views/partials/footer.php'; ?>

