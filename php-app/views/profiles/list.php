<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div class="text-muted small">
        <i class="bi bi-router me-1"></i>Data dari MikroTik: <strong><?= e(MT_HOST) ?></strong>
    </div>
    <a href="index.php?page=add-profile" class="btn btn-success btn-sm">
        <i class="bi bi-plus-circle me-1"></i>Add Profile
    </a>
</div>

<?php if ($mtError): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Gagal terhubung ke MikroTik: <?= e($mtError) ?>
    </div>
<?php endif; ?>

<div class="card-panel">
    <div class="panel-header">
        <h3>
            <i class="bi bi-pie-chart me-2"></i>Hotspot User Profiles
            <span class="badge bg-secondary ms-2"><?= count($profiles) ?></span>
        </h3>
    </div>
    <div class="panel-body p-0">
        <?php if (empty($profiles)): ?>
            <div class="empty-state py-5">
                <i class="bi bi-pie-chart display-4"></i>
                <p class="mt-2"><?= $mtError ? 'Tidak dapat mengambil data.' : 'Belum ada profile hotspot.' ?></p>
                <?php if (!$mtError): ?>
                    <a href="index.php?page=add-profile" class="btn btn-success btn-sm mt-1">
                        <i class="bi bi-plus-circle me-1"></i>Add Profile
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama Profile</th>
                            <th>Session Timeout</th>
                            <th>Shared Users</th>
                            <th>Rate Limit</th>
                            <th>Idle Timeout</th>
                            <th style="width:90px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($profiles as $p): ?>
                        <tr>
                            <td>
                                <span class="badge-profile fw-semibold"><?= e($p['name']) ?></span>
                                <?php if ($p['name'] === 'default'): ?>
                                    <span class="ms-1 badge bg-secondary" style="font-size:10px;">default</span>
                                <?php endif; ?>
                            </td>
                            <td class="font-mono">
                                <?= e($p['session-timeout'] === '0s' || $p['session-timeout'] === '' ? '∞ Unlimited' : $p['session-timeout']) ?>
                            </td>
                            <td class="text-center"><?= e($p['shared-users']) ?></td>
                            <td class="font-mono small"><?= e($p['rate-limit'] === '' ? '-' : $p['rate-limit']) ?></td>
                            <td class="font-mono small"><?= e($p['idle-timeout'] === '' ? '-' : $p['idle-timeout']) ?></td>
                            <td>
                                <?php if ($p['name'] !== 'default'): ?>
                                <a href="index.php?page=edit-profile&name=<?= urlencode($p['name']) ?>"
                                   class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="index.php?page=delete-profile"
                                      onsubmit="return confirm('Hapus profile <?= e($p['name']) ?>? User yang memakai profile ini akan terpengaruh.')"
                                      class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="name"       value="<?= e($p['name']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tips -->
<div class="card-panel mt-4">
    <div class="panel-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="tip-item">
                    <i class="bi bi-clock text-primary"></i>
                    <div>
                        <div class="fw-semibold small">Format Session Timeout</div>
                        <div class="text-muted" style="font-size:12px;">
                            <code>1h</code> = 1 jam &nbsp;|&nbsp;
                            <code>1d</code> = 1 hari &nbsp;|&nbsp;
                            <code>7d</code> = 1 minggu
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="tip-item">
                    <i class="bi bi-speedometer2 text-success"></i>
                    <div>
                        <div class="fw-semibold small">Format Rate Limit</div>
                        <div class="text-muted" style="font-size:12px;">
                            <code>1M/2M</code> = Upload 1Mbps / Download 2Mbps
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="tip-item">
                    <i class="bi bi-people text-orange"></i>
                    <div>
                        <div class="fw-semibold small">Shared Users</div>
                        <div class="text-muted" style="font-size:12px;">
                            Jumlah device yang bisa login dengan 1 akun bersamaan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
