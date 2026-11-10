<?php

declare(strict_types=1);

use App\Services\CryptoService;

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

function makeTestKeyPair(string $directory, string $name): array
{
    $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
    if ($key === false) {
        throw new RuntimeException('Tidak dapat membuat RSA key pair untuk test.');
    }
    $private = '';
    if (!openssl_pkey_export($key, $private)) {
        throw new RuntimeException('Tidak dapat export test private key.');
    }
    $details = openssl_pkey_get_details($key);
    $public = $details['key'] ?? null;
    if (!is_string($public)) {
        throw new RuntimeException('Tidak dapat export test public key.');
    }
    $privatePath = $directory . '/' . $name . '-private.pem';
    $publicPath = $directory . '/' . $name . '-public.pem';
    file_put_contents($privatePath, $private);
    file_put_contents($publicPath, $public);
    return [$publicPath, $privatePath];
}

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function expectFailure(callable $callback, string $message): void
{
    try {
        $callback();
    } catch (Throwable) {
        return;
    }
    throw new RuntimeException($message);
}

$temp = sys_get_temp_dir() . '/simd-crypto-test-' . bin2hex(random_bytes(5));
if (!mkdir($temp, 0700, true)) {
    throw new RuntimeException('Tidak dapat membuat temporary directory.');
}

$passed = 0;
$failed = 0;
$tests = [];
try {
    [$publicPath, $privatePath] = makeTestKeyPair($temp, 'valid');
    [$wrongPublicPath, $wrongPrivatePath] = makeTestKeyPair($temp, 'wrong');
    $crypto = new CryptoService($publicPath, $privatePath);
    $wrongCrypto = new CryptoService($wrongPublicPath, $wrongPrivatePath);
    $id = '8c42cfca-1a98-4b25-a4bd-46e17eeb9d5c';
    $payload = [
        'title' => 'Dokumen Rahasia Pengujian',
        'category' => 'Internal',
        'description' => 'Deskripsi yang seharusnya tidak terbaca pada database.',
        'content' => 'ISI-RAHASIA-12345',
    ];
    $encrypted = $crypto->encryptDocument($payload, $id);
    $record = ['id' => $id] + $encrypted;

    $tests['round-trip AES-GCM + RSA-OAEP'] = function () use ($crypto, $record, $payload): void {
        assertTrue($crypto->decryptDocument($record) === $payload, 'Hasil dekripsi tidak sama dengan plaintext awal.');
    };
    $tests['ciphertext tidak memuat plaintext'] = function () use ($encrypted): void {
        assertTrue(!str_contains($encrypted['encrypted_payload'], 'ISI-RAHASIA-12345'), 'Plaintext ditemukan di ciphertext.');
        assertTrue(!str_contains($encrypted['encrypted_payload'], 'Dokumen Rahasia'), 'Judul plaintext ditemukan di ciphertext.');
    };
    $tests['perubahan ciphertext ditolak oleh GCM'] = function () use ($crypto, $record): void {
        $changed = $record;
        $raw = base64_decode($changed['encrypted_payload'], true);
        $raw[0] = chr(ord($raw[0]) ^ 1);
        $changed['encrypted_payload'] = base64_encode($raw);
        expectFailure(fn () => $crypto->decryptDocument($changed), 'Ciphertext yang dimodifikasi tidak ditolak.');
    };
    $tests['AAD mengikat ciphertext ke ID dokumen'] = function () use ($crypto, $record): void {
        $changed = $record;
        $changed['id'] = '72a5cfca-1a98-4b25-a4bd-46e17eeb9d5c';
        expectFailure(fn () => $crypto->decryptDocument($changed), 'ID dokumen berbeda tidak ditolak.');
    };
    $tests['private key yang salah tidak dapat membuka encrypted AES key'] = function () use ($wrongCrypto, $record): void {
        expectFailure(fn () => $wrongCrypto->decryptDocument($record), 'Private key yang salah berhasil mendekripsi dokumen.');
    };
    $tests['encryption version yang tidak dikenal ditolak'] = function () use ($crypto, $record): void {
        $changed = $record;
        $changed['encryption_version'] = 99;
        expectFailure(fn () => $crypto->decryptDocument($changed), 'Versi enkripsi yang tidak didukung tidak ditolak.');
    };

    foreach ($tests as $name => $test) {
        try {
            $test();
            $passed++;
            fwrite(STDOUT, "[PASS] {$name}\n");
        } catch (Throwable $exception) {
            $failed++;
            fwrite(STDOUT, "[FAIL] {$name}: {$exception->getMessage()}\n");
        }
    }
} finally {
    foreach (glob($temp . '/*') ?: [] as $file) {
        @unlink($file);
    }
    @rmdir($temp);
}

fwrite(STDOUT, PHP_EOL . "Hasil: {$passed} passed, {$failed} failed." . PHP_EOL);
exit($failed === 0 ? 0 : 1);
