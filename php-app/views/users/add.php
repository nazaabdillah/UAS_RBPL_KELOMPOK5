<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-header">
                <h3><i class="bi bi-person-plus me-2"></i>Tambah User Hotspot</h3>
            </div>
            <div class="panel-body">

                <?php if ($mtError): ?>
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        MikroTik tidak terhubung: <?= e($mtError) ?><br>
                        <small>Form tetap bisa disubmit, tapi user tidak akan terkirim ke router.</small>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?page=add-user">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="username">
                            <i class="bi bi-person me-1"></i>Username <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="username" id="username"
                               class="form-control" required
                               pattern="[a-zA-Z0-9_\-]+"
                               placeholder="Contoh: pelanggan01"
                               value="<?= e($_POST['username'] ?? '') ?>">
                        <div class="form-text">Hanya huruf, angka, underscore, dan dash.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="password">
                            <i class="bi bi-lock me-1"></i>Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <input type="text" name="password" id="password"
                                   class="form-control" required
                                   placeholder="Masukkan password"
                                   value="<?= e($_POST['password'] ?? '') ?>">
                            <button type="button" class="btn-toggle-pass" id="genPass" title="Generate password acak">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                        <div class="form-text">Klik ikon untuk generate password acak.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="profile">
                            <i class="bi bi-pie-chart me-1"></i>Profile <span class="text-danger">*</span>
                        </label>
                        <select name="profile" id="profile" class="form-select" required>
                            <option value="">— Pilih Profile —</option>
                            <?php foreach ($profiles as $p): ?>
                                <option value="<?= e($p) ?>"
                                    <?= ($_POST['profile'] ?? '') === $p ? 'selected' : '' ?>>
                                    <?= e(profileLabel($p)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="comment">
                            <i class="bi bi-chat-left-text me-1"></i>Komentar
                        </label>
                        <input type="text" name="comment" id="comment"
                               class="form-control"
                               placeholder="Opsional — catatan untuk user ini"
                               maxlength="200"
                               value="<?= e($_POST['comment'] ?? '') ?>">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success flex-fill">
                            <i class="bi bi-check-circle me-2"></i>Simpan ke MikroTik
                        </button>
                        <a href="index.php?page=users" class="btn btn-outline-secondary">
                            Batal
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Info panel -->
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-header">
                <h3><i class="bi bi-info-circle me-2"></i>Catatan</h3>
            </div>
            <div class="panel-body">
                <div class="info-steps">
                    <div class="info-step">
                        <div class="step-num"><i class="bi bi-1-circle"></i></div>
                        <div class="step-text">
                            User akan langsung ditambahkan ke <strong>MikroTik Hotspot</strong> via API.
                        </div>
                    </div>
                    <div class="info-step">
                        <div class="step-num"><i class="bi bi-2-circle"></i></div>
                        <div class="step-text">
                            Data juga disimpan ke <strong>database lokal</strong> sebagai backup.
                        </div>
                    </div>
                    <div class="info-step">
                        <div class="step-num"><i class="bi bi-3-circle"></i></div>
                        <div class="step-text">
                            Jika ingin generate banyak user sekaligus, gunakan fitur
                            <a href="index.php?page=generate">Generate User</a>.
                        </div>
                    </div>
                </div>
                <hr class="my-3">
                <div class="text-muted small">
                    <i class="bi bi-router me-1 text-primary"></i>
                    Terhubung ke: <strong><?= e(MT_HOST) ?></strong>
                    <?= $mtError
                        ? '<span class="text-danger ms-1">(Offline)</span>'
                        : '<span class="text-success ms-1">(Ready)</span>' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Generate password acak saat klik ikon
document.getElementById('genPass').addEventListener('click', function() {
    const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
    let pass = '';
    for (let i = 0; i < 8; i++) {
        pass += chars[Math.floor(Math.random() * chars.length)];
    }
    document.getElementById('password').value = pass;
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
