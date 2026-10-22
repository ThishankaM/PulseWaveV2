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

function icon(string $name, string $className = ''): string
{
    $paths = [
        'home' => '<path d="M3 10.8 12 3l9 7.8"/><path d="M5.5 9.5V21h13V9.5"/><path d="M9.5 21v-6h5v6"/>',
        'search' => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
        'compass' => '<circle cx="12" cy="12" r="9"/><path d="m15.8 8.2-2.1 5.5-5.5 2.1 2.1-5.5 5.5-2.1Z"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'library' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/><path d="M8 7h8M8 11h6"/>',
        'sparkles' => '<path d="m12 3-1.2 3.8L7 8l3.8 1.2L12 13l1.2-3.8L17 8l-3.8-1.2L12 3Z"/><path d="m5 14-.8 2.2L2 17l2.2.8L5 20l.8-2.2L8 17l-2.2-.8L5 14Z"/><path d="m19 13-.7 2.3L16 16l2.3.7L19 19l.7-2.3L22 16l-2.3-.7L19 13Z"/>',
        'heart' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/>',
        'play' => '<path class="icon-fill" d="m8 5 11 7-11 7V5Z"/>',
        'pause' => '<path d="M8 5v14M16 5v14"/>',
        'previous' => '<path class="icon-fill" d="M6 5h2v14H6zM18 5v14L9 12l9-7Z"/>',
        'next' => '<path class="icon-fill" d="M16 5h2v14h-2zM6 5v14l9-7L6 5Z"/>',
        'shuffle' => '<path d="M16 3h5v5"/><path d="M4 20 21 3"/><path d="M21 16v5h-5"/><path d="m15 15 6 6"/><path d="M4 4l5 5"/>',
        'repeat' => '<path d="m17 2 4 4-4 4"/><path d="M3 11V9a3 3 0 0 1 3-3h15"/><path d="m7 22-4-4 4-4"/><path d="M21 13v2a3 3 0 0 1-3 3H3"/>',
        'volume' => '<path d="M11 5 6 9H2v6h4l5 4V5Z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M18.5 5.5a9 9 0 0 1 0 13"/>',
        'volume-mute' => '<path d="M11 5 6 9H2v6h4l5 4V5Z"/><path d="m16 10 5 5M21 10l-5 5"/>',
        'queue' => '<path d="M4 6h12M4 12h12M4 18h8"/><path d="m18 15 3 2-3 2v-4Z" class="icon-fill"/>',
        'device' => '<rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>',
        'expand' => '<path d="M8 3H3v5M16 3h5v5M8 21H3v-5M21 16v5h-5"/>',
        'collapse' => '<path d="M3 8h5V3M21 8h-5V3M3 16h5v5M21 16h-5v5"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'disc' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="2.5"/><path d="M12 3a9 9 0 0 1 9 9"/>',
        'more' => '<circle cx="5" cy="12" r="1" class="icon-fill"/><circle cx="12" cy="12" r="1" class="icon-fill"/><circle cx="19" cy="12" r="1" class="icon-fill"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-1.6v-.2h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1Z"/>',
        'logout' => '<path d="M10 17l5-5-5-5M15 12H3"/><path d="M14 3h6a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-6"/>',
        'upload' => '<path d="M12 16V4M7 9l5-5 5 5"/><path d="M4 20h16"/>',
        'music' => '<path d="M9 18V5l10-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="16" cy="16" r="3"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'trash' => '<path d="M4 7h16M9 7V4h6v3M6 7l1 14h10l1-14M10 11v6M14 11v6"/>',
        'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-right' => '<path d="m9 6 6 6-6 6"/>',
    ];

    $safeName = isset($paths[$name]) ? $name : 'music';
    $classes = trim('icon icon-' . $safeName . ' ' . $className);
    return '<svg class="' . e($classes) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[$safeName] . '</svg>';
}

function brand_mark(string $className = ''): string
{
    return '<span class="brand-mark ' . e($className) . '" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M4 17c2.7 0 2.7-7 5.4-7s2.7 12 5.4 12 2.7-16 5.4-16 2.7 11 8 11" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/></svg></span>';
}

function render_track_card(array $track, bool $showArtist = true): void
{
    $trackId = (int) ($track['id'] ?? 0);
    $liked = (bool) ($track['is_liked'] ?? false);
    $duration = (int) ($track['duration_seconds'] ?? 0);
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
            <p>
                <?= format_count($track['play_count'] ?? 0) ?> plays
                <?php if ($duration > 0): ?><span aria-hidden="true"> / </span><?= floor($duration / 60) . ':' . str_pad((string) ($duration % 60), 2, '0', STR_PAD_LEFT) ?><?php endif; ?>
            </p>
        </div>
        <?php if (Auth::check()): ?>
            <form method="post" action="<?= e(url('actions/toggle-like.php')) ?>" class="icon-form" data-like-form data-track-id="<?= $trackId ?>">
                <?= csrf_input() ?>
                <input type="hidden" name="track_id" value="<?= $trackId ?>">
                <button class="icon-button like-button <?= $liked ? 'is-active' : '' ?>" type="submit" aria-label="<?= $liked ? 'Remove from liked songs' : 'Add to liked songs' ?>" aria-pressed="<?= $liked ? 'true' : 'false' ?>">
                    <?= icon('heart') ?>
                </button>
            </form>
        <?php endif; ?>
    </article>
    <?php
}

function render_release_card(array $release): void
{
    $trackId = (int) ($release['id'] ?? 0);
    $trackCount = (int) ($release['track_count'] ?? 1);
    ?>
    <a class="release-card" href="<?= e(url('release.php?track=' . $trackId)) ?>">
        <span class="release-art">
            <img src="<?= e(media_url($release['cover_path'] ?? null)) ?>" alt="" loading="lazy">
            <span class="release-play" aria-hidden="true"><?= icon('arrow-right') ?></span>
        </span>
        <span class="release-copy">
            <strong><?= e($release['title'] ?? 'Untitled release') ?></strong>
            <small><?= e($release['artist_name'] ?? 'Unknown artist') ?></small>
            <span><?= $trackCount ?> song<?= $trackCount === 1 ? '' : 's' ?><?= !empty($release['release_year']) ? ' / ' . (int) $release['release_year'] : '' ?></span>
        </span>
    </a>
    <?php
}

