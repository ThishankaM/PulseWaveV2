<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
Auth::requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('profile.php');
}
verify_csrf();

$name = trim((string) ($_POST['name'] ?? ''));
if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
    flash('error', 'Display name must contain between 2 and 80 characters.');
    redirect('profile.php');
}

$statement = db()->prepare('UPDATE users SET name = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
$statement->execute([$name, Auth::id()]);
Auth::refresh();
flash('success', 'Profile updated.');
redirect('profile.php');

