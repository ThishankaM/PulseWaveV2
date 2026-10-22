<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? 'PulseWave';
$pageDescription = $pageDescription ?? 'Discover music, artists and playlists on PulseWave.';
$activeNav = $activeNav ?? '';
$bodyClass = $bodyClass ?? '';
$flashMessages = flashes();
$user = Auth::user();
$displayName = $user['name'] ?? 'Listener';
$initial = mb_strtoupper(mb_substr($displayName, 0, 1));
$navItems = [
    ['key' => 'home', 'label' => 'Home', 'href' => 'index.php', 'icon' => 'home'],
    ['key' => 'explore', 'label' => 'Discover', 'href' => 'explore.php', 'icon' => 'compass'],
    ['key' => 'artists', 'label' => 'Artists', 'href' => 'artists.php', 'icon' => 'users'],
    ['key' => 'library', 'label' => 'Your Library', 'href' => 'library.php', 'icon' => 'library'],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="theme-color" content="#08080a">
    <title><?= e($pageTitle) ?> | PulseWave</title>
    <link rel="icon" type="image/svg+xml" href="<?= e(url('assets/favicon.svg')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/pulsewave.css')) ?>">
    <script>
        window.PULSEWAVE = <?= json_encode([
            'baseUrl' => base_url(),
            'authenticated' => Auth::check(),
            'csrfToken' => csrf_token(),
        ], JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="<?= e(url('assets/js/app-v3.js')) ?>" defer></script>
</head>
<body class="<?= e(trim('app-body ' . $bodyClass)) ?>">
<a class="skip-link" href="#main-content">Skip to content</a>

<div class="app-frame">
    <aside class="side-rail" id="app-sidebar" data-mobile-menu aria-label="PulseWave navigation">
        <a class="brand rail-brand" href="<?= e(url('index.php')) ?>" aria-label="PulseWave home">
            <?= brand_mark() ?>
            <span>PulseWave</span>
        </a>

        <nav class="rail-nav" aria-label="Primary navigation">
            <span class="rail-label">Listen</span>
            <?php foreach ($navItems as $item): ?>
                <a class="<?= $activeNav === $item['key'] ? 'active' : '' ?>" href="<?= e(url($item['href'])) ?>" <?= $activeNav === $item['key'] ? 'aria-current="page"' : '' ?>>
                    <?= icon($item['icon']) ?>
                    <span><?= e($item['label']) ?></span>
                    <?php if ($item['key'] === 'library' && !$user): ?><small>Sign in</small><?php endif; ?>
                </a>
            <?php endforeach; ?>
            <button class="rail-search" type="button" data-search-open>
                <?= icon('search') ?>
                <span>Search</span>
                <kbd>Ctrl K</kbd>
            </button>
        </nav>

        <div class="rail-library">
            <span class="rail-label">Your collection</span>
            <a href="<?= e(url('library.php#liked')) ?>">
                <span class="rail-item-art rail-item-heart"><?= icon('heart') ?></span>
                <span><strong>Liked songs</strong><small>Your favorites</small></span>
            </a>
            <a href="<?= e(url('library.php')) ?>">
                <span class="rail-item-art rail-item-vinyl"><?= icon('disc') ?></span>
                <span><strong>Playlists</strong><small>Made by you</small></span>
            </a>
            <a href="<?= e(url('explore.php')) ?>">
                <span class="rail-item-art rail-item-signal"><?= icon('sparkles') ?></span>
                <span><strong>Pulse Picks</strong><small>Curated for you</small></span>
            </a>
        </div>

        <div class="rail-bottom">
            <a class="rail-upgrade" href="<?= e(url('subscription.php')) ?>">
                <span class="rail-upgrade-icon"><?= icon('sparkles') ?></span>
                <span><strong>Go further with Pulse+</strong><small>Higher quality. Zero limits.</small></span>
                <?= icon('arrow-right') ?>
            </a>
            <?php if ($user && in_array($user['role'], ['artist', 'admin'], true)): ?>
                <a class="rail-utility" href="<?= e(url('dashboard.php')) ?>"><?= icon('upload') ?><span>Creator studio</span></a>
            <?php endif; ?>
            <span class="rail-version">PulseWave 2.0</span>
        </div>
    </aside>

    <div class="workspace">
        <header class="topbar">
            <button class="menu-toggle" type="button" data-menu-toggle aria-expanded="false" aria-controls="app-sidebar">
                <?= icon('menu') ?>
                <span class="sr-only">Toggle navigation</span>
            </button>
            <div class="topbar-context">
                <span>PulseWave</span>
                <strong><?= e($pageTitle) ?></strong>
            </div>
            <div class="topbar-actions">
                <button class="search-trigger" type="button" data-search-open aria-label="Search PulseWave">
                    <?= icon('search') ?>
                    <span>Search</span>
                </button>
                <?php if ($user): ?>
                    <div class="profile-menu">
                        <button class="avatar-button" type="button" aria-label="Open account menu" aria-haspopup="true">
                            <span><?= e($initial) ?></span>
                            <?= icon('chevron-down') ?>
                        </button>
                        <div class="profile-links">
                            <div class="profile-menu-head">
                                <span class="avatar-button avatar-static"><?= e($initial) ?></span>
                                <span><strong><?= e($displayName) ?></strong><small><?= e(ucfirst($user['role'])) ?></small></span>
                            </div>
                            <a href="<?= e(url('profile.php')) ?>"><?= icon('user') ?><span>Profile</span></a>
                            <a href="<?= e(url('library.php')) ?>"><?= icon('library') ?><span>Your library</span></a>
                            <?php if (in_array($user['role'], ['artist', 'admin'], true)): ?>
                                <a href="<?= e(url('dashboard.php')) ?>"><?= icon('upload') ?><span>Creator studio</span></a>
                            <?php endif; ?>
                            <form method="post" action="<?= e(url('actions/logout.php')) ?>">
                                <?= csrf_input() ?>
                                <button type="submit"><?= icon('logout') ?><span>Sign out</span></button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <a class="button button-small button-ghost" href="<?= e(url('login.php')) ?>">Sign in</a>
                <?php endif; ?>
            </div>
        </header>

        <div class="mobile-nav-scrim" data-nav-scrim></div>

        <div class="search-panel" data-search-panel hidden>
            <div class="search-dialog" role="dialog" aria-modal="true" aria-labelledby="search-title">
                <div class="search-heading">
                    <div>
                        <span class="eyebrow">Explore the catalog</span>
                        <h2 id="search-title">What do you want to hear?</h2>
                    </div>
                    <button class="icon-button" type="button" data-search-close aria-label="Close search"><?= icon('close') ?></button>
                </div>
                <label class="search-field">
                    <span class="sr-only">Search tracks, artists and genres</span>
                    <?= icon('search') ?>
                    <input type="search" data-search-input autocomplete="off" placeholder="Songs, artists, genres...">
                    <kbd>ESC</kbd>
                </label>
                <div class="search-quick" data-search-quick>
                    <span>Try</span>
                    <button type="button" data-search-suggestion="Rosé">Rosé</button>
                    <button type="button" data-search-suggestion="Phonk">Phonk</button>
                    <button type="button" data-search-suggestion="Pop">Pop</button>
                    <button type="button" data-search-suggestion="LiSA">LiSA</button>
                </div>
                <div class="search-results" data-search-results aria-live="polite">
                    <div class="search-empty">
                        <?= icon('sparkles') ?>
                        <strong>Find your next favorite</strong>
                        <span>Search across the whole PulseWave catalog.</span>
                    </div>
                </div>
                <div class="search-dialog-footer">
                    <span>Use the arrow keys to move through results.</span>
                    <a href="<?= e(url('search.php')) ?>" data-search-all>Open full search <?= icon('arrow-right') ?></a>
                </div>
            </div>
        </div>

        <?php if ($flashMessages): ?>
            <div class="flash-stack" aria-live="polite">
                <?php foreach ($flashMessages as $message): ?>
                    <div class="flash flash-<?= e($message['type'] ?? 'info') ?>">
                        <span><?= e($message['message'] ?? '') ?></span>
                        <button type="button" data-dismiss-flash aria-label="Dismiss message"><?= icon('close') ?></button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <main id="main-content">
