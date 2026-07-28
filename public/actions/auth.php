<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}
verify_csrf();

$operation = (string) ($_POST['operation'] ?? '');
$email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['password'] ?? '');
remember_input($_POST);

if ($operation === 'signup') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $confirmation = (string) ($_POST['confirm_password'] ?? '');
    $errors = [];

    if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
        $errors[] = 'Display name must contain between 2 and 80 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must contain at least 8 characters.';
    }
    if ($password !== $confirmation) {
        $errors[] = 'Password confirmation does not match.';
    }

    $existing = db()->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
    $existing->execute([$email]);
    if ($existing->fetchColumn()) {
        $errors[] = 'An account already exists for this email address.';
    }

    if ($errors) {
        foreach ($errors as $error) {
            flash('error', $error);
        }
        redirect('login.php?mode=signup');
    }

    $connection = db();
    $connection->beginTransaction();
    try {
        $statement = $connection->prepare(
            'INSERT INTO users (name, email, password_hash, role, status)
             VALUES (?, ?, ?, "user", "active")'
        );
        $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        $userId = (int) $connection->lastInsertId();

        $planId = (int) $connection->query('SELECT id FROM subscription_plans WHERE slug = "free" LIMIT 1')->fetchColumn();
        if ($planId > 0) {
            $subscription = $connection->prepare(
                'INSERT INTO subscriptions (user_id, plan_id, status, starts_at)
                 VALUES (?, ?, "active", CURRENT_TIMESTAMP)'
            );
            $subscription->execute([$userId, $planId]);
        }
        $connection->commit();

        Auth::setUser(['id' => $userId, 'name' => $name, 'email' => $email, 'role' => 'user']);
        unset($_SESSION['_old_input']);
        flash('success', 'Welcome to PulseWave. Your account is ready.');
        redirect('index.php');
    } catch (Throwable $exception) {
        $connection->rollBack();
        throw $exception;
    }
}

if ($operation === 'signin') {
    $window = 300;
    $maximumAttempts = 5;
    $attempts = $_SESSION['_login_attempts'] ?? ['count' => 0, 'started' => time()];
    if (time() - (int) $attempts['started'] > $window) {
        $attempts = ['count' => 0, 'started' => time()];
    }
    if ((int) $attempts['count'] >= $maximumAttempts) {
        flash('error', 'Too many sign-in attempts. Please wait five minutes and try again.');
        redirect('login.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        flash('error', 'Enter a valid email address and password.');
        redirect('login.php');
    }

    if (Auth::attempt($email, $password)) {
        unset($_SESSION['_login_attempts'], $_SESSION['_old_input']);
        flash('success', 'Welcome back, ' . Auth::user()['name'] . '.');
        redirect(in_array(Auth::role(), ['artist', 'admin'], true) ? 'dashboard.php' : 'index.php');
    }

    $attempts['count'] = (int) $attempts['count'] + 1;
    $_SESSION['_login_attempts'] = $attempts;
    flash('error', 'The email address or password is incorrect.');
    redirect('login.php');
}

flash('error', 'Invalid authentication request.');
redirect('login.php');

