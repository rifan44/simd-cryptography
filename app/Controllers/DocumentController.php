<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\DocumentRepository;
use App\Services\CryptoService;
use App\Support\CryptoException;
use Throwable;

final class DocumentController
{
    public function __construct(
        private readonly DocumentRepository $documents,
        private readonly CryptoService $crypto
    ) {
    }

    public function index(): void
    {
        $items = [];
        foreach ($this->documents->all() as $record) {
            try {
                $items[] = ['record' => $record, 'document' => $this->crypto->decryptDocument($record), 'valid' => true];
            } catch (Throwable $exception) {
                error_log('[SIMD] Gagal dekripsi dokumen ' . $record['id'] . ': ' . $exception->getMessage());
                $items[] = ['record' => $record, 'document' => null, 'valid' => false];
            }
        }

        \render('documents/index', [
            'title' => 'Dashboard Dokumen',
            'items' => $items,
            'documentCount' => $this->documents->count(),
        ]);
    }

    public function create(): void
    {
        \render('documents/form', [
            'title' => 'Tambah Dokumen',
            'heading' => 'Tambah dokumen baru',
            'action' => 'documents/store',
            'document' => ['title' => '', 'category' => 'Umum', 'description' => '', 'content' => ''],
            'submitLabel' => 'Simpan dan Enkripsi',
        ]);
    }

    public function store(): void
    {
        \verify_csrf();
        $payload = $this->validatedPayload();
        if ($payload === null) {
            \redirect('documents/create');
        }

        try {
            $id = self::uuidV4();
            $encrypted = $this->crypto->encryptDocument($payload, $id);
            $this->documents->create($id, $encrypted);
            \flash('success', 'Dokumen berhasil disimpan dalam bentuk terenkripsi.');
            \redirect('documents/show', ['id' => $id]);
        } catch (Throwable $exception) {
            error_log('[SIMD] Store gagal: ' . $exception->getMessage());
            \flash('danger', 'Dokumen gagal disimpan. Periksa konfigurasi key dan database.');
            \redirect('documents/create');
        }
    }

    public function show(string $id): void
    {
        $record = $this->findOr404($id);
        try {
            $document = $this->crypto->decryptDocument($record);
        } catch (Throwable $exception) {
            error_log('[SIMD] Show gagal: ' . $exception->getMessage());
            http_response_code(500);
            \render('errors/crypto', ['title' => 'Dekripsi gagal', 'documentId' => $id]);
            return;
        }

        \render('documents/show', ['title' => $document['title'], 'document' => $document, 'record' => $record]);
    }

    public function edit(string $id): void
    {
        $record = $this->findOr404($id);
        try {
            $document = $this->crypto->decryptDocument($record);
        } catch (Throwable $exception) {
            error_log('[SIMD] Edit gagal: ' . $exception->getMessage());
            http_response_code(500);
            \render('errors/crypto', ['title' => 'Dokumen tidak dapat diedit', 'documentId' => $id]);
            return;
        }

        \render('documents/form', [
            'title' => 'Edit Dokumen',
            'heading' => 'Edit dokumen',
            'action' => 'documents/update',
            'document' => $document,
            'documentId' => $id,
            'submitLabel' => 'Simpan Perubahan dan Enkripsi Ulang',
        ]);
    }

    public function update(string $id): void
    {
        \verify_csrf();
        $record = $this->findOr404($id);
        $payload = $this->validatedPayload();
        if ($payload === null) {
            \redirect('documents/edit', ['id' => $id]);
        }

        try {
            $encrypted = $this->crypto->encryptDocument($payload, $id);
            $this->documents->update($id, $encrypted);
            \flash('success', 'Dokumen berhasil diperbarui dan dienkripsi ulang dengan AES key baru.');
            \redirect('documents/show', ['id' => $id]);
        } catch (Throwable $exception) {
            error_log('[SIMD] Update gagal: ' . $exception->getMessage());
            \flash('danger', 'Perubahan gagal disimpan. Data sebelumnya tetap dipertahankan.');
            \redirect('documents/edit', ['id' => $id]);
        }
    }

    public function delete(string $id): void
    {
        \verify_csrf();
        $this->findOr404($id);
        $this->documents->delete($id);
        \flash('success', 'Dokumen berhasil dihapus.');
        \redirect('documents');
    }

    public function security(string $id): void
    {
        $record = $this->findOr404($id);
        \render('documents/security', [
            'title' => 'Bukti Enkripsi',
            'record' => $record,
            'ciphertextPreview' => substr((string) $record['encrypted_payload'], 0, 180) . '...',
            'wrappedKeyPreview' => substr((string) $record['encrypted_key'], 0, 180) . '...',
        ]);
    }

    private function validatedPayload(): ?array
    {
        $payload = [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'category' => trim((string) ($_POST['category'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'content' => trim((string) ($_POST['content'] ?? '')),
        ];

        $length = static fn (string $value): int => function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
        $errors = [];
        if ($payload['title'] === '' || $length($payload['title']) > 160) {
            $errors[] = 'Judul wajib diisi dan maksimal 160 karakter.';
        }
        if ($payload['category'] === '' || $length($payload['category']) > 80) {
            $errors[] = 'Kategori wajib diisi dan maksimal 80 karakter.';
        }
        if ($length($payload['description']) > 3000) {
            $errors[] = 'Deskripsi maksimal 3.000 karakter.';
        }
        if ($payload['content'] === '' || $length($payload['content']) > 50000) {
            $errors[] = 'Isi dokumen wajib diisi dan maksimal 50.000 karakter.';
        }

        if ($errors !== []) {
            \flash('danger', implode(' ', $errors));
            return null;
        }
        return $payload;
    }

    private function findOr404(string $id): array
    {
        if (!preg_match('/^[a-f0-9-]{36}$/i', $id)) {
            http_response_code(404);
            \render('errors/404', ['title' => 'Dokumen tidak ditemukan']);
            exit;
        }
        $record = $this->documents->find($id);
        if ($record === null) {
            http_response_code(404);
            \render('errors/404', ['title' => 'Dokumen tidak ditemukan']);
            exit;
        }
        return $record;
    }

    private static function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        $hex = bin2hex($data);
        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20, 12));
    }
}
