# AGENTS.md

Instruksi bersama untuk AI coding agent pada repository ini.

## Aturan utama
- Baca `AGENTS.md` dan `PROJECT_CONTEXT.md` sebelum memulai pekerjaan.
- Periksa struktur repository, konfigurasi, dependency, versi, dan implementasi terkait sebelum memberi solusi atau mengubah kode.
- Jangan mengarang nama file, API, database schema, dependency, atau versi.
- Pertahankan stack, arsitektur, conventions, dan pola yang sudah ada.
- Jangan melakukan refactor besar atau mengubah bagian yang tidak terkait tanpa alasan dan persetujuan.
- Jangan menghapus file, menjalankan perintah destruktif, mengubah database secara berisiko, atau melakukan deployment tanpa persetujuan eksplisit.
- Jangan meminta, menampilkan, atau menyalin secret (API key, password, token, private key, atau isi `.env`).
- Jangan menyimpan secret atau data sensitif ke dokumentasi, prompt, log, atau commit.
- Jika requirement penting belum jelas, ajukan satu pertanyaan klarifikasi yang paling menentukan.

## Workflow
1. Ringkas requirement dan acceptance criteria.
2. Periksa file dan implementasi yang relevan; gunakan `git status` dan periksa diff sebelum mengedit.
3. Untuk debugging, identifikasi root cause sebelum mengubah kode.
4. Sampaikan rencana singkat dan file yang kemungkinan terdampak.
5. Implementasikan perubahan sekecil mungkin.
6. Jalankan test, lint, type-check, atau build yang tersedia dan relevan.
7. Tinjau diff untuk memastikan tidak ada perubahan tak terkait.
8. Laporkan hasil, file yang berubah, alasan, verifikasi, dan pekerjaan yang belum selesai.

Untuk tugas sederhana dan berisiko rendah, rencana boleh singkat. Tetap periksa konteks sebelum mengedit.

## Kualitas implementasi
- Utamakan keamanan, maintainability, keterujian, dan kesesuaian dengan versi dependency.
- Validasi input pada boundary yang tepat dan tangani error secara eksplisit.
- Hindari duplikasi logic dan abstraksi berlebihan.
- Jangan menambahkan dependency jika kemampuan yang ada sudah memadai.
- Untuk API atau fitur yang bergantung pada versi, periksa dokumentasi resmi versi terkait.
- Jangan menonaktifkan security checks hanya agar implementasi terlihat berhasil.

## Database dan operasi berisiko
- Jelaskan dampak dan minta persetujuan sebelum migration yang berisiko terhadap data.
- Jangan melakukan reset database, force push, penghapusan massal, deployment, atau perubahan production tanpa persetujuan eksplisit.
- Jika operasi data disetujui, jelaskan backup dan rollback plan yang relevan.

## Kolaborasi multi-agent
- `PROJECT_CONTEXT.md` adalah ringkasan handoff, bukan pengganti source code sebagai sumber kebenaran.
- Periksa perubahan terbaru sebelum mengedit; jangan menimpa pekerjaan agent lain.
- Hindari beberapa agent mengedit file/area yang sama secara bersamaan.
- Jika pekerjaan dibagi, tetapkan area atau file tanggung jawab masing-masing agent.
- Setelah pekerjaan yang mengubah status/keputusan selesai, perbarui bagian relevan di `PROJECT_CONTEXT.md`.
- Jangan mengklaim pekerjaan agent lain selesai tanpa memverifikasi hasilnya.

## Format laporan
- **Hasil:** perubahan yang dilakukan.
- **File:** file yang dibuat/diubah.
- **Alasan:** root cause atau pertimbangan desain.
- **Verifikasi:** perintah dan hasil test/lint/type-check/build.
- **Belum selesai:** risiko, keterbatasan, atau pekerjaan lanjutan.

Jika tidak menjalankan test, nyatakan dengan jelas. Jangan mengklaim implementasi sudah teruji.
