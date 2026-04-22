<?php require __DIR__ . '/../layout/header.php'; ?>

<!-- Toolbar -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <form method="GET" action="index.php" class="d-flex gap-2">
        <input type="hidden" name="page" value="users">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Cari username / profile..." value="<?= e($_GET['search'] ?? '') ?>" style="width:220px">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-search"></i>
        </button>
        <?php if (!empty($_GET['search'])): ?>
            <a href="index.php?page=users" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-circle"></i>
            </a>
        <?php endif; ?>
    </form>
    <div class="d-flex gap-2">
        <a href="index.php?page=add-user" class="btn btn-success btn-sm">
            <i class="bi bi-person-plus me-1"></i>Add User
        </a>
        <a href="index.php?page=generate" class="btn btn-primary btn-sm">
            <i class="bi bi-lightning-charge me-1"></i>Generate
        </a>
        <a href="index.php?page=sync"
           class="btn btn-outline-secondary btn-sm"
           onclick="return confirm('Sinkronisasi dengan MikroTik?')">
            <i class="bi bi-arrow-repeat me-1"></i>Sync
        </a>
    </div>
</div>

<?php if ($mtError): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Gagal terhubung ke MikroTik: <?= e($mtError) ?>
        <div class="mt-1 small">Pastikan konfigurasi di <code>config/mikrotik.php</code> sudah benar.</div>
    </div>
<?php endif; ?>

<!-- User Table -->
<div class="card-panel">
    <div class="panel-header">
        <h3><i class="bi bi-people me-2"></i>Hotspot Users
            <span class="badge bg-secondary ms-2"><?= count($mtUsers) ?></span>
        </h3>
        <span class="small text-muted">Data langsung dari MikroTik</span>
    </div>
    <div class="panel-body p-0">
        <?php if (empty($mtUsers)): ?>
            <div class="empty-state py-5">
                <i class="bi bi-people display-4"></i>
                <p class="mt-2">
                    <?= $mtError ? 'Tidak dapat mengambil data.' : 'Belum ada user hotspot.' ?>
                </p>
                <?php if (!$mtError): ?>
                    <a href="index.php?page=add-user" class="btn btn-success btn-sm mt-1">
                        <i class="bi bi-person-plus me-1"></i>Add User
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Profile</th>
                            <th>Status</th>
                            <th>Komentar</th>
                            <th style="width:110px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mtUsers as $u): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <!-- Dot online/offline -->
                                    <span class="status-dot <?= isset($mtOnline[$u['name']]) ? 'dot-online' : 'dot-offline' ?>"
                                          title="<?= isset($mtOnline[$u['name']]) ? 'Sedang online' : 'Offline' ?>">
                                    </span>
                                    <span class="font-mono fw-semibold text-primary">
                                        <?= e($u['name']) ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="font-mono text-pass" data-pass="<?= e($u['password']) ?>">••••••••</span>
                                <button class="btn-reveal" onclick="togglePass(this)" title="Tampilkan">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                            <td><span class="badge-profile"><?= e($u['profile']) ?></span></td>
                            <td>
                                <?php if (isset($mtOnline[$u['name']])): ?>
                                    <span class="badge-online">Online</span>
                                <?php elseif ($u['disabled']): ?>
                                    <span class="badge-disabled">Disabled</span>
                                <?php else: ?>
                                    <span class="badge-unused">Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= e($u['comment'] ?: '-') ?></td>
                            <td>
                                <a href="index.php?page=edit-user&username=<?= urlencode($u['name']) ?>"
                                   class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="index.php?page=delete-user"
                                      onsubmit="return confirm('Hapus user <?= e($u['name']) ?> dari MikroTik?')"
                                      class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="username"   value="<?= e($u['name']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function togglePass(btn) {
    const span = btn.previousElementSibling;
    const icon = btn.querySelector('i');
    if (span.textContent.trim() === '••••••••') {
        span.textContent = span.dataset.pass;
        icon.className = 'bi bi-eye-slash';
    } else {
        span.textContent = '••••••••';
        icon.className = 'bi bi-eye';
    }
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
