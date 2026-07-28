<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');

require_once APP_PATH . '/Support/helpers.php';
load_env(ROOT_PATH . '/.env');

date_default_timezone_set((string) env('APP_TIMEZONE', 'Asia/Colombo'));

$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_name((string) env('SESSION_NAME', 'pulsewave_session'));
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once APP_PATH . '/Core/Database.php';
require_once APP_PATH . '/Core/Auth.php';
require_once APP_PATH . '/Core/Csrf.php';
require_once APP_PATH . '/Repositories/CatalogRepository.php';
require_once APP_PATH . '/Repositories/LibraryRepository.php';

$GLOBALS['old_input'] = $_SESSION['_old_input'] ?? [];
unset($_SESSION['_old_input']);

$environment = (string) env('APP_ENV', 'production');
ini_set('display_errors', $environment === 'local' ? '1' : '0');
error_reporting(E_ALL);

set_exception_handler(static function (Throwable $exception) use ($environment): void {
    $message = sprintf(
        "[%s] %s in %s:%d\n%s\n\n",
        date('c'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    $logFile = STORAGE_PATH . '/logs/app.log';
    if (is_dir(dirname($logFile)) && is_writable(dirname($logFile))) {
        error_log($message, 3, $logFile);
    }

    http_response_code(500);
    if ($environment === 'local') {
        echo '<h1>Application error</h1><pre>' . e($exception->getMessage()) . '</pre>';
    } else {
        echo '<h1>Something went wrong</h1><p>Please try again later.</p>';
    }
});

