<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$artistId = request_int('id');
$artist = $artistId > 0 ? CatalogRepository::artist($artistId) : null;
if (!$artist) {
    http_response_code(404);
    $pageTitle = 'Artist not found';
    require APP_PATH . '/Views/partials/header-v3.php';
    ?>
    <section class="empty-state section-shell">
        <span class="empty-icon">?</span>
        <h1>Artist not found</h1>
        <p>The artist may have been removed or the link is incorrect.</p>
        <a class="button button-primary" href="<?= e(url('artists.php')) ?>">Browse artists</a>
    </section>
    <?php
    require APP_PATH . '/Views/partials/footer-v3.php';
    exit;
}

$tracks = CatalogRepository::artistTracks($artistId);
$trackIds = implode(',', array_column($tracks, 'id'));
$pageTitle = $artist['name'];
$pageDescription = 'Listen to ' . $artist['name'] . ' on PulseWave.';
$activeNav = 'artists';
require APP_PATH . '/Views/partials/header-v3.php';
?>

<section class="artist-hero section-shell" style="--artist-image: url('<?= e(media_url($artist['image_path'])) ?>')">
    <div class="artist-hero-overlay"></div>
    <div class="artist-hero-content">
        <span class="verified-label">PulseWave artist</span>
        <h1><?= e($artist['name']) ?></h1>
        <p><?= e($artist['bio'] ?: 'Discover the latest music and popular releases from this artist.') ?></p>
        <div class="artist-meta">
            <span><?= format_count($artist['follower_count']) ?> followers</span>
            <span><?= (int) $artist['track_count'] ?> track<?= (int) $artist['track_count'] === 1 ? '' : 's' ?></span>
        </div>
        <div class="hero-actions" data-play-collection="<?= e($trackIds) ?>" data-queue-label="<?= e($artist['name']) ?> radio">
            <?php if ($tracks): ?>
                <button class="button button-primary" type="button" data-play-track="<?= (int) $tracks[0]['id'] ?>"><?= icon('play') ?> Play</button>
                <button class="button button-ghost" type="button" data-shuffle-artist="<?= (int) $artist['id'] ?>">Shuffle</button>
            <?php endif; ?>
            <?php if (Auth::check()): ?>
                <form method="post" action="<?= e(url('actions/toggle-follow.php')) ?>" data-follow-form data-artist-id="<?= $artistId ?>">
                    <?= csrf_input() ?>
                    <input type="hidden" name="artist_id" value="<?= $artistId ?>">
                    <button class="button button-ghost <?= $artist['is_following'] ? 'is-active' : '' ?>" type="submit" aria-pressed="<?= $artist['is_following'] ? 'true' : 'false' ?>" data-follow-button>
                        <?= $artist['is_following'] ? 'Following' : 'Follow' ?>
                    </button>
                </form>
            <?php else: ?>
                <a class="button button-ghost" href="<?= e(url('login.php')) ?>">Sign in to follow</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow">Catalog</span>
            <h2>Popular tracks</h2>
        </div>
    </div>
    <?php if ($tracks): ?>
        <div class="track-grid" data-play-collection="<?= e($trackIds) ?>" data-queue-label="<?= e($artist['name']) ?> radio">
            <?php foreach ($tracks as $track): ?>
                <?php render_track_card($track, false); ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-panel">
            <p>No tracks have been added for this artist yet.</p>
        </div>
    <?php endif; ?>
</section>

<?php require APP_PATH . '/Views/partials/footer-v3.php'; ?>

