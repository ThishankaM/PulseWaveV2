<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$query = trim((string) ($_GET['q'] ?? ''));
$results = CatalogRepository::search($query, 20);
$pageTitle = $query !== '' ? 'Search: ' . $query : 'Search';
$pageDescription = 'Search songs, artists, releases and genres on PulseWave.';
$activeNav = 'explore';
require APP_PATH . '/Views/partials/header-v3.php';
?>

<section class="search-page-hero section-shell">
    <span class="eyebrow">Search the catalog</span>
    <h1>What are you in the mood for?</h1>
    <form class="search-page-form" method="get" action="<?= e(url('search.php')) ?>">
        <?= icon('search') ?>
        <label class="sr-only" for="page-search">Search PulseWave</label>
        <input id="page-search" type="search" name="q" value="<?= e($query) ?>" placeholder="Songs, artists, releases..." autofocus>
        <button class="button button-primary" type="submit">Search</button>
    </form>
</section>

<?php if (mb_strlen($query) < 2): ?>
    <section class="content-section section-shell">
        <div class="empty-panel search-start-state">
            <?= icon('sparkles') ?>
            <h2>Start with a name</h2>
            <p>Search for an artist, song, release or feeling. Try Rosé, LiSA, Phonk or Pop.</p>
        </div>
    </section>
<?php else: ?>
    <?php if ($results['releases']): ?>
    <section class="content-section section-shell">
        <div class="section-heading">
            <div><span class="eyebrow">Releases</span><h2>Matching releases</h2></div>
        </div>
        <div class="release-grid">
            <?php foreach ($results['releases'] as $release): ?>
                <?php render_release_card($release); ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($results['tracks']): ?>
    <section class="content-section section-shell">
        <div class="section-heading">
            <div><span class="eyebrow">Songs</span><h2>Matching songs</h2></div>
        </div>
        <div class="track-grid" data-play-collection="<?= e(implode(',', array_column($results['tracks'], 'id'))) ?>" data-queue-label="Search: <?= e($query) ?>">
            <?php foreach ($results['tracks'] as $track): ?>
                <?php render_track_card($track); ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($results['artists']): ?>
    <section class="content-section section-shell">
        <div class="section-heading">
            <div><span class="eyebrow">Artists</span><h2>Matching artists</h2></div>
        </div>
        <div class="artist-directory">
            <?php foreach ($results['artists'] as $artist): ?>
                <a class="artist-tile" href="<?= e(url('artist.php?id=' . (int) $artist['id'])) ?>">
                    <img src="<?= e(media_url($artist['image_path'])) ?>" alt="" loading="lazy">
                    <div><h2><?= e($artist['name']) ?></h2><p>Artist</p></div>
                    <span aria-hidden="true"><?= icon('arrow-right') ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($results['genres']): ?>
    <section class="content-section section-shell">
        <div class="section-heading">
            <div><span class="eyebrow">Genres</span><h2>Matching genres</h2></div>
        </div>
        <div class="genre-grid">
            <?php foreach ($results['genres'] as $genre): ?>
                <a class="genre-card" style="--genre-accent: <?= e($genre['accent_color']) ?>" href="<?= e(url('explore.php?genre=' . (int) $genre['id'])) ?>">
                    <span><?= e($genre['name']) ?></span>
                    <small>Explore genre</small>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!$results['releases'] && !$results['tracks'] && !$results['artists'] && !$results['genres']): ?>
    <section class="content-section section-shell">
        <div class="empty-panel search-start-state">
            <?= icon('search') ?>
            <h2>No results for "<?= e($query) ?>"</h2>
            <p>Try a different spelling or browse something new.</p>
            <a class="button button-primary" href="<?= e(url('explore.php')) ?>">Explore music</a>
        </div>
    </section>
    <?php endif; ?>
<?php endif; ?>

<?php require APP_PATH . '/Views/partials/footer-v3.php'; ?>
