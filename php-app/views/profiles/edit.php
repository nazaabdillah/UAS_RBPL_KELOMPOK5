<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="row g-4">
    <!-- Form Edit -->
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-header">
                <h3><i class="bi bi-pencil-square me-2"></i>Edit Profile Hotspot</h3>
            </div>
            <div class="panel-body">

                <?php if (!empty($mtError)): ?>
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    MikroTik tidak terhubung: <?= e($mtError) ?>
                </div>
                <?php endif; ?>

                <!-- Badge nama profile -->
                <div class="edit-user-badge mb-4">
                    <div class="eub-icon" style="background:rgba(139,92,246,0.15);color:#a78bfa;">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                    <div>
                        <div class="eub-username"><?= e($profileData['name']) ?></div>
                        <div class="eub-label">Profile sedang diedit — nama tidak bisa diubah</div>
                    </div>
                </div>

                <form method="POST" action="index.php?page=edit-profile" id="editProfileForm">
                    <input type="hidden" name="csrf_token"     value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="name_original"  value="<?= e($profileData['name']) ?>">

                    <!-- Session Timeout -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="session_timeout">
                            <i class="bi bi-clock me-1"></i>Session Timeout <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-2 flex-wrap mb-2">
                            <?php foreach (['30m'=>'30m','1h'=>'1 Jam','3h'=>'3 Jam','6h'=>'6 Jam','12h'=>'12 Jam','1d'=>'1 Hari','3d'=>'3 Hari','7d'=>'1 Minggu','1w'=>'1 Minggu','30d'=>'1 Bulan','0s'=>'Unlimited'] as $val => $lbl): ?>
                                <button type="button" class="btn-preset"
                                        onclick="document.getElementById('session_timeout').value='<?= $val ?>';updatePreview()">
                                    <?= $lbl ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" name="session_timeout" id="session_timeout"
                               class="form-control" required
                               placeholder="Contoh: 1h, 1d, 7d, 0s"
                               value="<?= e($profileData['session-timeout'] === '0s' ? '0s' : $profileData['session-timeout']) ?>"
                               oninput="updatePreview()">
                        <div class="form-text">Format: <code>s</code>=detik <code>m</code>=menit <code>h</code>=jam <code>d</code>=hari <code>w</code>=minggu</div>
                    </div>

                    <!-- Rate Limit -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="rate_limit">
                            <i class="bi bi-speedometer2 me-1"></i>Rate Limit
                        </label>
                        <div class="d-flex gap-2 flex-wrap mb-2">
                            <?php foreach (['512k/1M','1M/2M','2M/4M','5M/10M','10M/20M',''] as $r): ?>
                                <button type="button" class="btn-preset <?= $r === '' ? 'btn-preset--muted' : '' ?>"
                                        onclick="document.getElementById('rate_limit').value='<?= $r ?>';updatePreview()">
                                    <?= $r === '' ? 'Unlimited' : $r ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" name="rate_limit" id="rate_limit"
                               class="form-control"
                               placeholder="Contoh: 1M/2M  (kosong = tidak dibatasi)"
                               value="<?= e($profileData['rate-limit'] === '-' ? '' : $profileData['rate-limit']) ?>"
                               oninput="updatePreview()">
                    </div>

                    <!-- Shared Users -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="shared_users">
                            <i class="bi bi-people me-1"></i>Shared Users
                        </label>
                        <select name="shared_users" id="shared_users" class="form-select" onchange="updatePreview()">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>"
                                    <?= (int)($profileData['shared-users'] ?? 1) === $i ? 'selected' : '' ?>>
                                    <?= $i ?> device
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Idle Timeout -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="idle_timeout">
                            <i class="bi bi-hourglass me-1"></i>Idle Timeout
                        </label>
                        <input type="text" name="idle_timeout" id="idle_timeout"
                               class="form-control"
                               placeholder="Contoh: 10m  (kosong = tidak ada)"
                               value="<?= e($profileData['idle-timeout'] === '-' ? '' : $profileData['idle-timeout']) ?>"
                               oninput="updatePreview()">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill" id="submitBtn">
                            <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                        </button>
                        <a href="index.php?page=profiles" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview + Info -->
    <div class="col-lg-6">
        <!-- Live Preview -->
        <div class="card-panel mb-4">
            <div class="panel-header">
                <h3><i class="bi bi-eye me-2"></i>Preview Perubahan</h3>
            </div>
            <div class="panel-body">
                <div class="profile-preview-card">
                    <div class="ppc-header">
                        <span class="ppc-name"><?= e($profileData['name']) ?></span>
                        <span class="ppc-badge">Hotspot Profile</span>
                    </div>
                    <div class="ppc-row">
                        <span><i class="bi bi-clock me-1"></i>Session Timeout</span>
                        <strong id="prev-timeout"><?= e($profileData['session-timeout']) ?></strong>
                    </div>
                    <div class="ppc-row">
                        <span><i class="bi bi-speedometer2 me-1"></i>Rate Limit</span>
                        <strong id="prev-rate">
                            <?= e($profileData['rate-limit'] && $profileData['rate-limit'] !== '-' ? $profileData['rate-limit'] : 'Unlimited') ?>
                        </strong>
                    </div>
                    <div class="ppc-row">
                        <span><i class="bi bi-people me-1"></i>Shared Users</span>
                        <strong id="prev-shared"><?= e($profileData['shared-users']) ?> device</strong>
                    </div>
                    <div class="ppc-row">
                        <span><i class="bi bi-hourglass me-1"></i>Idle Timeout</span>
                        <strong id="prev-idle">
                            <?= e($profileData['idle-timeout'] && $profileData['idle-timeout'] !== '-' ? $profileData['idle-timeout'] : '—') ?>
                        </strong>
                    </div>
                </div>

                <!-- Diff indicator -->
                <div id="diffBadge" class="mt-3" style="display:none;">
                    <div class="alert alert-info py-2 mb-0" style="font-size:12px;">
                        <i class="bi bi-arrow-left-right me-1"></i>
                        Ada perubahan yang belum disimpan
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Sebelumnya -->
        <div class="card-panel">
            <div class="panel-header">
                <h3><i class="bi bi-clock-history me-2"></i>Data Saat Ini di MikroTik</h3>
            </div>
            <div class="panel-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted ps-3" style="width:45%">Nama Profile</td>
                        <td class="font-mono fw-semibold"><?= e($profileData['name']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Session Timeout</td>
                        <td class="font-mono"><?= e($profileData['session-timeout']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Rate Limit</td>
                        <td class="font-mono"><?= e($profileData['rate-limit'] ?: '—') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Shared Users</td>
                        <td><?= e($profileData['shared-users']) ?> device</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Idle Timeout</td>
                        <td class="font-mono"><?= e($profileData['idle-timeout'] ?: '—') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Nilai awal untuk deteksi perubahan
const original = {
    timeout: <?= json_encode($profileData['session-timeout']) ?>,
    rate:    <?= json_encode($profileData['rate-limit'] === '-' ? '' : $profileData['rate-limit']) ?>,
    shared:  <?= json_encode($profileData['shared-users']) ?>,
    idle:    <?= json_encode($profileData['idle-timeout'] === '-' ? '' : $profileData['idle-timeout']) ?>,
};

function updatePreview() {
    const timeout = document.getElementById('session_timeout').value;
    const rate    = document.getElementById('rate_limit').value;
    const shared  = document.getElementById('shared_users').value;
    const idle    = document.getElementById('idle_timeout').value;

    document.getElementById('prev-timeout').textContent = timeout || '—';
    document.getElementById('prev-rate').textContent    = rate    || 'Unlimited';
    document.getElementById('prev-shared').textContent  = shared + ' device';
    document.getElementById('prev-idle').textContent    = idle   || '—';

    // Tampilkan diff badge jika ada perubahan
    const changed = timeout !== original.timeout ||
                    rate    !== original.rate    ||
                    shared  !== original.shared  ||
                    idle    !== original.idle;

    document.getElementById('diffBadge').style.display = changed ? 'block' : 'none';
}

// Disable submit saat loading
document.getElementById('editProfileForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
