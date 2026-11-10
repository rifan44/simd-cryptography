<div class="mb-4"><a class="back-link" href="<?= e(url('documents/show', ['id' => $record['id']])) ?>">← Kembali ke dokumen</a><div class="eyebrow mt-3">CRYPTOGRAPHY EVIDENCE</div><h1 class="page-title">Bukti data terenkripsi</h1><p class="page-subtitle">Tampilan berikut menunjukkan nilai yang benar-benar disimpan dalam tabel <code>documents</code>.</p></div>
<div class="row g-3 mb-3">
    <div class="col-md-6"><div class="crypto-card"><span class="crypto-label">SYMMETRIC ENCRYPTION</span><h2>AES-256-GCM</h2><p>Payload dokumen dienkripsi menggunakan AES 256-bit. IV dan authentication tag disimpan bersama ciphertext.</p><div class="crypto-chip">AES key: 32 bytes · IV: 12 bytes · Tag: 16 bytes</div></div></div>
    <div class="col-md-6"><div class="crypto-card crypto-card-violet"><span class="crypto-label">ASYMMETRIC ENCRYPTION</span><h2>RSA-OAEP SHA-256</h2><p>AES key dienkripsi menggunakan RSA public key. Hanya private key yang sesuai yang dapat membukanya.</p><div class="crypto-chip">RSA key pair: 3072-bit · MGF1: SHA-256</div></div></div>
</div>
<section class="content-card">
    <div class="card-heading"><div><h2>Record database</h2><p>Identitas dokumen digunakan sebagai Additional Authenticated Data (AAD).</p></div><span class="secure-pill"><span></span> Version <?= e($record['encryption_version']) ?></span></div>
    <div class="data-block"><label>Document ID (plaintext metadata)</label><code><?= e($record['id']) ?></code><small>ID diperlukan untuk lookup dan diikat ke autentikasi GCM melalui AAD.</small></div>
    <div class="data-block"><label>Encrypted payload (base64)</label><pre><?= e($ciphertextPreview) ?></pre><small>Judul, kategori, deskripsi, dan isi dokumen berada di dalam ciphertext ini.</small></div>
    <div class="row g-3">
        <div class="col-md-4"><div class="data-block"><label>Initialization Vector</label><code><?= e($record['iv']) ?></code><small>Base64 dari IV acak 96-bit.</small></div></div>
        <div class="col-md-4"><div class="data-block"><label>Authentication tag</label><code><?= e($record['auth_tag']) ?></code><small>Base64 dari tag 128-bit.</small></div></div>
        <div class="col-md-4"><div class="data-block"><label>Encrypted AES key</label><pre><?= e($wrappedKeyPreview) ?></pre><small>Base64 dari hasil RSA-OAEP.</small></div></div>
    </div>
    <div class="alert alert-info mt-3 mb-0">Nilai ID dan timestamp tetap berupa metadata. Konten dokumen tidak tersimpan plaintext. Private key tidak pernah dikirim ke browser.</div>
</section>
