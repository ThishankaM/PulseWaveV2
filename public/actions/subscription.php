<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
Auth::requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('subscription.php');
}
verify_csrf();

$planId = post_int('plan_id');
$planStatement = db()->prepare('SELECT id, name, price_monthly FROM subscription_plans WHERE id = ? AND is_active = 1');
$planStatement->execute([$planId]);
$plan = $planStatement->fetch();
if (!$plan) {
    flash('error', 'Selected plan is unavailable.');
    redirect('subscription.php');
}

$status = (float) $plan['price_monthly'] === 0.0 ? 'active' : 'pending';
$connection = db();
$connection->beginTransaction();
try {
    $cancel = $connection->prepare(
        'UPDATE subscriptions SET status = "cancelled", ends_at = CURRENT_TIMESTAMP
         WHERE user_id = ? AND status IN ("active", "pending")'
    );
    $cancel->execute([Auth::id()]);

    $insert = $connection->prepare(
        'INSERT INTO subscriptions (user_id, plan_id, status, starts_at)
         VALUES (?, ?, ?, CURRENT_TIMESTAMP)'
    );
    $insert->execute([Auth::id(), $planId, $status]);
    $connection->commit();
} catch (Throwable $exception) {
    $connection->rollBack();
    throw $exception;
}

if ($status === 'active') {
    flash('success', $plan['name'] . ' plan activated.');
} else {
    flash('success', $plan['name'] . ' checkout request created. Connect a hosted payment provider to complete payment.');
}
redirect('subscription.php');

