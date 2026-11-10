CREATE DATABASE IF NOT EXISTS simd_cryptography
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE simd_cryptography;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(60) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS documents (
    id CHAR(36) NOT NULL,
    encryption_version TINYINT UNSIGNED NOT NULL DEFAULT 1,
    encrypted_payload MEDIUMTEXT NOT NULL,
    iv VARCHAR(32) NOT NULL,
    auth_tag VARCHAR(32) NOT NULL,
    encrypted_key MEDIUMTEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_documents_created_at (created_at)
) ENGINE=InnoDB;
