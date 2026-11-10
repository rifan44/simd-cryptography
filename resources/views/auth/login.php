<div class="login-card">
    <div class="login-kicker">ADMINISTRATOR ACCESS</div>
    <h1>Masuk ke workspace</h1>
    <p class="text-secondary mb-4">Kelola dokumen dengan perlindungan hybrid encryption.</p>
    <form method="post" action="<?= e(url('login')) ?>" autocomplete="on">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input class="form-control form-control-lg" id="username" name="username" required maxlength="60" autocomplete="username" autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label" for="password">Password</label>
            <input class="form-control form-control-lg" id="password" name="password" type="password" required autocomplete="current-password">
        </div>
        <button class="btn btn-primary btn-lg w-100" type="submit">Masuk <span aria-hidden="true">→</span></button>
    </form>
    <div class="login-security"><span class="security-dot"></span> Area administrator terlindungi</div>
</div>
