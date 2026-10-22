<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
Auth::requireRole(['artist', 'admin']);

$isAdmin = Auth::role() === 'admin';
$sql = 'SELECT t.id, t.title, t.cover_path, t.play_count, t.like_count, t.uploaded_by, a.name AS artist_name, u.name AS uploader_name
        FROM tracks t
        INNER JOIN artists a ON a.id = t.artist_id
        LEFT JOIN users u ON u.id = t.uploaded_by';
$parameters = [];
if (!$isAdmin) {
    $sql .= ' WHERE t.uploaded_by = ?';
    $parameters[] = Auth::id();
}
$sql .= ' ORDER BY t.created_at DESC LIMIT 100';
$statement = db()->prepare($sql);
$statement->execute($parameters);
$tracks = $statement->fetchAll();

$pageTitle = 'Creator dashboard';
$pageDescription = 'Manage PulseWave catalog uploads.';
require APP_PATH . '/Views/partials/header-v3.php';
?>

<section class="page-hero section-shell dashboard-hero">
    <div>
        <span class="eyebrow"><?= $isAdmin ? 'Administration' : 'Artist workspace' ?></span>
        <h1>Creator dashboard</h1>
        <p>Manage catalog uploads and review track performance.</p>
    </div>
    <a class="button button-primary" href="<?= e(url('upload.php')) ?>"><?= icon('plus') ?> Release new music</a>
</section>

<section class="content-section section-shell">
    <div class="section-heading">
        <div>
            <span class="eyebrow"><?= count($tracks) ?> visible uploads</span>
            <h2>Catalog management</h2>
        </div>
    </div>
    <div class="management-table-wrap">
        <table class="management-table">
            <thead><tr><th>Track</th><th>Artist</th><th>Plays</th><th>Likes</th><th>Uploader</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($tracks as $track): ?>
                <tr>
                    <td><div class="table-track"><img src="<?= e(media_url($track['cover_path'])) ?>" alt=""><strong><?= e($track['title']) ?></strong></div></td>
                    <td><?= e($track['artist_name']) ?></td>
                    <td><?= format_count($track['play_count']) ?></td>
                    <td><?= format_count($track['like_count']) ?></td>
                    <td><?= e($track['uploader_name'] ?? 'Imported catalog') ?></td>
                    <td>
                        <?php if ($isAdmin || (int) $track['uploaded_by'] === Auth::id()): ?>
                            <form method="post" action="<?= e(url('actions/delete-track.php')) ?>" data-confirm="Remove this track from the catalog?">
                                <?= csrf_input() ?>
                                <input type="hidden" name="track_id" value="<?= (int) $track['id'] ?>">
                                <button class="text-danger" type="submit"><?= icon('trash') ?><span>Remove</span></button>
                            </form>
                        <?php else: ?>
                            <span class="muted">Protected</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require APP_PATH . '/Views/partials/footer-v3.php'; ?>

