<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$genres = CatalogRepository::genres();
$releases = CatalogRepository::releases(10);
$genreId = request_int('genre');
$selectedGenre = $genreId > 0 ? CatalogRepository::genre($genreId) : null;
$tracks = $selectedGenre ? CatalogRepository::genreTracks($genreId) : CatalogRepository::featuredTracks(24);
$trackIds = implode(',', array_column($tracks, 'id'));

$pageTitle = $selectedGenre ? $selectedGenre['name'] . ' music' : 'Explore';
$pageDescription = 'Explore tracks and genres in the PulseWave catalog.';
$activeNav = 'explore';
require APP_PATH . '/Views/partials/header-v3.php';
?>

<section class="page-hero section-shell">
    <span class="eyebrow"><?= $selectedGenre ? 'Explore the sound' : 'Find your next signal' ?></span>
    <h1><?= $selectedGenre ? e($selectedGenre['name']) : 'Explore' ?></h1>
    <p><?= $selectedGenre ? 'Tracks collected for this genre.' : 'Browse every mood and find your next favorite track.' ?></p>
</section>

<?php if (!$selectedGenre): ?>
<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow">New and noteworthy</span>
            <h2>Fresh releases</h2>
        </div>
    </div>
    <div class="release-grid">
        <?php foreach ($releases as $release): ?>
            <?php render_release_card($release); ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="content-section section-shell">
    <div class="filter-row" aria-label="Filter by genre">
        <a class="<?= !$selectedGenre ? 'active' : '' ?>" href="<?= e(url('explore.php')) ?>">All</a>
        <?php foreach ($genres as $genre): ?>
            <a class="<?= $genreId === (int) $genre['id'] ? 'active' : '' ?>" href="<?= e(url('explore.php?genre=' . (int) $genre['id'])) ?>">
                <?= e($genre['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($tracks): ?>
        <div class="track-grid" data-play-collection="<?= e($trackIds) ?>" data-queue-label="<?= e($selectedGenre ? $selectedGenre['name'] . ' mix' : 'Explore PulseWave') ?>">
            <?php foreach ($tracks as $track): ?>
                <?php render_track_card($track); ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-panel"><p>No tracks are assigned to this genre yet.</p></div>
    <?php endif; ?>
</section>

<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow">Browse everything</span>
            <h2>Moods and genres</h2>
        </div>
    </div>
    <div class="genre-grid genre-grid-large">
        <?php foreach ($genres as $genre): ?>
            <a class="genre-card" style="--genre-accent: <?= e($genre['accent_color']) ?>; --genre-image: url('<?= e(media_url($genre['cover_path'])) ?>')" href="<?= e(url('explore.php?genre=' . (int) $genre['id'])) ?>">
                <span><?= e($genre['name']) ?></span>
                <small><?= (int) $genre['track_count'] ?> tracks</small>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php require APP_PATH . '/Views/partials/footer-v3.php'; ?>

