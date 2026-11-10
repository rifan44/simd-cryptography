<?php

declare(strict_types=1);

use App\Support\Env;

$root = dirname(__DIR__);
$resolvePath = static function (string $value) use ($root): string {
    if ($value === '') {
        return $value;
    }
    if ($value[0] === DIRECTORY_SEPARATOR || preg_match('/^[A-Za-z]:[\\\\\/]/', $value)) {
        return $value;
    }
    return $root . '/' . $value;
};

return [
    'name' => Env::get('APP_NAME', 'SIMD Cryptography'),
    'environment' => Env::get('APP_ENV', 'production'),
    'debug' => filter_var(Env::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL),
    'timezone' => Env::get('APP_TIMEZONE', 'Asia/Jakarta'),
    'base_url' => rtrim(Env::get('APP_BASE_URL', ''), '/'),
    'session_secure_cookie' => filter_var(Env::get('SESSION_SECURE_COOKIE', 'false'), FILTER_VALIDATE_BOOL),
    'rsa_public_key_path' => $resolvePath(Env::get('RSA_PUBLIC_KEY_PATH', 'storage/keys/public.pem')),
    'rsa_private_key_path' => $resolvePath(Env::get('RSA_PRIVATE_KEY_PATH', 'storage/keys/private.pem')),
    'rsa_private_key_passphrase' => Env::get('RSA_PRIVATE_KEY_PASSPHRASE', ''),
    'database' => [
        'host' => Env::get('DB_HOST', '127.0.0.1'),
        'port' => Env::get('DB_PORT', '3306'),
        'database' => Env::get('DB_DATABASE', 'simd_cryptography'),
        'username' => Env::get('DB_USERNAME', 'root'),
        'password' => Env::get('DB_PASSWORD', ''),
        'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
    ],
];
