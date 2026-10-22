<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$featuredTracks = CatalogRepository::trendingTracks(10);
$recentTracks = CatalogRepository::recentTracks(8);
$recentListening = Auth::check() ? CatalogRepository::recentlyPlayedByUser(Auth::id(), 8) : [];
$releases = CatalogRepository::releases(8);
$artists = array_values(array_filter(
    CatalogRepository::artists(12),
    static fn(array $artist): bool => !str_contains((string) $artist['image_path'], 'default-album-art')
));
$artists = array_slice($artists, 0, 8);
$genres = array_slice(CatalogRepository::genres(), 0, 8);
$heroTrack = $featuredTracks[0] ?? null;
$featuredIds = implode(',', array_column($featuredTracks, 'id'));
$freshIds = implode(',', array_column($recentTracks, 'id'));
$listener = Auth::user();
$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$totalPlays = array_sum(array_map(static fn(array $track): int => (int) $track['play_count'], $featuredTracks));

$pageTitle = 'Home';
$pageDescription = 'Discover music, artists and curated listening moments on PulseWave.';
$activeNav = 'home';
require APP_PATH . '/Views/partials/header-v3.php';
?>

<section class="home-stage section-shell">
    <div class="home-stage-copy">
        <span class="eyebrow"><?= $listener ? 'Your listening room' : 'A new frequency' ?></span>
        <h1>
            <?php if ($listener): ?>
                <?= e($greeting) ?>, <?= e($listener['name']) ?>.<br><em>Let the music take over.</em>
            <?php else: ?>
                Every moment deserves<br><em>the right soundtrack.</em>
            <?php endif; ?>
        </h1>
        <p><?= $listener ? 'Pick up where you left off or follow the signal into something new.' : 'Discover music with atmosphere, follow the artists behind it, and keep every favorite close.' ?></p>
        <div class="hero-actions">
            <?php if ($heroTrack): ?>
                <button class="button button-primary" type="button" data-play-track="<?= (int) $heroTrack['id'] ?>" data-play-collection="<?= e($featuredIds) ?>" data-queue-label="Trending on PulseWave">
                    <?= icon('play') ?> Play tonight's signal
                </button>
            <?php endif; ?>
            <a class="button button-ghost" href="<?= e(url('explore.php')) ?>"><?= icon('compass') ?> Discover music</a>
        </div>
        <dl class="hero-stats">
            <div><dt><?= format_count($totalPlays) ?></dt><dd>listener plays</dd></div>
            <div><dt><?= count($featuredTracks) ?></dt><dd>signals in rotation</dd></div>
            <div><dt><?= count($genres) ?></dt><dd>moods to explore</dd></div>
        </dl>
    </div>

    <?php if ($heroTrack): ?>
        <div class="home-stage-visual" data-play-collection="<?= e($featuredIds) ?>" data-queue-label="Tonight's signal">
            <div class="home-stage-art">
                <img src="<?= e(media_url($heroTrack['cover_path'])) ?>" alt="">
                <button class="home-stage-play" type="button" data-play-track="<?= (int) $heroTrack['id'] ?>" aria-label="Play <?= e($heroTrack['title']) ?>">
                    <?= icon('play') ?>
                </button>
            </div>
            <div class="home-stage-card">
                <span>Tonight's signal</span>
                <strong><?= e($heroTrack['title']) ?></strong>
                <small><?= e($heroTrack['artist_name']) ?></small>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php if ($recentListening): ?>
<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow">Welcome back</span>
            <h2>Keep listening</h2>
        </div>
        <a href="<?= e(url('library.php')) ?>">Your library <?= icon('arrow-right') ?></a>
    </div>
    <div class="track-grid" data-play-collection="<?= e(implode(',', array_column($recentListening, 'id'))) ?>" data-queue-label="Recently played">
        <?php foreach (array_slice($recentListening, 0, 5) as $track): ?>
            <?php render_track_card($track); ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow">New in the catalog</span>
            <h2>Fresh releases</h2>
        </div>
        <a href="<?= e(url('explore.php')) ?>">Browse releases <?= icon('arrow-right') ?></a>
    </div>
    <div class="release-grid">
        <?php foreach (array_slice($releases, 0, 5) as $release): ?>
            <?php render_release_card($release); ?>
        <?php endforeach; ?>
    </div>
</section>

<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow">Popular right now</span>
            <h2>Trending tracks</h2>
        </div>
        <a href="<?= e(url('explore.php')) ?>">See all <?= icon('arrow-right') ?></a>
    </div>
    <div class="track-grid" data-play-collection="<?= e($featuredIds) ?>" data-queue-label="Trending on PulseWave">
        <?php foreach ($featuredTracks as $track): ?>
            <?php render_track_card($track); ?>
        <?php endforeach; ?>
    </div>
</section>

<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow">New to the catalog</span>
            <h2>Fresh on PulseWave</h2>
        </div>
    </div>
    <div class="compact-track-list" data-play-collection="<?= e($freshIds) ?>" data-queue-label="Fresh additions">
        <?php foreach ($recentTracks as $index => $track): ?>
            <button class="compact-track" type="button" data-play-track="<?= (int) $track['id'] ?>">
                <span class="track-number"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <img src="<?= e(media_url($track['cover_path'])) ?>" alt="" loading="lazy">
                <span class="compact-copy">
                    <strong><?= e($track['title']) ?></strong>
                    <small><?= e($track['artist_name']) ?></small>
                </span>
                <span><?= format_count($track['play_count']) ?> plays</span>
                <span class="compact-play" aria-hidden="true"><?= icon('play') ?></span>
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
        <a href="<?= e(url('artists.php')) ?>">Meet all artists <?= icon('arrow-right') ?></a>
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
            <a class="genre-card" style="--genre-accent: <?= e($genre['accent_color']) ?>; --genre-image: url('<?= e(media_url($genre['cover_path'])) ?>')" href="<?= e(url('explore.php?genre=' . (int) $genre['id'])) ?>">
                <span><?= e($genre['name']) ?></span>
                <small><?= (int) $genre['track_count'] ?> tracks</small>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php require APP_PATH . '/Views/partials/footer-v3.php'; ?>

