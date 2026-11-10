<?php

declare(strict_types=1);

use App\Core\Database;
use App\Support\Env;

$projectRoot = dirname(__DIR__);

if (!is_file($projectRoot . '/vendor/autoload.php')) {
    http_response_code(500);
    exit('Dependency belum terpasang. Jalankan: composer install');
}

require $projectRoot . '/vendor/autoload.php';
Env::load($projectRoot . '/.env');

$config = require $projectRoot . '/config/app.php';

if (PHP_SAPI !== 'cli') {
    date_default_timezone_set($config['timezone']);
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('SIMDSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $config['session_secure_cookie'] || $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

$pdo = Database::connect($config['database']);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $route = 'documents', array $params = []): string
{
    global $config;
    $query = http_build_query(array_merge(['r' => $route], $params));
    return ($config['base_url'] !== '' ? $config['base_url'] : '') . '/index.php?' . $query;
}

function redirect(string $route = 'documents', array $params = []): never
{
    header('Location: ' . url($route, $params));
    exit;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return is_string($value) ? $value : null;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['_csrf'] ?? '';
    $known = $_SESSION['_csrf'] ?? '';
    if (!is_string($sent) || !is_string($known) || $known === '' || !hash_equals($known, $sent)) {
        http_response_code(419);
        exit('Permintaan tidak valid atau sesi telah kedaluwarsa. Muat ulang halaman dan coba kembali.');
    }
}

function render(string $view, array $data = []): void
{
    global $projectRoot, $config;
    extract($data, EXTR_SKIP);
    $viewFile = $projectRoot . '/resources/views/' . $view . '.php';
    if (!is_file($viewFile)) {
        throw new RuntimeException('View tidak ditemukan.');
    }
    ob_start();
    require $viewFile;
    $content = (string) ob_get_clean();
    require $projectRoot . '/resources/views/layout.php';
}
