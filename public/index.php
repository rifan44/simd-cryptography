<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DocumentController;
use App\Middleware\AuthMiddleware;
use App\Repositories\DocumentRepository;
use App\Repositories\UserRepository;
use App\Services\CryptoService;

try {
    require dirname(__DIR__) . '/bootstrap/app.php';
} catch (Throwable $bootstrapException) {
    error_log('[SIMD] Bootstrap error: ' . $bootstrapException->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Aplikasi belum siap. Periksa konfigurasi .env, dependency Composer, dan koneksi MySQL.';
    exit;
}

$auth = new AuthController(new UserRepository($pdo));
$documents = new DocumentController(
    new DocumentRepository($pdo),
    new CryptoService(
        $config['rsa_public_key_path'],
        $config['rsa_private_key_path'],
        $config['rsa_private_key_passphrase']
    )
);

$route = (string) ($_GET['r'] ?? 'documents');
$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

try {
    if ($route === 'login') {
        if ($method === 'POST') {
            $auth->login();
        }
        $auth->showLogin();
        exit;
    }

    if ($route === 'logout' && $method === 'POST') {
        AuthMiddleware::enforce();
        $auth->logout();
    }

    AuthMiddleware::enforce();

    switch ($route) {
        case 'documents':
            $documents->index();
            break;
        case 'documents/create':
            $documents->create();
            break;
        case 'documents/store':
            $method === 'POST' ? $documents->store() : redirect('documents');
            break;
        case 'documents/show':
            $documents->show((string) ($_GET['id'] ?? ''));
            break;
        case 'documents/edit':
            $documents->edit((string) ($_GET['id'] ?? ''));
            break;
        case 'documents/update':
            $method === 'POST' ? $documents->update((string) ($_POST['id'] ?? '')) : redirect('documents');
            break;
        case 'documents/delete':
            $method === 'POST' ? $documents->delete((string) ($_POST['id'] ?? '')) : redirect('documents');
            break;
        case 'documents/security':
            $documents->security((string) ($_GET['id'] ?? ''));
            break;
        default:
            http_response_code(404);
            render('errors/404', ['title' => 'Halaman tidak ditemukan']);
    }
} catch (Throwable $exception) {
    error_log('[SIMD] Unhandled error: ' . $exception->getMessage());
    http_response_code(500);
    if ($config['debug']) {
        render('errors/500', ['title' => 'Terjadi kesalahan', 'debugMessage' => $exception->getMessage()]);
    } else {
        render('errors/500', ['title' => 'Terjadi kesalahan', 'debugMessage' => null]);
    }
}
