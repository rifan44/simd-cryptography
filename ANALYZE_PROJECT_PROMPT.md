# Prompt: Analisis awal repository dan isi PROJECT_CONTEXT.md

Baca `AGENTS.md` terlebih dahulu, lalu analisis repository yang sedang terbuka.

Tujuan:
1. Identifikasi nama dan tujuan project hanya jika dapat disimpulkan dari bukti repository. Jika tidak jelas, tulis "Belum diketahui".
2. Periksa manifest dan konfigurasi yang relevan (misalnya `package.json`, lockfile, `composer.json`, `pyproject.toml`, konfigurasi framework, Dockerfile, dan konfigurasi test jika tersedia).
3. Identifikasi framework, bahasa, runtime, dependency utama, dan versinya. Cantumkan file bukti. Jangan menebak versi.
4. Petakan struktur folder penting dan jelaskan tanggung jawabnya. Abaikan folder hasil build, dependency, cache, dan file generated yang tidak relevan.
5. Identifikasi entry point, routing, API/services, models/schema, shared components, dan konfigurasi jika memang ada.
6. Catat fitur yang sudah dapat dibuktikan dari implementasi. Bedakan fitur yang selesai, parsial, dan belum diketahui.
7. Identifikasi test/lint/type-check/build scripts yang benar-benar tersedia.
8. Isi `PROJECT_CONTEXT.md` berdasarkan temuan. Pertahankan bagian yang belum dapat dipastikan dengan label "Belum diketahui" atau "Perlu verifikasi".
9. Jangan mengubah source code, dependency, konfigurasi aplikasi, database, atau file lain selain `PROJECT_CONTEXT.md`.
10. Jangan membaca, menyalin, atau menampilkan secret. Jangan membuka isi `.env`, credential, token, atau private key. Nama file `.env.example` boleh dicatat tanpa menampilkan nilainya.

Sebelum selesai, laporkan ringkasan stack, file bukti, bagian konteks yang belum diketahui, dan konfirmasi bahwa tidak ada file selain `PROJECT_CONTEXT.md` yang diubah.
