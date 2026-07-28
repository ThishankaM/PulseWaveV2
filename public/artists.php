<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$artists = CatalogRepository::artists();
$pageTitle = 'Artists';
$pageDescription = 'Browse artists and their music on PulseWave.';
$activeNav = 'artists';
require APP_PATH . '/Views/partials/header.php';
?>

<section class="page-hero section-shell">
    <span class="eyebrow">People behind the sound</span>
    <h1>Artists</h1>
    <p>Find familiar voices and discover someone new.</p>
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

<?php require APP_PATH . '/Views/partials/footer.php'; ?>

