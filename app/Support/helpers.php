<?php
declare(strict_types=1);

function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        if ($key !== '' && !array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }
    }
}

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    return ($value === false || $value === '') ? $default : $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_url(): string
{
    static $base;
    if ($base !== null) {
        return $base;
    }

    $configured = trim((string) env('APP_URL', ''));
    if ($configured !== '') {
        return $base = rtrim($configured, '/');
    }

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    foreach (['/actions/', '/api/'] as $segment) {
        $position = strpos($script, $segment);
        if ($position !== false) {
            return $base = rtrim(substr($script, 0, $position), '/');
        }
    }

    return $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
}

function url(string $path = ''): string
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    return base_url() . '/' . ltrim($path, '/');
}

function media_url(?string $path, string $fallback = 'uploads/album_art/default-album-art.jpg'): string
{
    $path = trim((string) $path);
    return url($path !== '' ? $path : $fallback);
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function redirect_back(string $fallback = 'index.php'): never
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer !== '') {
        $parts = parse_url($referer);
        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
        if (($parts['host'] ?? $currentHost) === $currentHost && isset($parts['path'])) {
            $target = $parts['path'] . (isset($parts['query']) ? '?' . $parts['query'] : '');
            header('Location: ' . $target);
            exit;
        }
    }

    redirect($fallback);
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return is_array($messages) ? $messages : [];
}

function remember_input(array $input): void
{
    unset($input['password'], $input['confirm_password'], $input['_token']);
    $_SESSION['_old_input'] = $input;
}

function old(string $key, string $default = ''): string
{
    return (string) (($GLOBALS['old_input'][$key] ?? $default));
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function request_int(string $key, int $default = 0): int
{
    $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
    return $value === false || $value === null ? $default : $value;
}

function post_int(string $key, int $default = 0): int
{
    $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT);
    return $value === false || $value === null ? $default : $value;
}

function format_count(int|string|null $count): string
{
    $number = (int) $count;
    if ($number >= 1_000_000) {
        return rtrim(rtrim(number_format($number / 1_000_000, 1), '0'), '.') . 'M';
    }
    if ($number >= 1_000) {
        return rtrim(rtrim(number_format($number / 1_000, 1), '0'), '.') . 'K';
    }
    return (string) $number;
}

function render_track_card(array $track, bool $showArtist = true): void
{
    $trackId = (int) ($track['id'] ?? 0);
    $liked = (bool) ($track['is_liked'] ?? false);
    ?>
    <article class="track-card">
        <button class="cover-button" type="button" data-play-track="<?= $trackId ?>" aria-label="Play <?= e($track['title'] ?? 'track') ?>">
            <img src="<?= e(media_url($track['cover_path'] ?? null)) ?>" alt="" loading="lazy">
            <span class="cover-play" aria-hidden="true">▶</span>
        </button>
        <div class="track-card-copy">
            <h3><?= e($track['title'] ?? 'Untitled') ?></h3>
            <?php if ($showArtist): ?>
                <a href="<?= e(url('artist.php?id=' . (int) ($track['artist_id'] ?? 0))) ?>">
                    <?= e($track['artist_name'] ?? 'Unknown artist') ?>
                </a>
            <?php endif; ?>
            <p><?= format_count($track['play_count'] ?? 0) ?> plays</p>
        </div>
        <?php if (Auth::check()): ?>
            <form method="post" action="<?= e(url('actions/toggle-like.php')) ?>" class="icon-form">
                <?= csrf_input() ?>
                <input type="hidden" name="track_id" value="<?= $trackId ?>">
                <button class="icon-button <?= $liked ? 'is-active' : '' ?>" type="submit" aria-label="<?= $liked ? 'Remove from liked songs' : 'Add to liked songs' ?>">
                    <?= $liked ? '♥' : '♡' ?>
                </button>
            </form>
        <?php endif; ?>
    </article>
    <?php
}

