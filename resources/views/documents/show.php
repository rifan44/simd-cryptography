<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div><a class="back-link" href="<?= e(url('documents')) ?>">← Semua dokumen</a><div class="eyebrow mt-3">DECRYPTED DOCUMENT VIEW</div><h1 class="page-title"><?= e($document['title']) ?></h1><div class="d-flex align-items-center gap-2 mt-2"><span class="category-pill"><?= e($document['category']) ?></span><span class="status-pill"><span></span> Integrity verified</span></div></div>
    <div class="d-flex gap-2"><a class="btn btn-outline-primary" href="<?= e(url('documents/security', ['id' => $record['id']])) ?>">Lihat ciphertext</a><a class="btn btn-primary" href="<?= e(url('documents/edit', ['id' => $record['id']])) ?>">Edit dokumen</a></div>
</div>
<section class="content-card document-detail">
    <div class="detail-meta"><div><span>DESKRIPSI</span><p><?= $document['description'] !== '' ? nl2br(e($document['description'])) : '<span class="text-secondary">Tidak ada deskripsi.</span>' ?></p></div><div class="detail-dates"><span>DIBUAT</span><strong><?= e(date('d M Y, H:i', strtotime((string) $record['created_at']))) ?></strong><span>DIPERBARUI</span><strong><?= e(date('d M Y, H:i', strtotime((string) $record['updated_at']))) ?></strong></div></div>
    <hr class="soft-divider">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3"><h2 class="section-title mb-0">Isi dokumen</h2><span class="decrypted-label">Didekripsi di server</span></div>
    <div class="document-body"><?= nl2br(e($document['content'])) ?></div>
</section>
<div class="info-strip mt-3"><strong>Validasi berhasil</strong><span>AES-GCM menerima authentication tag yang valid. Payload dapat didekripsi dengan AES key yang dibuka menggunakan RSA private key.</span></div>
<div class="d-flex justify-content-end mt-3"><form method="post" action="<?= e(url('documents/delete')) ?>" onsubmit="return confirm('Hapus dokumen ini secara permanen?');"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($record['id']) ?>"><button class="btn btn-outline-danger btn-sm" type="submit">Hapus dokumen</button></form></div>
