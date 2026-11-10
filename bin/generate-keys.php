#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Support\Env;

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
Env::load($root . '/.env');

$keyDirValue = Env::get('RSA_KEY_DIR', 'storage/keys');
$keyDir = str_starts_with((string) $keyDirValue, '/') ? $keyDirValue : $root . '/' . $keyDirValue;
$passphrase = Env::get('RSA_PRIVATE_KEY_PASSPHRASE', '');

if (strlen((string) $passphrase) < 12) {
    fwrite(STDERR, "Atur RSA_PRIVATE_KEY_PASSPHRASE di .env dengan minimal 12 karakter sebelum membuat key.\n");
    exit(1);
}

if (!is_dir($keyDir) && !mkdir($keyDir, 0700, true) && !is_dir($keyDir)) {
    fwrite(STDERR, "Tidak dapat membuat direktori key.\n");
    exit(1);
}

$options = [
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'private_key_bits' => 3072,
];
$privateKey = openssl_pkey_new($options);
if ($privateKey === false) {
    fwrite(STDERR, "Pembuatan RSA key pair gagal: " . (openssl_error_string() ?: 'OpenSSL error') . "\n");
    exit(1);
}

$privatePem = '';
if (!openssl_pkey_export($privateKey, $privatePem, (string) $passphrase)) {
    fwrite(STDERR, "Export private key gagal.\n");
    exit(1);
}
$details = openssl_pkey_get_details($privateKey);
$publicPem = $details['key'] ?? null;
if (!is_string($publicPem)) {
    fwrite(STDERR, "Export public key gagal.\n");
    exit(1);
}

$privatePath = $keyDir . '/private.pem';
$publicPath = $keyDir . '/public.pem';
if (is_file($privatePath) || is_file($publicPath)) {
    fwrite(STDERR, "Key sudah ada. Script tidak menimpa key yang ada. Backup dan rotasi key harus dilakukan secara terencana.\n");
    exit(1);
}

if (file_put_contents($privatePath, $privatePem, LOCK_EX) === false
    || file_put_contents($publicPath, $publicPem, LOCK_EX) === false) {
    @unlink($privatePath);
    @unlink($publicPath);
    fwrite(STDERR, "Penulisan key gagal.\n");
    exit(1);
}
chmod($keyDir, 0700);
chmod($privatePath, 0600);
chmod($publicPath, 0644);

fwrite(STDOUT, "RSA-3072 key pair berhasil dibuat.\n");
fwrite(STDOUT, "Private key: {$privatePath} (terenkripsi dengan passphrase, permission 0600)\n");
fwrite(STDOUT, "Public key : {$publicPath}\n");
fwrite(STDOUT, "Jangan unggah private.pem atau passphrase ke repository. Simpan backup key secara aman.\n");
