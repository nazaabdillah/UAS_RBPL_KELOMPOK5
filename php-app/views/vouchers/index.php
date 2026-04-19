<?php
$pageTitle   = 'Daftar Voucher';
$currentPage = 'vouchers';
$breadcrumb  = 'Voucher';
require __DIR__ . '/../layout/header.php';
?>

<!-- Filter Bar -->
<div class="card-panel mb-4">
    <div class="panel-body">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="vouchers">

            <div class="col-sm-4 col-md-3">
                <label class="form-label-sm">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="unused" <?= ($filters['status'] ?? '') === 'unused' ? 'selected' : '' ?>>Tersedia</option>
                    <option value="used"   <?= ($filters['status'] ?? '') === 'used'   ? 'selected' : '' ?>>Terpakai</option>
                </select>
            </div>

            <div class="col-sm-4 col-md-3">
                <label class="form-label-sm">Profile</label>
                <select name="profile" class="form-select form-select-sm">
                    <option value="">Semua Profile</option>
                    <?php foreach ($profiles as $key => $label): ?>
                        <option value="<?= e($key) ?>"
                            <?= ($filters['profile'] ?? '') === $key ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-sm-4 col-md-3">
                <label class="form-label-sm">Cari Username</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="WF..." value="<?= e($filters['search'] ?? '') ?>">
            </div>

            <div class="col-sm-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="index.php?page=vouchers" class="btn btn-outline-secondary btn-sm ms-1">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>

            <div class="col-sm-auto ms-auto">
                <a href="index.php?page=generate" class="btn btn-success btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>Generate Baru
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Voucher Table -->
<div class="card-panel">
    <div class="panel-header">
        <h3>
            <i class="bi bi-ticket-perforated me-2"></i>
            Voucher
            <span class="badge bg-secondary ms-2"><?= number_format($total) ?></span>
        </h3>
    </div>
    <div class="panel-body p-0">
        <?php if (empty($vouchers)): ?>
            <div class="empty-state py-5">
                <i class="bi bi-inbox display-4"></i>
                <p class="mt-2">Tidak ada voucher ditemukan</p>
                <a href="index.php?page=generate" class="btn btn-primary btn-sm mt-2">
                    <i class="bi bi-plus-circle me-1"></i>Generate Voucher
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="voucherTable">
                    <thead>
                        <tr>
                            <th style="width:40px">
                                <input type="checkbox" id="checkAll" class="form-check-input">
                            </th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Profile</th>
                            <th>Status</th>
                            <th>Dibuat Oleh</th>
                            <th>Tanggal</th>
                            <th style="width:120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vouchers as $v): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input row-check"
                                       value="<?= e($v['id']) ?>">
                            </td>
                            <td>
                                <span class="font-mono fw-semibold text-primary">
                                    <?= e($v['username']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="font-mono text-pass" data-pass="<?= e($v['password']) ?>">
                                    ••••••••
                                </span>
                                <button class="btn-reveal" title="Tampilkan password"
                                        onclick="togglePass(this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                            <td>
                                <span class="badge-profile"><?= e(profileLabel($v['profile'])) ?></span>
                            </td>
                            <td><?= statusBadge($v['status']) ?></td>
                            <td class="text-muted small"><?= e($v['admin_username'] ?? '-') ?></td>
                            <td class="text-muted small"><?= formatDate($v['created_at']) ?></td>
                            <td>
                                <!-- Tombol Hapus -->
                                <form method="POST" action="index.php?page=delete"
                                      onsubmit="return confirm('Hapus voucher <?= e($v['username']) ?>?')"
                                      class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="id" value="<?= e($v['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>

                                <!-- Tombol Print -->
                                <a href="index.php?page=print&ids=<?= e($v['id']) ?>"
                                   class="btn btn-sm btn-outline-secondary ms-1" title="Print"
                                   target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pages > 1): ?>
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                <small class="text-muted">
                    Halaman <?= $page ?> dari <?= $pages ?>
                    (<?= number_format($total) ?> total)
                </small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link"
                                   href="?page=vouchers&p=<?= $i ?>&status=<?= e($filters['status']) ?>&profile=<?= e($filters['profile']) ?>&search=<?= e($filters['search']) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Toggle tampilkan/sembunyikan password
function togglePass(btn) {
    const span = btn.previousElementSibling;
    const icon = btn.querySelector('i');
    if (span.textContent === '••••••••') {
        span.textContent = span.dataset.pass;
        icon.className = 'bi bi-eye-slash';
    } else {
        span.textContent = '••••••••';
        icon.className = 'bi bi-eye';
    }
}

// Select all checkbox
document.getElementById('checkAll').addEventListener('change', function() {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
