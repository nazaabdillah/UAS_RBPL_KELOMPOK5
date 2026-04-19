<?php
$pageTitle   = 'Generate Voucher';
$currentPage = 'generate';
$breadcrumb  = 'Generate Voucher';
$breadcrumbParent = 'Users';
require __DIR__ . '/../layout/header.php';
?>

<div class="row g-4">
    <!-- Form Generate -->
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-header">
                <h3><i class="bi bi-plus-circle me-2"></i>Generate Voucher Baru</h3>
            </div>
            <div class="panel-body">

                <?php if (!empty($mtError)): ?>
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>MikroTik tidak terhubung.</strong>
                    Daftar profile diambil dari config lokal.
                    <div class="small mt-1 text-muted"><?= e($mtError) ?></div>
                </div>
                <?php endif; ?>

                <form method="POST" action="index.php?page=generate" id="generateForm">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                    <!-- Profile -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="profile">
                            <i class="bi bi-clock me-1"></i>Profile Hotspot
                            <span class="text-danger">*</span>
                        </label>
                        <select name="profile" id="profile" class="form-select" required>
                            <option value="">— Pilih Profile —</option>
                            <?php if (empty($profiles)): ?>
                                <option value="" disabled>Tidak ada profile ditemukan</option>
                            <?php else: ?>
                                <?php foreach ($profiles as $key => $label): ?>
                                    <option value="<?= e($key) ?>"><?= e($label) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="form-text">
                            <?php if (!empty($mtError)): ?>
                                ⚠️ Menggunakan daftar dari <code>config/mikrotik.php</code>.
                            <?php else: ?>
                                <i class="bi bi-router text-success"></i>
                                <?= count($profiles) ?> profile dimuat langsung dari MikroTik.
                                <a href="index.php?page=add-profile" class="ms-1">+ Tambah profile baru</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Jumlah Voucher -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="qty">
                            <i class="bi bi-stack me-1"></i>Jumlah Voucher
                        </label>
                        <div class="qty-input-group">
                            <button type="button" class="qty-btn" id="qtyMinus">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" name="qty" id="qty"
                                   class="form-control text-center"
                                   value="1" min="1" max="50" required>
                            <button type="button" class="qty-btn" id="qtyPlus">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        <div class="form-text">Maksimal 50 voucher sekaligus.</div>
                    </div>

                    <!-- Komentar -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="comment">
                            <i class="bi bi-chat-left-text me-1"></i>Komentar / Keterangan
                        </label>
                        <input type="text" name="comment" id="comment"
                               class="form-control"
                               placeholder="Contoh: Paket promo weekend, Counter 1..."
                               maxlength="200">
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="generateBtn">
                        <i class="bi bi-lightning-charge me-2"></i>
                        Generate Voucher
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Info & Panduan -->
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-header">
                <h3><i class="bi bi-info-circle me-2"></i>Panduan Generate</h3>
            </div>
            <div class="panel-body">
                <div class="info-steps">
                    <div class="info-step">
                        <div class="step-num">1</div>
                        <div class="step-text">
                            <strong>Pilih profile</strong> yang sesuai dengan durasi akses yang ingin dijual
                        </div>
                    </div>
                    <div class="info-step">
                        <div class="step-num">2</div>
                        <div class="step-text">
                            <strong>Tentukan jumlah</strong> voucher yang akan digenerate (1–50)
                        </div>
                    </div>
                    <div class="info-step">
                        <div class="step-num">3</div>
                        <div class="step-text">
                            <strong>Klik Generate</strong> — sistem akan otomatis kirim ke MikroTik
                        </div>
                    </div>
                    <div class="info-step">
                        <div class="step-num">4</div>
                        <div class="step-text">
                            <strong>Print voucher</strong> setelah berhasil digenerate
                        </div>
                    </div>
                </div>

                <hr class="my-3">

                <div class="mt-3">
                    <p class="text-muted small mb-2">
                        <i class="bi bi-shield-check text-success me-1"></i>
                        Username & password digenerate secara acak dan unik
                    </p>
                    <p class="text-muted small mb-2">
                        <i class="bi bi-router text-primary me-1"></i>
                        Voucher langsung ditambahkan ke MikroTik via API
                    </p>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-database text-warning me-1"></i>
                        Data tersimpan di database lokal sebagai backup
                    </p>
                </div>
            </div>
        </div>

        <!-- Konfigurasi MikroTik Info -->
        <div class="card-panel mt-4">
            <div class="panel-header">
                <h3><i class="bi bi-router me-2"></i>Info MikroTik</h3>
            </div>
            <div class="panel-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted">Host</td>
                        <td class="font-mono fw-semibold"><?= e(MT_HOST) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Port API</td>
                        <td class="font-mono"><?= e(MT_PORT) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">User</td>
                        <td class="font-mono"><?= e(MT_USER) ?></td>
                    </tr>
                </table>
                <div class="mt-2">
                    <small class="text-muted">
                        Ubah konfigurasi di <code>config/mikrotik.php</code>
                        atau gunakan environment variable.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Quantity +/- buttons
const qty = document.getElementById('qty');

document.getElementById('qtyPlus').addEventListener('click', () => {
    if (parseInt(qty.value) < 50) qty.value = parseInt(qty.value) + 1;
});

document.getElementById('qtyMinus').addEventListener('click', () => {
    if (parseInt(qty.value) > 1) qty.value = parseInt(qty.value) - 1;
});

// Disable submit button saat loading
document.getElementById('generateForm').addEventListener('submit', function() {
    const btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sedang generate...';
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
