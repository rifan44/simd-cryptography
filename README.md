# SIMD Cryptography — Sistem Informasi Manajemen Dokumen

Aplikasi PHP Native + MySQL untuk tugas implementasi **hybrid cryptography**:

- **AES-256-GCM** mengenkripsi payload dokumen (judul, kategori, deskripsi, isi).
- **RSA-OAEP SHA-256 + MGF1 SHA-256** mengenkripsi AES key per dokumen.
- **RSA-3072** key pair; private key dienkripsi dengan passphrase dan disimpan di luar web root.
- CRUD admin, CSRF protection, PDO prepared statements, password hashing, dan test kriptografi.
- UI menggunakan Bootstrap 5.3.8.

## 1. Persyaratan

- PHP 8.1 atau lebih baru.
- Extensions: `openssl`, `pdo`, `pdo_mysql`, `json`.
- Composer 2.x.
- MySQL 8+ atau MariaDB yang kompatibel dengan InnoDB dan `utf8mb4`.

## 2. Instalasi lokal (Windows / Laragon / XAMPP)

1. Ekstrak project ke folder kerja.
2. Buka terminal di root project.
3. Install dependency:

   ```bash
   composer install
   ```

4. Salin `.env.example` menjadi `.env`, lalu sesuaikan konfigurasi database dan `RSA_PRIVATE_KEY_PASSPHRASE`.
5. Buat database dan tabel dengan mengimpor `database/schema.sql` melalui phpMyAdmin atau CLI MySQL.
6. Buat RSA key pair:

   ```bash
   php bin/generate-keys.php
   ```

   File key dibuat di `storage/keys/`. Script menolak menimpa key yang telah ada. Jangan hapus key lama jika database masih berisi dokumen yang dienkripsi dengannya.
7. Buat akun administrator:

   ```bash
   php bin/create-admin.php
   ```

8. Jalankan server development:

   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

9. Buka `http://127.0.0.1:8000`.

## 3. Instalasi di Linux

Contoh paket dasar Ubuntu/Debian (sesuaikan versi repository OS):

```bash
sudo apt update
sudo apt install php-cli php-mysql php-openssl php-mbstring composer mariadb-server
```

Lanjutkan langkah Composer, `.env`, import SQL, generate key, dan create admin seperti di atas. Untuk Apache/Nginx production, arahkan document root ke folder `public/`, bukan root repository.

## 4. Konfigurasi `.env`

```dotenv
APP_NAME="SIMD Cryptography"
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
SESSION_SECURE_COOKIE=false

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simd_cryptography
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4

RSA_PUBLIC_KEY_PATH=storage/keys/public.pem
RSA_PRIVATE_KEY_PATH=storage/keys/private.pem
RSA_PRIVATE_KEY_PASSPHRASE=passphrase-yang-sama-dengan-saat-key-generation
```

Pada server publik, set `APP_DEBUG=false`, gunakan HTTPS dan `SESSION_SECURE_COOKIE=true`, buat user database khusus dengan privilege minimum, dan simpan `.env` di luar public web root bila memungkinkan.

## 5. Menjalankan test

```bash
composer test
```

Test otomatis membuat RSA key sementara dan memeriksa:

1. Round-trip AES-GCM + RSA-OAEP.
2. Ciphertext tidak memuat plaintext.
3. Perubahan ciphertext ditolak oleh GCM.
4. Perubahan document ID/AAD ditolak.
5. Private key yang salah tidak dapat membuka AES key.
6. Versi enkripsi yang tidak didukung ditolak.

Test tidak menggunakan database produksi dan tidak mengubah data aplikasi.

## 6. Struktur project

```text
app/
  Controllers/       # Auth dan CRUD dokumen
  Core/              # Koneksi PDO
  Middleware/        # Proteksi halaman admin
  Repositories/      # Query users dan documents
  Services/          # Hybrid Crypto Service
  Support/           # Env, CryptoException
bootstrap/           # Bootstrap aplikasi
config/              # Konfigurasi aplikasi dan database
database/            # SQL schema
bin/                 # Generator key dan admin CLI
public/              # Document root, front controller, assets
resources/views/     # Bootstrap UI
tests/               # Automated crypto tests
docs/                # Panduan laporan dan security model
storage/keys/        # RSA keys (PEM ignored by Git)
```

## 7. Catatan desain kriptografi

Satu dokumen memiliki satu AES key acak 256-bit. Seluruh field dokumen dikemas sebagai JSON dan dienkripsi menggunakan AES-256-GCM dengan IV 96-bit dan tag 128-bit. UUID dokumen dipakai sebagai AAD (`SIMD-DOCUMENT:v1:<uuid>`) sehingga record tidak dapat dipindahkan begitu saja ke ID lain tanpa menggagalkan autentikasi.

AES key kemudian dienkripsi menggunakan RSA public key dengan OAEP SHA-256 dan MGF1 SHA-256. Proses dekripsi melakukan operasi terbalik: RSA private key membuka AES key, lalu AES-GCM memverifikasi tag dan mendekripsi payload.

RSA tidak digunakan untuk mengenkripsi isi dokumen secara langsung karena RSA ditujukan untuk data pendek seperti AES key.

## 8. GitHub repository

Project ini sudah disusun agar siap diinisialisasi sebagai Git repository. Perintah untuk membuat repository remote sendiri:

```bash
git init
git add .
git commit -m "Initial commit: SIMD hybrid cryptography"
git branch -M main
git remote add origin https://github.com/USERNAME/simd-hybrid-cryptography.git
git push -u origin main
```

Buat repository kosong bernama `simd-hybrid-cryptography` di akun GitHub terlebih dahulu. Pastikan `.env`, `storage/keys/private.pem`, dan passphrase tidak masuk commit. Periksa sebelum push:

```bash
git status --short
git check-ignore .env storage/keys/private.pem
```

## 9. Dokumentasi tugas

Lihat [`docs/REPORT-GUIDE.md`](docs/REPORT-GUIDE.md) untuk kerangka BAB I–VI dan [`docs/SECURITY-MODEL.md`](docs/SECURITY-MODEL.md) untuk penjelasan aspek keamanan dan batasan implementasi.
