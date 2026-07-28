<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? 'PulseWave';
$pageDescription = $pageDescription ?? 'Discover music, artists, genres and playlists on PulseWave.';
$activeNav = $activeNav ?? '';
$bodyClass = $bodyClass ?? '';
$flashMessages = flashes();
$user = Auth::user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="theme-color" content="#0b0914">
    <title><?= e($pageTitle) ?> · PulseWave</title>
    <link rel="icon" href="<?= e(media_url(null)) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/app.css')) ?>">
    <script>
        window.PULSEWAVE = <?= json_encode([
            'baseUrl' => base_url(),
            'authenticated' => Auth::check(),
            'csrfToken' => csrf_token(),
        ], JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="<?= e(url('assets/js/app.js')) ?>" defer></script>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main-content">Skip to content</a>

<header class="site-header">
    <div class="nav-shell">
        <a class="brand" href="<?= e(url('index.php')) ?>" aria-label="PulseWave home">
            <span class="brand-mark" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
            <span>PulseWave</span>
        </a>

        <button class="menu-toggle" type="button" data-menu-toggle aria-expanded="false" aria-controls="primary-navigation">
            <span></span><span></span><span></span>
            <span class="sr-only">Toggle menu</span>
        </button>

        <nav class="primary-nav" id="primary-navigation" data-mobile-menu aria-label="Primary navigation">
            <a class="<?= $activeNav === 'home' ? 'active' : '' ?>" href="<?= e(url('index.php')) ?>">Home</a>
            <a class="<?= $activeNav === 'explore' ? 'active' : '' ?>" href="<?= e(url('explore.php')) ?>">Explore</a>
            <a class="<?= $activeNav === 'artists' ? 'active' : '' ?>" href="<?= e(url('artists.php')) ?>">Artists</a>
            <a class="<?= $activeNav === 'library' ? 'active' : '' ?>" href="<?= e(url('library.php')) ?>">Library</a>
            <a class="<?= $activeNav === 'upgrade' ? 'active' : '' ?>" href="<?= e(url('subscription.php')) ?>">Upgrade</a>
        </nav>

        <div class="nav-actions">
            <button class="search-trigger" type="button" data-search-open aria-label="Open search">
                <span aria-hidden="true">⌕</span>
                <span>Search</span>
            </button>
            <?php if ($user): ?>
                <div class="profile-menu">
                    <a class="avatar-button" href="<?= e(url('profile.php')) ?>" aria-label="Open profile">
                        <?= e(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?>
                    </a>
                    <div class="profile-links">
                        <a href="<?= e(url('profile.php')) ?>"><?= e($user['name']) ?></a>
                        <?php if (in_array($user['role'], ['artist', 'admin'], true)): ?>
                            <a href="<?= e(url('dashboard.php')) ?>">Dashboard</a>
                        <?php endif; ?>
                        <form method="post" action="<?= e(url('actions/logout.php')) ?>">
                            <?= csrf_input() ?>
                            <button type="submit">Sign out</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <a class="button button-small button-ghost" href="<?= e(url('login.php')) ?>">Sign in</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="search-panel" data-search-panel hidden>
    <div class="search-dialog" role="dialog" aria-modal="true" aria-labelledby="search-title">
        <div class="search-heading">
            <div>
                <span class="eyebrow">Find your sound</span>
                <h2 id="search-title">Search PulseWave</h2>
            </div>
            <button class="icon-button" type="button" data-search-close aria-label="Close search">×</button>
        </div>
        <label class="search-field">
            <span class="sr-only">Search tracks, artists and genres</span>
            <input type="search" data-search-input autocomplete="off" placeholder="Search tracks, artists and genres…">
        </label>
        <div class="search-results" data-search-results>
            <p class="empty-copy">Start typing to search your music catalog.</p>
        </div>
    </div>
</div>

<?php if ($flashMessages): ?>
    <div class="flash-stack" aria-live="polite">
        <?php foreach ($flashMessages as $message): ?>
            <div class="flash flash-<?= e($message['type'] ?? 'info') ?>">
                <?= e($message['message'] ?? '') ?>
                <button type="button" data-dismiss-flash aria-label="Dismiss message">×</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main id="main-content">
