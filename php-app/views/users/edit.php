<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="row g-4">
    <!-- Form Edit -->
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-header">
                <h3><i class="bi bi-pencil-square me-2"></i>Edit User Hotspot</h3>
            </div>
            <div class="panel-body">

                <?php if (!empty($mtError)): ?>
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    MikroTik tidak terhubung: <?= e($mtError) ?>
                </div>
                <?php endif; ?>

                <!-- Info user yang sedang diedit -->
                <div class="edit-user-badge mb-4">
                    <div class="eub-icon"><i class="bi bi-person-fill"></i></div>
                    <div>
                        <div class="eub-username"><?= e($userData['name']) ?></div>
                        <div class="eub-label">Sedang diedit</div>
                    </div>
                </div>

                <form method="POST" action="index.php?page=edit-user">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="username"   value="<?= e($userData['name']) ?>">

                    <!-- Password (opsional — kosong = tidak diubah) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="password">
                            <i class="bi bi-lock me-1"></i>Password Baru
                        </label>
                        <div class="input-icon-wrap">
                            <input type="text" name="password" id="password"
                                   class="form-control"
                                   placeholder="Kosongkan jika tidak ingin mengubah password"
                                   autocomplete="off">
                            <button type="button" id="genPass" class="btn-toggle-pass" title="Generate password acak">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Password saat ini: <span class="font-mono" id="currentPassDisplay">••••••••</span>
                            <button type="button" class="btn-reveal ms-1" onclick="toggleCurrentPass(this)"
                                    data-pass="<?= e($userData['password']) ?>">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Profile -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="profile">
                            <i class="bi bi-pie-chart me-1"></i>Profile <span class="text-danger">*</span>
                        </label>
                        <select name="profile" id="profile" class="form-select" required>
                            <option value="">— Pilih Profile —</option>
                            <?php foreach ($profiles as $p): ?>
                                <option value="<?= e($p) ?>"
                                    <?= $userData['profile'] === $p ? 'selected' : '' ?>>
                                    <?= e(profileLabel($p)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Komentar -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="comment">
                            <i class="bi bi-chat-left-text me-1"></i>Komentar
                        </label>
                        <input type="text" name="comment" id="comment"
                               class="form-control"
                               placeholder="Keterangan opsional"
                               maxlength="200"
                               value="<?= e($userData['comment']) ?>">
                    </div>

                    <!-- Status Disabled -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   name="disabled" id="disabled" value="1"
                                   <?= $userData['disabled'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="disabled">
                                <span class="fw-semibold">Nonaktifkan user ini</span>
                                <span class="text-muted small ms-1">(user tidak bisa login)</span>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill" id="submitBtn">
                            <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                        </button>
                        <a href="index.php?page=users" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Panel -->
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-header">
                <h3><i class="bi bi-info-circle me-2"></i>Informasi User</h3>
            </div>
            <div class="panel-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted ps-3" style="width:40%">Username</td>
                        <td class="font-mono fw-semibold text-primary"><?= e($userData['name']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Profile Saat Ini</td>
                        <td><span class="badge-profile"><?= e(profileLabel($userData['profile'])) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Status</td>
                        <td>
                            <?php if ($userData['disabled']): ?>
                                <span class="badge-disabled">Disabled</span>
                            <?php else: ?>
                                <span class="badge-unused">Aktif</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Komentar</td>
                        <td class="text-muted small"><?= e($userData['comment'] ?: '—') ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card-panel mt-4">
            <div class="panel-header">
                <h3><i class="bi bi-shield-check me-2"></i>Catatan Keamanan</h3>
            </div>
            <div class="panel-body">
                <div class="info-steps">
                    <div class="info-step">
                        <div class="step-num"><i class="bi bi-key"></i></div>
                        <div class="step-text">Kosongkan field password jika tidak ingin mengubahnya.</div>
                    </div>
                    <div class="info-step">
                        <div class="step-num"><i class="bi bi-router"></i></div>
                        <div class="step-text">Perubahan langsung dikirim ke MikroTik dan berlaku segera.</div>
                    </div>
                    <div class="info-step">
                        <div class="step-num"><i class="bi bi-person-dash"></i></div>
                        <div class="step-text">User yang sedang online akan tetap terhubung sampai sesi berakhir.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Generate password acak
document.getElementById('genPass').addEventListener('click', function () {
    const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
    let pass = '';
    for (let i = 0; i < 8; i++) pass += chars[Math.floor(Math.random() * chars.length)];
    document.getElementById('password').value = pass;
});

// Toggle tampilkan password saat ini
function toggleCurrentPass(btn) {
    const span = document.getElementById('currentPassDisplay');
    const icon = btn.querySelector('i');
    if (span.textContent === '••••••••') {
        span.textContent = btn.dataset.pass;
        icon.className = 'bi bi-eye-slash';
    } else {
        span.textContent = '••••••••';
        icon.className = 'bi bi-eye';
    }
}

// Disable submit saat loading
document.querySelector('form').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
