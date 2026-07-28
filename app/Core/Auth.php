<?php
declare(strict_types=1);

final class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user']['id']);
    }

    public static function id(): ?int
    {
        return self::check() ? (int) $_SESSION['user']['id'] : null;
    }

    public static function user(): ?array
    {
        return self::check() ? $_SESSION['user'] : null;
    }

    public static function role(): string
    {
        return (string) ($_SESSION['user']['role'] ?? 'guest');
    }

    public static function attempt(string $email, string $password): bool
    {
        $statement = db()->prepare(
            'SELECT id, name, email, password_hash, role, status
             FROM users
             WHERE email = ?
             LIMIT 1'
        );
        $statement->execute([mb_strtolower(trim($email))]);
        $user = $statement->fetch();

        if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        self::setUser($user);

        $update = db()->prepare('UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?');
        $update->execute([(int) $user['id']]);
        return true;
    }

    public static function setUser(array $user): void
    {
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
        ];
    }

    public static function refresh(): void
    {
        if (!self::check()) {
            return;
        }

        $statement = db()->prepare('SELECT id, name, email, role FROM users WHERE id = ? AND status = "active"');
        $statement->execute([self::id()]);
        $user = $statement->fetch();
        if ($user) {
            self::setUser($user);
        } else {
            self::logout();
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $parameters = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $parameters['path'],
                $parameters['domain'],
                (bool) $parameters['secure'],
                (bool) $parameters['httponly']
            );
        }
        session_destroy();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            flash('warning', 'Please sign in to continue.');
            redirect('login.php');
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();
        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            flash('error', 'You do not have permission to access that page.');
            redirect('index.php');
        }
    }
}

