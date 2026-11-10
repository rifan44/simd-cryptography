# Security Model dan Batasan

## Data yang dilindungi

Field `title`, `category`, `description`, dan `content` digabung dalam payload JSON lalu dienkripsi sebagai satu unit. Database tidak menyimpan nilai field tersebut dalam plaintext.

## Komponen yang tetap plaintext

- UUID dokumen (dipakai untuk pencarian record dan AAD).
- `encryption_version`.
- `created_at` dan `updated_at`.
- Data login: username dan password hash (password hash tidak dapat didekripsi).

## Pengelolaan kunci

- AES key dibuat random 32 byte per dokumen dan tidak disimpan plaintext.
- RSA public key digunakan untuk membungkus AES key.
- RSA private key disimpan pada `storage/keys/private.pem`, di luar document root, dienkripsi dengan passphrase, dan permission diset `0600`.
- Public key disimpan di `storage/keys/public.pem`.
- Kedua file PEM diabaikan oleh Git melalui `.gitignore`.
- Backup private key harus disimpan secara terpisah dan aman. Kehilangan private key atau passphrase berarti data tidak dapat didekripsi.

## Integritas

AES-GCM memakai tag 128-bit dan AAD `SIMD-DOCUMENT:v1:<document-id>`. Perubahan ciphertext, tag, atau ID yang digunakan sebagai AAD akan menyebabkan verifikasi/dekripsi gagal.

## Kontrol aplikasi

- PDO prepared statements.
- `password_hash()` dan `password_verify()`.
- Session ID diregenerasi saat login.
- CSRF token untuk seluruh operasi POST.
- Escaping output dengan `htmlspecialchars()`.
- Login throttling sederhana per session.
- Private key tidak pernah dikirim ke browser.

## Batasan untuk tugas kuliah

Ini adalah baseline edukasi, bukan hasil security audit. Sebelum production, tambahkan HTTPS wajib, rate limiting login lintas-session berbasis server, audit log, backup/restore terenkripsi, key rotation, hardening server, security headers/CSP yang disesuaikan, dan pengujian keamanan independen. Hindari menempatkan direktori project selain `public/` di document root web server.
