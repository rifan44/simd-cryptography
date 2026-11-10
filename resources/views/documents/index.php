<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <div class="eyebrow">OVERVIEW</div>
        <h1 class="page-title">Dashboard dokumen</h1>
        <p class="page-subtitle">Seluruh konten dokumen dienkripsi sebelum disimpan ke database.</p>
    </div>
    <a class="btn btn-primary" href="<?= e(url('documents/create')) ?>">＋ Tambah dokumen</a>
</div>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="stat-card"><div class="stat-icon">▤</div><div><div class="stat-label">Total dokumen</div><div class="stat-value"><?= e($documentCount) ?></div><div class="stat-caption">Data tersimpan terenkripsi</div></div></div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="stat-card"><div class="stat-icon icon-blue">⌁</div><div><div class="stat-label">Symmetric cipher</div><div class="stat-value stat-text">AES-256-GCM</div><div class="stat-caption">Enkripsi payload dan autentikasi</div></div></div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="stat-card"><div class="stat-icon icon-violet">◇</div><div><div class="stat-label">Asymmetric cipher</div><div class="stat-value stat-text">RSA-OAEP</div><div class="stat-caption">SHA-256 · RSA-3072</div></div></div>
    </div>
</div>
<section class="content-card">
    <div class="card-heading">
        <div><h2>Daftar dokumen</h2><p>Data ditampilkan setelah proses dekripsi di sisi server.</p></div>
        <span class="secure-pill"><span></span> Protected storage</span>
    </div>
    <?php if ($items === []): ?>
        <div class="empty-state"><div class="empty-icon">▤</div><h3>Belum ada dokumen</h3><p>Mulai dengan menambahkan dokumen pertama. Sistem akan mengenkripsinya otomatis.</p><a class="btn btn-primary" href="<?= e(url('documents/create')) ?>">Tambah dokumen pertama</a></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle document-table mb-0">
                <thead><tr><th>Dokumen</th><th>Kategori</th><th>Status kriptografi</th><th>Dibuat</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <?php if ($item['valid']): ?>
                            <td><div class="doc-title"><?= e($item['document']['title']) ?></div><div class="doc-description"><?= e(function_exists('mb_strimwidth') ? mb_strimwidth($item['document']['description'], 0, 88, '…', 'UTF-8') : substr($item['document']['description'], 0, 88)) ?></div></td>
                            <td><span class="category-pill"><?= e($item['document']['category']) ?></span></td>
                            <td><span class="status-pill"><span></span> Terenkripsi</span></td>
                        <?php else: ?>
                            <td><div class="doc-title text-danger">Dokumen tidak dapat didekripsi</div><div class="doc-description">Periksa key atau integritas ciphertext.</div></td>
                            <td>—</td><td><span class="status-pill status-danger">Integrity check gagal</span></td>
                        <?php endif; ?>
                        <td class="date-cell"><?= e(date('d M Y, H:i', strtotime((string) $item['record']['created_at']))) ?></td>
                        <td class="text-end text-nowrap">
                            <?php if ($item['valid']): ?>
                                <a class="btn btn-sm btn-light" href="<?= e(url('documents/show', ['id' => $item['record']['id']])) ?>">Buka</a>
                                <a class="btn btn-sm btn-light" href="<?= e(url('documents/edit', ['id' => $item['record']['id']])) ?>">Edit</a>
                            <?php endif; ?>
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(url('documents/security', ['id' => $item['record']['id']])) ?>" title="Lihat data terenkripsi">Ciphertext</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<div class="info-strip mt-3"><strong>Catatan keamanan</strong><span>Private key disimpan di luar web root. Database hanya menyimpan ciphertext, IV, authentication tag, dan AES key yang telah dienkripsi RSA.</span></div>
