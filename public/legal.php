<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$document = ($_GET['doc'] ?? '') === 'terms' ? 'terms' : 'privacy';
$isPrivacy = $document === 'privacy';
$pageTitle = $isPrivacy ? 'Privacy policy' : 'Terms of use';
$pageDescription = 'PulseWave ' . ($isPrivacy ? 'privacy policy.' : 'terms of use.');
require APP_PATH . '/Views/partials/header-v3.php';
?>

<article class="legal-page section-shell">
    <span class="eyebrow">PulseWave policies</span>
    <h1><?= $isPrivacy ? 'Privacy policy' : 'Terms of use' ?></h1>
    <p class="legal-updated">Template last updated <?= date('F Y') ?></p>

    <?php if ($isPrivacy): ?>
        <h2>Information collected</h2>
        <p>PulseWave stores account details, playlists, likes, follows, listening history and subscription status required to provide the service.</p>
        <h2>How information is used</h2>
        <p>Information is used to authenticate users, personalize libraries, maintain the catalog and improve application reliability.</p>
        <h2>Payments</h2>
        <p>Card numbers and CVV values must never be stored by PulseWave. Production payments should use a compliant hosted checkout provider.</p>
        <h2>Your choices</h2>
        <p>Users can update profile details and remove playlists, likes and follows. Requests concerning account deletion or data access can be sent to support@pulsewave.local.</p>
    <?php else: ?>
        <h2>Acceptable use</h2>
        <p>Users may not upload harmful files, attempt unauthorized access, interfere with other accounts or distribute media without the necessary rights.</p>
        <h2>Catalog rights</h2>
        <p>Administrators and artists are responsible for ensuring uploaded audio and artwork are licensed for use.</p>
        <h2>Subscriptions</h2>
        <p>Paid plans become active when a payment provider confirms checkout. Plan prices, device limits and features are shown before purchase.</p>
        <h2>Availability</h2>
        <p>PulseWave aims to provide reliable access to the service. Planned maintenance and material outages will be communicated through appropriate service channels.</p>
    <?php endif; ?>
</article>

<?php require APP_PATH . '/Views/partials/footer-v3.php'; ?>

