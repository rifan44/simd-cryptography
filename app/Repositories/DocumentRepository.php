<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class DocumentRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, encryption_version, encrypted_payload, iv, auth_tag, encrypted_key, created_at, updated_at
             FROM documents ORDER BY created_at DESC'
        );
        return $statement->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM documents')->fetchColumn();
    }

    public function find(string $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, encryption_version, encrypted_payload, iv, auth_tag, encrypted_key, created_at, updated_at
             FROM documents WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $record = $statement->fetch();
        return $record ?: null;
    }

    public function create(string $id, array $encrypted): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO documents (id, encryption_version, encrypted_payload, iv, auth_tag, encrypted_key)
             VALUES (:id, :version, :payload, :iv, :tag, :key)'
        );
        $statement->execute([
            'id' => $id,
            'version' => $encrypted['encryption_version'],
            'payload' => $encrypted['encrypted_payload'],
            'iv' => $encrypted['iv'],
            'tag' => $encrypted['auth_tag'],
            'key' => $encrypted['encrypted_key'],
        ]);
    }

    public function update(string $id, array $encrypted): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE documents SET encryption_version = :version, encrypted_payload = :payload,
             iv = :iv, auth_tag = :tag, encrypted_key = :key WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'version' => $encrypted['encryption_version'],
            'payload' => $encrypted['encrypted_payload'],
            'iv' => $encrypted['iv'],
            'tag' => $encrypted['auth_tag'],
            'key' => $encrypted['encrypted_key'],
        ]);
        return $statement->rowCount() > 0;
    }

    public function delete(string $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM documents WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }
}
