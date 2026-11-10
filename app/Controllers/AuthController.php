<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\UserRepository;

final class AuthController
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function showLogin(): void
    {
        if (!empty($_SESSION['user']['id'])) {
            \redirect('documents');
        }
        \render('auth/login', ['title' => 'Masuk Admin']);
    }

    public function login(): void
    {
        \verify_csrf();

        $now = time();
        $attempts = array_values(array_filter(
            $_SESSION['_login_attempts'] ?? [],
            static fn ($timestamp): bool => is_int($timestamp) && $timestamp > ($now - 900)
        ));
        if (count($attempts) >= 8) {
            $_SESSION['_login_attempts'] = $attempts;
            \flash('danger', 'Terlalu banyak percobaan login. Coba kembali setelah 15 menit.');
            \redirect('login');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $user = $username !== '' ? $this->users->findByUsername($username) : null;
        $hash = $user['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llB6z7e9r5uR0n2f6v6e';
        $valid = password_verify($password, $hash) && $user !== null;

        if (!$valid) {
            $attempts[] = $now;
            $_SESSION['_login_attempts'] = $attempts;
            \flash('danger', 'Username atau password tidak sesuai.');
            \redirect('login');
        }

        session_regenerate_id(true);
        $_SESSION['_login_attempts'] = [];
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'username' => (string) $user['username'],
        ];

        \flash('success', 'Login berhasil. Selamat datang kembali.');
        \redirect('documents');
    }

    public function logout(): void
    {
        \verify_csrf();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
        \redirect('login');
    }
}
