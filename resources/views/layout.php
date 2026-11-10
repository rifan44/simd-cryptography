<?php
$title = $title ?? 'SIMD Cryptography';
$loggedIn = !empty($_SESSION['user']['id']);
$successMessage = flash('success');
$errorMessage = flash('danger');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f6fb">
    <title><?= e($title) ?> · <?= e($config['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<?php if ($loggedIn): ?>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="<?= e(url('documents')) ?>">
            <span class="brand-mark">S</span>
            <span><strong>SIMD</strong><small>Secure Workspace</small></span>
        </a>
        <div class="sidebar-label">WORKSPACE</div>
        <nav class="nav flex-column gap-1">
            <a class="nav-link <?= str_starts_with((string) ($_GET['r'] ?? ''), 'documents') ? 'active' : '' ?>" href="<?= e(url('documents')) ?>">
                <span class="nav-icon">▦</span> Dashboard dokumen
            </a>
            <a class="nav-link" href="<?= e(url('documents/create')) ?>">
                <span class="nav-icon">＋</span> Tambah dokumen
            </a>
        </nav>
        <div class="sidebar-note mt-auto">
            <div class="note-icon">◇</div>
            <strong>Hybrid encryption</strong>
            <p>AES-256-GCM melindungi konten. RSA-OAEP melindungi AES key.</p>
        </div>
        <div class="sidebar-footer">PROJECT KRIPTOGRAFI · PHP NATIVE</div>
    </aside>
    <main class="main-panel">
        <header class="topbar">
            <div>
                <div class="eyebrow">SECURE DOCUMENT MANAGEMENT</div>
                <div class="topbar-title">Ruang kerja administrator</div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="user-chip"><span class="avatar"><?= e(strtoupper(substr((string) $_SESSION['user']['username'], 0, 1))) ?></span><span class="d-none d-sm-inline"><?= e($_SESSION['user']['username']) ?></span></div>
                <form method="post" action="<?= e(url('logout')) ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Keluar</button>
                </form>
            </div>
        </header>
        <div class="page-content">
            <?php if ($successMessage): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?= e($successMessage) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div><?php endif; ?>
            <?php if ($errorMessage): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?= e($errorMessage) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div><?php endif; ?>
            <?= $content ?>
        </div>
        <footer class="app-footer">SIMD Cryptography <span>•</span> PHP Native · MySQL · AES-256-GCM · RSA-OAEP SHA-256</footer>
    </main>
</div>
<?php else: ?>
<div class="auth-shell">
    <div class="auth-brand"><span class="brand-mark">S</span><span><strong>SIMD</strong><small>Secure Workspace</small></span></div>
    <main class="auth-content">
        <?php if ($successMessage): ?><div class="alert alert-success" role="alert"><?= e($successMessage) ?></div><?php endif; ?>
        <?php if ($errorMessage): ?><div class="alert alert-danger" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
        <?= $content ?>
    </main>
    <div class="auth-caption">Sistem Informasi Manajemen Dokumen · Hybrid Cryptography</div>
</div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>
</html>
