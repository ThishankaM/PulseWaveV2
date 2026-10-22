<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
if (Auth::check()) {
    redirect('profile.php');
}

$mode = ($_GET['mode'] ?? '') === 'signup' ? 'signup' : 'signin';
$pageTitle = $mode === 'signup' ? 'Create account' : 'Sign in';
$pageDescription = 'Sign in to PulseWave or create a free account.';
$bodyClass = 'auth-page';
require APP_PATH . '/Views/partials/header-v3.php';
?>

<section class="auth-shell section-shell" data-auth-shell data-mode="<?= e($mode) ?>">
    <div class="auth-promo">
        <span class="eyebrow">Welcome to PulseWave</span>
        <h1>Let the next track find you.</h1>
        <p>Create playlists, follow artists and save every song you love.</p>
        <div class="auth-art-grid">
            <img src="<?= e(url('uploads/album_art/apt.jpg')) ?>" alt="">
            <img src="<?= e(url('uploads/album_art/Adamas.jpg')) ?>" alt="">
            <img src="<?= e(url('uploads/album_art/GOTMain1.jpg')) ?>" alt="">
            <img src="<?= e(url('uploads/album_art/Magnetic.jpg')) ?>" alt="">
        </div>
    </div>

    <div class="auth-card">
        <div class="auth-tabs" role="tablist">
            <button type="button" role="tab" data-auth-tab="signin" aria-selected="<?= $mode === 'signin' ? 'true' : 'false' ?>">Sign in</button>
            <button type="button" role="tab" data-auth-tab="signup" aria-selected="<?= $mode === 'signup' ? 'true' : 'false' ?>">Create account</button>
        </div>

        <div class="auth-panel" data-auth-panel="signin" <?= $mode !== 'signin' ? 'hidden' : '' ?>>
            <span class="eyebrow">Good to see you</span>
            <h2>Sign in</h2>
            <p>Continue to your playlists and favorites.</p>
            <form class="stack-form" method="post" action="<?= e(url('actions/auth.php')) ?>">
                <?= csrf_input() ?>
                <input type="hidden" name="operation" value="signin">
                <label>
                    <span>Email address</span>
                    <input type="email" name="email" value="<?= e(old('email')) ?>" maxlength="190" autocomplete="email" required>
                </label>
                <label>
                    <span>Password</span>
                    <span class="password-field">
                        <input type="password" name="password" minlength="8" autocomplete="current-password" required>
                        <button type="button" data-password-toggle aria-label="Show password">Show</button>
                    </span>
                </label>
                <button class="button button-primary button-wide" type="submit">Sign in</button>
            </form>
        </div>

        <div class="auth-panel" data-auth-panel="signup" <?= $mode !== 'signup' ? 'hidden' : '' ?>>
            <span class="eyebrow">Start listening</span>
            <h2>Create your account</h2>
            <p>A free account gives you playlists, likes and follows.</p>
            <form class="stack-form" method="post" action="<?= e(url('actions/auth.php')) ?>">
                <?= csrf_input() ?>
                <input type="hidden" name="operation" value="signup">
                <label>
                    <span>Display name</span>
                    <input type="text" name="name" value="<?= e(old('name')) ?>" minlength="2" maxlength="80" autocomplete="name" required>
                </label>
                <label>
                    <span>Email address</span>
                    <input type="email" name="email" value="<?= e(old('email')) ?>" maxlength="190" autocomplete="email" required>
                </label>
                <label>
                    <span>Password</span>
                    <span class="password-field">
                        <input type="password" name="password" minlength="8" autocomplete="new-password" required>
                        <button type="button" data-password-toggle aria-label="Show password">Show</button>
                    </span>
                    <small>Use at least 8 characters.</small>
                </label>
                <label>
                    <span>Confirm password</span>
                    <input type="password" name="confirm_password" minlength="8" autocomplete="new-password" required>
                </label>
                <button class="button button-primary button-wide" type="submit">Create account</button>
            </form>
        </div>
    </div>
</section>

<?php require APP_PATH . '/Views/partials/footer-v3.php'; ?>

