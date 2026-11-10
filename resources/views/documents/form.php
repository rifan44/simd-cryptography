<div class="mb-4">
    <a class="back-link" href="<?= e(url('documents')) ?>">← Kembali ke dashboard</a>
    <div class="eyebrow mt-3">DOCUMENT EDITOR</div>
    <h1 class="page-title"><?= e($heading) ?></h1>
    <p class="page-subtitle">Field dokumen akan digabung menjadi payload JSON lalu dienkripsi menggunakan AES-256-GCM.</p>
</div>
<section class="content-card form-card">
    <form method="post" action="<?= e(url($action)) ?>">
        <?= csrf_field() ?>
        <?php if (!empty($documentId)): ?><input type="hidden" name="id" value="<?= e($documentId) ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label" for="title">Judul dokumen <span class="text-danger">*</span></label>
                <input class="form-control" id="title" name="title" maxlength="160" required value="<?= e($document['title']) ?>" placeholder="Contoh: Kebijakan Keamanan Informasi">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="category">Kategori <span class="text-danger">*</span></label>
                <input class="form-control" id="category" name="category" maxlength="80" required value="<?= e($document['category']) ?>" placeholder="Umum / Internal / Arsip">
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="3" maxlength="3000" placeholder="Ringkasan dokumen..."><?= e($document['description']) ?></textarea>
                <div class="form-text">Maksimal 3.000 karakter. Deskripsi juga dienkripsi.</div>
            </div>
            <div class="col-12">
                <label class="form-label" for="content">Isi dokumen <span class="text-danger">*</span></label>
                <textarea class="form-control document-content-input" id="content" name="content" rows="12" maxlength="50000" required placeholder="Tuliskan isi dokumen di sini..."><?= e($document['content']) ?></textarea>
                <div class="form-text">Maksimal 50.000 karakter. Konten tidak disimpan sebagai plaintext di database.</div>
            </div>
        </div>
        <div class="encryption-preview mt-4"><div class="preview-icon">⌁</div><div><strong>Perlindungan otomatis aktif</strong><p>Setiap penyimpanan menghasilkan AES key dan IV baru. AES key dibungkus dengan RSA-OAEP SHA-256.</p></div></div>
        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4"><a class="btn btn-light" href="<?= e(url('documents')) ?>">Batal</a><button class="btn btn-primary" type="submit"><?= e($submitLabel) ?> <span aria-hidden="true">→</span></button></div>
    </form>
</section>
