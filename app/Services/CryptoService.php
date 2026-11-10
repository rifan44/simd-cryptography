<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\CryptoException;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use Throwable;

/**
 * Hybrid encryption service:
 * - AES-256-GCM encrypts the JSON document payload.
 * - RSA-OAEP SHA-256 encrypts the per-document AES key.
 */
final class CryptoService
{
    private const CIPHER = 'aes-256-gcm';
    private const KEY_LENGTH = 32;
    private const IV_LENGTH = 12;
    private const TAG_LENGTH = 16;
    private const VERSION = 1;

    public function __construct(
        private readonly string $publicKeyPath,
        private readonly string $privateKeyPath,
        private readonly string $privateKeyPassphrase = ''
    ) {
    }

    /** @param array{title:string,category:string,description:string,content:string} $payload */
    public function encryptDocument(array $payload, string $documentId): array
    {
        $aesKey = random_bytes(self::KEY_LENGTH);
        $iv = random_bytes(self::IV_LENGTH);
        $aad = $this->additionalAuthenticatedData($documentId);
        $plainText = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $tag = '';

        try {
            $cipherText = openssl_encrypt(
                $plainText,
                self::CIPHER,
                $aesKey,
                OPENSSL_RAW_DATA,
                $iv,
                $tag,
                $aad,
                self::TAG_LENGTH
            );

            if ($cipherText === false || strlen($tag) !== self::TAG_LENGTH) {
                throw new CryptoException('AES-256-GCM gagal mengenkripsi dokumen.');
            }

            $publicPem = $this->readKeyFile($this->publicKeyPath, 'public');
            $publicKey = PublicKeyLoader::loadPublicKey($publicPem);
            $rsa = $publicKey
                ->withPadding(RSA::ENCRYPTION_OAEP)
                ->withHash('sha256')
                ->withMGFHash('sha256');

            $wrappedKey = $rsa->encrypt($aesKey);
            if (!is_string($wrappedKey) || $wrappedKey === '') {
                throw new CryptoException('RSA-OAEP gagal mengenkripsi AES key.');
            }

            return [
                'encryption_version' => self::VERSION,
                'encrypted_payload' => base64_encode($cipherText),
                'iv' => base64_encode($iv),
                'auth_tag' => base64_encode($tag),
                'encrypted_key' => base64_encode($wrappedKey),
            ];
        } catch (CryptoException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new CryptoException('Proses enkripsi gagal. Periksa key pair dan konfigurasi kriptografi.', 0, $exception);
        } finally {
            if (function_exists('sodium_memzero')) {
                sodium_memzero($aesKey);
            }
        }
    }

    /** @return array{title:string,category:string,description:string,content:string} */
    public function decryptDocument(array $record): array
    {
        if ((int) ($record['encryption_version'] ?? 0) !== self::VERSION) {
            throw new CryptoException('Versi data terenkripsi tidak didukung.');
        }

        $cipherText = $this->decodeBase64((string) ($record['encrypted_payload'] ?? ''), 'ciphertext');
        $iv = $this->decodeBase64((string) ($record['iv'] ?? ''), 'IV');
        $tag = $this->decodeBase64((string) ($record['auth_tag'] ?? ''), 'authentication tag');
        $wrappedKey = $this->decodeBase64((string) ($record['encrypted_key'] ?? ''), 'encrypted AES key');

        if (strlen($iv) !== self::IV_LENGTH || strlen($tag) !== self::TAG_LENGTH) {
            throw new CryptoException('Panjang IV atau authentication tag tidak valid.');
        }

        try {
            $privatePem = $this->readKeyFile($this->privateKeyPath, 'private');
            $privateKey = PublicKeyLoader::loadPrivateKey($privatePem, $this->privateKeyPassphrase);
            $rsa = $privateKey
                ->withPadding(RSA::ENCRYPTION_OAEP)
                ->withHash('sha256')
                ->withMGFHash('sha256');

            $aesKey = $rsa->decrypt($wrappedKey);
            if (!is_string($aesKey) || strlen($aesKey) !== self::KEY_LENGTH) {
                throw new CryptoException('RSA tidak dapat membuka AES key.');
            }

            try {
                $plainText = openssl_decrypt(
                    $cipherText,
                    self::CIPHER,
                    $aesKey,
                    OPENSSL_RAW_DATA,
                    $iv,
                    $tag,
                    $this->additionalAuthenticatedData((string) ($record['id'] ?? ''))
                );
            } finally {
                if (function_exists('sodium_memzero')) {
                    sodium_memzero($aesKey);
                }
            }

            if ($plainText === false) {
                throw new CryptoException('Autentikasi AES-GCM gagal. Ciphertext, tag, atau identitas dokumen mungkin telah berubah.');
            }

            $payload = json_decode($plainText, true, 512, JSON_THROW_ON_ERROR);
            foreach (['title', 'category', 'description', 'content'] as $field) {
                if (!isset($payload[$field]) || !is_string($payload[$field])) {
                    throw new CryptoException('Struktur payload dokumen tidak valid.');
                }
            }

            return $payload;
        } catch (CryptoException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new CryptoException('Proses dekripsi gagal. Periksa private key, passphrase, dan integritas data.', 0, $exception);
        }
    }

    private function readKeyFile(string $path, string $type): string
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new CryptoException("File {$type} key tidak ditemukan atau tidak dapat dibaca: {$path}");
        }
        $contents = file_get_contents($path);
        if (!is_string($contents) || trim($contents) === '') {
            throw new CryptoException("File {$type} key kosong atau tidak valid.");
        }
        return $contents;
    }

    private function decodeBase64(string $value, string $label): string
    {
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            throw new CryptoException("Format base64 untuk {$label} tidak valid.");
        }
        return $decoded;
    }

    private function additionalAuthenticatedData(string $documentId): string
    {
        return 'SIMD-DOCUMENT:v1:' . $documentId;
    }
}
