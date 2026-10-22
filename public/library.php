<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireLogin();

$playlists = LibraryRepository::playlists(Auth::id());
$likedTracks = LibraryRepository::likedTracks(Auth::id());
$followedArtists = CatalogRepository::followedArtists(Auth::id(), 8);
$likedIds = implode(',', array_column($likedTracks, 'id'));
$pageTitle = 'Your Library';
$pageDescription = 'Your PulseWave playlists and liked music.';
$activeNav = 'library';
require APP_PATH . '/Views/partials/header-v3.php';
?>

<section class="page-hero section-shell library-hero">
    <div>
        <span class="eyebrow">Collected by <?= e(Auth::user()['name']) ?></span>
        <h1>Your Library</h1>
        <p>Every song, playlist and discovery that belongs to you.</p>
    </div>
    <button class="button button-primary" type="button" data-dialog-open="create-playlist"><?= icon('plus') ?> Create playlist</button>
</section>

<nav class="library-tabs section-shell" aria-label="Library filters">
    <a class="active" href="<?= e(url('library.php')) ?>">Overview</a>
    <a href="#playlists">Playlists</a>
    <a href="#liked">Liked songs</a>
    <a href="#artists">Artists</a>
    <a href="<?= e(url('explore.php')) ?>">Discover more</a>
</nav>

<section class="content-section section-shell" id="playlists">
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

<section class="content-section section-shell" id="liked">
    <div class="section-heading">
        <div>
            <span class="eyebrow"><?= count($likedTracks) ?> favorites</span>
            <h2>Liked songs</h2>
        </div>
    </div>
    <?php if ($likedTracks): ?>
        <div class="track-grid" data-play-collection="<?= e($likedIds) ?>" data-queue-label="Liked songs">
            <?php foreach ($likedTracks as $track): ?>
                <?php render_track_card($track); ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-panel"><p>Select the heart on any track to keep it here.</p></div>
    <?php endif; ?>
</section>

<?php if ($followedArtists): ?>
<section class="content-section section-shell" id="artists">
    <div class="section-heading">
        <div>
            <span class="eyebrow"><?= count($followedArtists) ?> followed</span>
            <h2>Artists you follow</h2>
        </div>
    </div>
    <div class="artist-row">
        <?php foreach ($followedArtists as $artist): ?>
            <a class="artist-card" href="<?= e(url('artist.php?id=' . (int) $artist['id'])) ?>">
                <img src="<?= e(media_url($artist['image_path'])) ?>" alt="" loading="lazy">
                <strong><?= e($artist['name']) ?></strong>
                <span><?= (int) $artist['track_count'] ?> track<?= (int) $artist['track_count'] === 1 ? '' : 's' ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<dialog class="app-dialog create-playlist-dialog" data-dialog="create-playlist">
    <button class="dialog-close icon-button" type="button" data-dialog-close aria-label="Close"><?= icon('close') ?></button>
    <span class="eyebrow">New collection</span>
    <h2>Create a playlist</h2>
    <p class="muted">Give it a name now. You can shape the sound as you go.</p>
    <form class="stack-form" method="post" action="<?= e(url('actions/playlist.php')) ?>">
        <?= csrf_input() ?>
        <input type="hidden" name="operation" value="create">
        <label>
            <span>Playlist name</span>
            <input type="text" name="name" maxlength="80" placeholder="Midnight drive" required autofocus>
        </label>
        <button class="button button-primary" type="submit">Create playlist</button>
    </form>
</dialog>

<?php require APP_PATH . '/Views/partials/footer-v3.php'; ?>

