#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Core\Database;
use App\Support\Env;

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
Env::load($root . '/.env');
$config = require $root . '/config/app.php';
$pdo = Database::connect($config['database']);

fwrite(STDOUT, 'Username admin: ');
$username = trim((string) fgets(STDIN));
if ($username === '' || strlen($username) > 60 || !preg_match('/^[A-Za-z0-9_.-]+$/', $username)) {
    fwrite(STDERR, "Username wajib 1-60 karakter dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.\n");
    exit(1);
}

function readHidden(string $prompt): string
{
    fwrite(STDOUT, $prompt);
    $isTty = function_exists('stream_isatty') && stream_isatty(STDIN);
    if ($isTty && function_exists('shell_exec')) {
        shell_exec('stty -echo');
    }
    $value = rtrim((string) fgets(STDIN), "\r\n");
    if ($isTty && function_exists('shell_exec')) {
        shell_exec('stty echo');
        fwrite(STDOUT, PHP_EOL);
    }
    return $value;
}

$password = readHidden('Password admin (minimal 12 karakter): ');
$confirmation = readHidden('Ulangi password: ');
if (strlen($password) < 12 || !hash_equals($password, $confirmation)) {
    fwrite(STDERR, "Password minimal 12 karakter dan kedua input harus sama.\n");
    exit(1);
}

$statement = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (:username, :hash)');
try {
    $statement->execute([
        'username' => $username,
        'hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);
    fwrite(STDOUT, "Admin berhasil dibuat. Login menggunakan username tersebut.\n");
} catch (PDOException $exception) {
    fwrite(STDERR, "Gagal membuat admin. Username mungkin sudah digunakan atau database bermasalah.\n");
    exit(1);
} finally {
    if (function_exists('sodium_memzero')) {
        sodium_memzero($password);
        sodium_memzero($confirmation);
    }
}
