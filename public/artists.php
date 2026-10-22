<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$artists = array_values(array_filter(
    CatalogRepository::artists(),
    static fn(array $artist): bool => (int) $artist['track_count'] > 0
        && !str_contains((string) $artist['image_path'], 'default-album-art')
));
$pageTitle = 'Artists';
$pageDescription = 'Browse artists and their music on PulseWave.';
$activeNav = 'artists';
require APP_PATH . '/Views/partials/header-v3.php';
?>

<section class="page-hero section-shell">
    <span class="eyebrow">People shaping the sound</span>
    <h1>Artists</h1>
    <p>Follow the voices behind the records. Every artist here has music waiting.</p>
</section>

<section class="content-section section-shell">
    <div class="artist-directory">
        <?php foreach ($artists as $artist): ?>
            <a class="artist-tile" href="<?= e(url('artist.php?id=' . (int) $artist['id'])) ?>">
                <img src="<?= e(media_url($artist['image_path'])) ?>" alt="" loading="lazy">
                <div>
                    <h2><?= e($artist['name']) ?></h2>
                    <p><?= (int) $artist['track_count'] ?> tracks · <?= format_count($artist['follower_count']) ?> followers</p>
                </div>
                <span aria-hidden="true">↗</span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php require APP_PATH . '/Views/partials/footer-v3.php'; ?>

