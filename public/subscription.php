<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$plans = db()->query(
    'SELECT id, name, slug, price_monthly, max_devices, audio_quality, features
     FROM subscription_plans
     WHERE is_active = 1
     ORDER BY price_monthly'
)->fetchAll();

$currentPlanId = 0;
if (Auth::check()) {
    $statement = db()->prepare(
        'SELECT plan_id FROM subscriptions
         WHERE user_id = ? AND status IN ("active", "pending")
         ORDER BY created_at DESC LIMIT 1'
    );
    $statement->execute([Auth::id()]);
    $currentPlanId = (int) ($statement->fetchColumn() ?: 0);
}

$pageTitle = 'Upgrade';
$pageDescription = 'Compare PulseWave subscription plans.';
$activeNav = 'upgrade';
require APP_PATH . '/Views/partials/header.php';
?>

<section class="page-hero section-shell centered-hero">
    <span class="eyebrow">Listen your way</span>
    <h1>Simple plans. More music.</h1>
    <p>Choose the experience that fits you. Paid plans create a checkout request without storing card details.</p>
</section>

<section class="content-section section-shell">
    <div class="pricing-grid">
        <?php foreach ($plans as $index => $plan): ?>
            <?php $features = json_decode($plan['features'], true) ?: []; ?>
            <article class="pricing-card <?= $index === 2 ? 'featured' : '' ?>">
                <?php if ($index === 2): ?><span class="popular-badge">Most popular</span><?php endif; ?>
                <span class="eyebrow"><?= e($plan['audio_quality']) ?></span>
                <h2><?= e($plan['name']) ?></h2>
                <div class="price">
                    <strong>$<?= e(number_format((float) $plan['price_monthly'], 2)) ?></strong>
                    <span>/ month</span>
                </div>
                <ul>
                    <?php foreach ($features as $feature): ?>
                        <li><span aria-hidden="true">✓</span><?= e($feature) ?></li>
                    <?php endforeach; ?>
                    <li><span aria-hidden="true">✓</span>Up to <?= (int) $plan['max_devices'] ?> device<?= (int) $plan['max_devices'] === 1 ? '' : 's' ?></li>
                </ul>
                <?php if ($currentPlanId === (int) $plan['id']): ?>
                    <button class="button button-ghost button-wide" type="button" disabled>Current plan</button>
                <?php elseif (Auth::check()): ?>
                    <form method="post" action="<?= e(url('actions/subscription.php')) ?>">
                        <?= csrf_input() ?>
                        <input type="hidden" name="plan_id" value="<?= (int) $plan['id'] ?>">
                        <button class="button <?= $index === 2 ? 'button-primary' : 'button-ghost' ?> button-wide" type="submit">
                            Choose <?= e($plan['name']) ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a class="button <?= $index === 2 ? 'button-primary' : 'button-ghost' ?> button-wide" href="<?= e(url('login.php')) ?>">Sign in to choose</a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
    <p class="payment-note">For production, connect the pending checkout request to a PCI-compliant hosted payment provider. PulseWave never asks this form for a card number or CVV.</p>
</section>

<?php require APP_PATH . '/Views/partials/footer.php'; ?>

