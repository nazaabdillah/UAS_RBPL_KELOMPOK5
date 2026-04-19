<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-header">
                <h3><i class="bi bi-plus-circle me-2"></i>Tambah Profile Hotspot</h3>
            </div>
            <div class="panel-body">
                <form method="POST" action="index.php?page=add-profile">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                    <!-- Nama Profile -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="name">
                            <i class="bi bi-tag me-1"></i>Nama Profile <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="name"
                               class="form-control" required
                               pattern="[a-zA-Z0-9_\-]+"
                               placeholder="Contoh: 1jam, 1hari, unlimited"
                               value="<?= e($_POST['name'] ?? '') ?>">
                        <div class="form-text">Hanya huruf, angka, underscore, dash. Tanpa spasi.</div>
                    </div>

                    <!-- Session Timeout -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="session_timeout">
                            <i class="bi bi-clock me-1"></i>Session Timeout <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-2 flex-wrap mb-2">
                            <?php
                            $presets = [
                                '30m'  => '30 Menit',
                                '1h'   => '1 Jam',
                                '3h'   => '3 Jam',
                                '6h'   => '6 Jam',
                                '12h'  => '12 Jam',
                                '1d'   => '1 Hari',
                                '3d'   => '3 Hari',
                                '7d'   => '1 Minggu',
                                '30d'  => '1 Bulan',
                                '0s'   => 'Unlimited',
                            ];
                            foreach ($presets as $val => $label):
                            ?>
                                <button type="button" class="btn-preset"
                                        onclick="document.getElementById('session_timeout').value='<?= $val ?>'">
                                    <?= $label ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" name="session_timeout" id="session_timeout"
                               class="form-control" required
                               placeholder="Contoh: 1h, 1d, 7d, 0s (unlimited)"
                               value="<?= e($_POST['session_timeout'] ?? '') ?>">
                        <div class="form-text">
                            Format: <code>s</code>=detik, <code>m</code>=menit, <code>h</code>=jam, <code>d</code>=hari. Isi <code>0s</code> untuk unlimited.
                        </div>
                    </div>

                    <!-- Rate Limit -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="rate_limit">
                            <i class="bi bi-speedometer2 me-1"></i>Rate Limit
                        </label>
                        <div class="d-flex gap-2 flex-wrap mb-2">
                            <?php
                            $rates = ['512k/1M','1M/2M','2M/4M','5M/10M','10M/20M'];
                            foreach ($rates as $r):
                            ?>
                                <button type="button" class="btn-preset"
                                        onclick="document.getElementById('rate_limit').value='<?= $r ?>'">
                                    <?= $r ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" name="rate_limit" id="rate_limit"
                               class="form-control"
                               placeholder="Contoh: 1M/2M (upload/download). Kosongkan = tidak dibatasi"
                               value="<?= e($_POST['rate_limit'] ?? '') ?>">
                        <div class="form-text">Format: <code>upload/download</code> dalam kbps atau Mbps.</div>
                    </div>

                    <!-- Shared Users -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="shared_users">
                            <i class="bi bi-people me-1"></i>Shared Users
                        </label>
                        <select name="shared_users" id="shared_users" class="form-select">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>" <?= ($i === 1) ? 'selected' : '' ?>>
                                    <?= $i ?> device
                                </option>
                            <?php endfor; ?>
                        </select>
                        <div class="form-text">Jumlah device yang bisa login bersamaan dengan 1 akun.</div>
                    </div>

                    <!-- Idle Timeout -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="idle_timeout">
                            <i class="bi bi-hourglass me-1"></i>Idle Timeout
                        </label>
                        <input type="text" name="idle_timeout" id="idle_timeout"
                               class="form-control"
                               placeholder="Contoh: 10m. Kosongkan = tidak ada"
                               value="<?= e($_POST['idle_timeout'] ?? '') ?>">
                        <div class="form-text">Waktu disconnect otomatis jika tidak aktif.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success flex-fill" id="submitBtn">
                            <i class="bi bi-check-circle me-2"></i>Simpan Profile
                        </button>
                        <a href="index.php?page=profiles" class="btn btn-outline-secondary">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview & Tips -->
    <div class="col-lg-6">
        <div class="card-panel mb-4">
            <div class="panel-header">
                <h3><i class="bi bi-eye me-2"></i>Preview Profile</h3>
            </div>
            <div class="panel-body">
                <div class="profile-preview-card">
                    <div class="ppc-header">
                        <span class="ppc-name" id="prev-name">—</span>
                        <span class="ppc-badge">Hotspot Profile</span>
                    </div>
                    <div class="ppc-row">
                        <span><i class="bi bi-clock me-1"></i>Session Timeout</span>
                        <strong id="prev-timeout">—</strong>
                    </div>
                    <div class="ppc-row">
                        <span><i class="bi bi-speedometer2 me-1"></i>Rate Limit</span>
                        <strong id="prev-rate">Unlimited</strong>
                    </div>
                    <div class="ppc-row">
                        <span><i class="bi bi-people me-1"></i>Shared Users</span>
                        <strong id="prev-shared">1 device</strong>
                    </div>
                    <div class="ppc-row">
                        <span><i class="bi bi-hourglass me-1"></i>Idle Timeout</span>
                        <strong id="prev-idle">—</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-panel">
            <div class="panel-header">
                <h3><i class="bi bi-lightbulb me-2"></i>Tips</h3>
            </div>
            <div class="panel-body">
                <div class="tip-list">
                    <div class="tip-item"><i class="bi bi-check text-success"></i>
                        <span>Nama profile harus <strong>sama persis</strong> dengan yang ada di <code>config/mikrotik.php</code> agar voucher bisa di-generate.</span>
                    </div>
                    <div class="tip-item mt-2"><i class="bi bi-check text-success"></i>
                        <span>Setelah tambah profile baru, tambahkan juga ke array <code>MT_PROFILES</code> di file config.</span>
                    </div>
                    <div class="tip-item mt-2"><i class="bi bi-check text-success"></i>
                        <span>Rate limit kosong berarti tidak ada pembatasan kecepatan.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Live preview
const fields = {
    name:    document.getElementById('name'),
    timeout: document.getElementById('session_timeout'),
    rate:    document.getElementById('rate_limit'),
    shared:  document.getElementById('shared_users'),
    idle:    document.getElementById('idle_timeout'),
};

function updatePreview() {
    document.getElementById('prev-name').textContent    = fields.name.value    || '—';
    document.getElementById('prev-timeout').textContent = fields.timeout.value || '—';
    document.getElementById('prev-rate').textContent    = fields.rate.value    || 'Unlimited';
    document.getElementById('prev-shared').textContent  = (fields.shared.value || 1) + ' device';
    document.getElementById('prev-idle').textContent    = fields.idle.value    || '—';
}

Object.values(fields).forEach(el => el.addEventListener('input', updatePreview));
document.getElementById('shared_users').addEventListener('change', updatePreview);

// Disable submit saat loading
document.querySelector('form').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
