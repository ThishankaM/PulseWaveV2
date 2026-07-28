<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$featuredTracks = CatalogRepository::featuredTracks(10);
$recentTracks = CatalogRepository::recentTracks(8);
$artists = CatalogRepository::artists(8);
$genres = array_slice(CatalogRepository::genres(), 0, 8);
$heroTrack = $featuredTracks[0] ?? null;

$pageTitle = 'Feel every frequency';
$pageDescription = 'Discover trending tracks, artists and genres on PulseWave.';
$activeNav = 'home';
require APP_PATH . '/Views/partials/header.php';
?>

<section class="hero section-shell">
    <div class="hero-copy">
        <span class="eyebrow">Your sound. Your space.</span>
        <h1>Music that moves at <em>your frequency.</em></h1>
        <p>Explore a global catalog, build personal playlists and keep every favourite close.</p>
        <div class="hero-actions">
            <?php if ($heroTrack): ?>
                <button class="button button-primary" type="button" data-play-track="<?= (int) $heroTrack['id'] ?>">Play trending</button>
            <?php endif; ?>
            <a class="button button-ghost" href="<?= e(url('explore.php')) ?>">Explore music</a>
        </div>
        <dl class="hero-stats">
            <div><dt><?= count($featuredTracks) ?>+</dt><dd>featured tracks</dd></div>
            <div><dt><?= count($artists) ?>+</dt><dd>featured artists</dd></div>
            <div><dt><?= count($genres) ?>+</dt><dd>music moods</dd></div>
        </dl>
    </div>

    <?php if ($heroTrack): ?>
        <button class="hero-record" type="button" data-play-track="<?= (int) $heroTrack['id'] ?>" aria-label="Play <?= e($heroTrack['title']) ?>">
            <span class="record-orbit"></span>
            <img src="<?= e(media_url($heroTrack['cover_path'])) ?>" alt="">
            <span class="hero-record-label">
                <strong><?= e($heroTrack['title']) ?></strong>
                <small><?= e($heroTrack['artist_name']) ?></small>
            </span>
        </button>
    <?php endif; ?>
</section>

<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow">Popular right now</span>
            <h2>Trending tracks</h2>
        </div>
        <a href="<?= e(url('explore.php')) ?>">See all <span aria-hidden="true">→</span></a>
    </div>
    <div class="track-grid">
        <?php foreach ($featuredTracks as $track): ?>
            <?php render_track_card($track); ?>
        <?php endforeach; ?>
    </div>
</section>

<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow">New to the catalog</span>
            <h2>Fresh additions</h2>
        </div>
    </div>
    <div class="compact-track-list">
        <?php foreach ($recentTracks as $index => $track): ?>
            <button class="compact-track" type="button" data-play-track="<?= (int) $track['id'] ?>">
                <span class="track-number"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <img src="<?= e(media_url($track['cover_path'])) ?>" alt="" loading="lazy">
                <span class="compact-copy">
                    <strong><?= e($track['title']) ?></strong>
                    <small><?= e($track['artist_name']) ?></small>
                </span>
                <span><?= format_count($track['play_count']) ?> plays</span>
                <span class="compact-play" aria-hidden="true">▶</span>
            </button>
        <?php endforeach; ?>
    </div>
</section>

<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow">People behind the sound</span>
            <h2>Featured artists</h2>
        </div>
        <a href="<?= e(url('artists.php')) ?>">Meet all artists <span aria-hidden="true">→</span></a>
    </div>
    <div class="artist-row">
        <?php foreach ($artists as $artist): ?>
            <a class="artist-card" href="<?= e(url('artist.php?id=' . (int) $artist['id'])) ?>">
                <img src="<?= e(media_url($artist['image_path'])) ?>" alt="" loading="lazy">
                <strong><?= e($artist['name']) ?></strong>
                <span><?= format_count($artist['follower_count']) ?> followers</span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow">Pick a feeling</span>
            <h2>Browse by mood</h2>
        </div>
    </div>
    <div class="genre-grid">
        <?php foreach ($genres as $genre): ?>
            <a class="genre-card" style="--genre-accent: <?= e($genre['accent_color']) ?>" href="<?= e(url('explore.php?genre=' . (int) $genre['id'])) ?>">
                <span><?= e($genre['name']) ?></span>
                <small><?= (int) $genre['track_count'] ?> tracks</small>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php require APP_PATH . '/Views/partials/footer.php'; ?>

