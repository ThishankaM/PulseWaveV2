<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireLogin();
Auth::refresh();

$userStatement = db()->prepare(
    'SELECT u.id, u.name, u.email, u.role, u.created_at, sp.name AS plan_name, s.status AS subscription_status
     FROM users u
     LEFT JOIN subscriptions s ON s.user_id = u.id AND s.status IN ("active", "pending")
     LEFT JOIN subscription_plans sp ON sp.id = s.plan_id
     WHERE u.id = ?
     ORDER BY s.created_at DESC
     LIMIT 1'
);
$userStatement->execute([Auth::id()]);
$profile = $userStatement->fetch();
$stats = LibraryRepository::userStats(Auth::id());
$recentTracks = CatalogRepository::recentlyPlayedByUser(Auth::id(), 4);
$recentIds = implode(',', array_column($recentTracks, 'id'));

$pageTitle = 'Profile';
$pageDescription = 'Manage your PulseWave profile.';
require APP_PATH . '/Views/partials/header-v3.php';
?>

<section class="profile-layout section-shell">
    <aside class="profile-summary panel">
        <div class="profile-avatar"><?= e(mb_strtoupper(mb_substr($profile['name'], 0, 1))) ?></div>
        <span class="role-badge"><?= e(ucfirst($profile['role'])) ?></span>
        <h1><?= e($profile['name']) ?></h1>
        <p><?= e($profile['email']) ?></p>
        <p class="muted">Member since <?= e(date('F Y', strtotime($profile['created_at']))) ?></p>
        <?php if (in_array($profile['role'], ['artist', 'admin'], true)): ?>
            <a class="button button-ghost button-wide" href="<?= e(url('dashboard.php')) ?>">Open dashboard</a>
        <?php endif; ?>
    </aside>

    <div class="profile-content">
        <section class="panel">
            <span class="eyebrow">Your listening identity</span>
            <h2>Your PulseWave story</h2>
            <div class="stat-grid">
                <div><strong><?= (int) $stats['playlists'] ?></strong><span>Playlists</span></div>
                <div><strong><?= (int) $stats['likes'] ?></strong><span>Liked tracks</span></div>
                <div><strong><?= (int) $stats['follows'] ?></strong><span>Following</span></div>
                <div><strong><?= (int) $stats['plays'] ?></strong><span>Recorded plays</span></div>
            </div>
        </section>

        <?php if ($recentTracks): ?>
        <section class="panel profile-recent">
            <span class="eyebrow">Back in rotation</span>
            <h2>Recently played</h2>
            <div class="track-grid profile-track-grid" data-play-collection="<?= e($recentIds) ?>" data-queue-label="Recently played">
                <?php foreach ($recentTracks as $track): ?>
                    <?php render_track_card($track); ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <section class="panel">
            <span class="eyebrow">Personal details</span>
            <h2>Edit your profile</h2>
            <form class="stack-form" method="post" action="<?= e(url('actions/profile.php')) ?>">
                <?= csrf_input() ?>
                <label><span>Display name</span><input type="text" name="name" value="<?= e($profile['name']) ?>" minlength="2" maxlength="80" required></label>
                <label><span>Email address</span><input type="email" value="<?= e($profile['email']) ?>" disabled><small>Email changes require administrator verification.</small></label>
                <button class="button button-primary" type="submit">Save changes</button>
            </form>
        </section>

        <section class="panel plan-summary">
            <div>
                <span class="eyebrow">Subscription</span>
                <h2><?= e($profile['plan_name'] ?? 'Free') ?> plan</h2>
                <p><?= e(ucfirst($profile['subscription_status'] ?? 'active')) ?> account access.</p>
            </div>
            <a class="button button-ghost" href="<?= e(url('subscription.php')) ?>">View plans</a>
        </section>
    </div>
</section>

<?php require APP_PATH . '/Views/partials/footer-v3.php'; ?>

