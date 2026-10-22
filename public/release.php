<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$representativeId = request_int('track');
$tracks = $representativeId > 0 ? CatalogRepository::releaseTracks($representativeId) : [];
if (!$tracks) {
    http_response_code(404);
    $pageTitle = 'Release not found';
    require APP_PATH . '/Views/partials/header-v3.php';
    ?>
    <section class="empty-state section-shell">
        <span class="empty-icon"><?= icon('disc') ?></span>
        <h1>Release not found</h1>
        <p>This release may have moved or is no longer available.</p>
        <a class="button button-primary" href="<?= e(url('explore.php')) ?>">Browse music</a>
    </section>
    <?php
    require APP_PATH . '/Views/partials/footer-v3.php';
    exit;
}

$release = $tracks[0];
$trackIds = implode(',', array_column($tracks, 'id'));
$totalSeconds = array_sum(array_map(static fn(array $track): int => (int) ($track['duration_seconds'] ?? 0), $tracks));
$pageTitle = $release['title'];
$pageDescription = 'Listen to ' . $release['title'] . ' by ' . $release['artist_name'] . ' on PulseWave.';
$activeNav = 'explore';
require APP_PATH . '/Views/partials/header-v3.php';
?>

<section class="release-hero section-shell" data-play-collection="<?= e($trackIds) ?>" data-queue-label="<?= e($release['title']) ?>">
    <div class="release-hero-art">
        <img src="<?= e(media_url($release['cover_path'])) ?>" alt="">
        <button class="release-hero-play" type="button" data-play-track="<?= (int) $release['id'] ?>" aria-label="Play <?= e($release['title']) ?>">
            <?= icon('play') ?>
        </button>
    </div>
    <div class="release-hero-copy">
        <span class="eyebrow">Release</span>
        <h1><?= e($release['title']) ?></h1>
        <a class="release-artist-link" href="<?= e(url('artist.php?id=' . (int) $release['artist_id'])) ?>">
            <span class="artist-mini-avatar"><?= e(mb_strtoupper(mb_substr($release['artist_name'], 0, 1))) ?></span>
            <?= e($release['artist_name']) ?>
        </a>
        <p><?= count($tracks) ?> song<?= count($tracks) === 1 ? '' : 's' ?><?= $release['release_year'] ? ' / ' . (int) $release['release_year'] : '' ?><?= $totalSeconds ? ' / ' . floor($totalSeconds / 60) . ' min' : '' ?></p>
        <div class="hero-actions">
            <button class="button button-primary" type="button" data-play-track="<?= (int) $release['id'] ?>"><?= icon('play') ?> Play release</button>
            <a class="button button-ghost" href="<?= e(url('artist.php?id=' . (int) $release['artist_id'])) ?>">View artist</a>
        </div>
    </div>
</section>

<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow">Track list</span>
            <h2>On this release</h2>
        </div>
    </div>
    <div class="release-track-list" data-play-collection="<?= e($trackIds) ?>" data-queue-label="<?= e($release['title']) ?>">
        <?php foreach ($tracks as $index => $track): ?>
            <button class="release-track" type="button" data-play-track="<?= (int) $track['id'] ?>">
                <span class="track-number"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <span class="release-track-main">
                    <strong><?= e($track['title']) ?></strong>
                    <small><?= (int) $track['release_year'] ?: 'PulseWave' ?></small>
                </span>
                <span><?= (int) $track['duration_seconds'] > 0 ? floor((int) $track['duration_seconds'] / 60) . ':' . str_pad((string) ((int) $track['duration_seconds'] % 60), 2, '0', STR_PAD_LEFT) : 'PulseWave' ?></span>
                <span class="release-row-play"><?= icon('play') ?></span>
            </button>
        <?php endforeach; ?>
    </div>
</section>

<?php require APP_PATH . '/Views/partials/footer-v3.php'; ?>
