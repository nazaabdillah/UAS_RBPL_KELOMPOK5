<?php
$pageTitle   = 'Dashboard';
$currentPage = 'dashboard';
$breadcrumb  = 'Dashboard';
require __DIR__ . '/../layout/header.php';
?>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card--blue">
            <div class="stat-icon"><i class="bi bi-ticket-perforated"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['total']) ?></div>
                <div class="stat-label">Total Voucher</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card--green">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['unused']) ?></div>
                <div class="stat-label">Tersedia</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card--orange">
            <div class="stat-icon"><i class="bi bi-person-check"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['used']) ?></div>
                <div class="stat-label">Terpakai</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card--<?= $mtStatus === 'online' ? 'green' : 'red' ?>">
            <div class="stat-icon">
                <i class="bi bi-router<?= $mtStatus === 'online' ? '-fill' : '' ?>"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value" style="font-size:1.2rem;text-transform:uppercase;">
                    <?= $mtStatus === 'online' ? 'Online' : 'Offline' ?>
                </div>
                <div class="stat-label">Status MikroTik</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Voucher per Profile -->
    <div class="col-lg-5">
        <div class="card-panel">
            <div class="panel-header">
                <h3><i class="bi bi-bar-chart me-2"></i>Voucher per Profile</h3>
            </div>
            <div class="panel-body">
                <?php if (empty($profileStats)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Belum ada data voucher</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($profileStats as $ps): ?>
                        <?php
                            $used    = $ps['total'] - $ps['unused'];
                            $pct     = $ps['total'] > 0 ? round($used / $ps['total'] * 100) : 0;
                        ?>
                        <div class="profile-stat-row">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="profile-name">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= e(profileLabel($ps['profile'])) ?>
                                </span>
                                <span class="profile-count">
                                    <?= $used ?>/<?= $ps['total'] ?> terpakai
                                </span>
                            </div>
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar"
                                     style="width:<?= $pct ?>%"
                                     role="progressbar">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Voucher Terbaru -->
    <div class="col-lg-7">
        <div class="card-panel">
            <div class="panel-header">
                <h3><i class="bi bi-clock-history me-2"></i>Voucher Terbaru</h3>
                <a href="index.php?page=vouchers" class="btn-text-link">Lihat Semua <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="panel-body p-0">
                <?php if (empty($recentVouchers)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Belum ada voucher</p>
                    </div>
                <?php else: ?>
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Profile</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentVouchers as $v): ?>
                            <tr>
                                <td class="font-mono fw-semibold"><?= e($v['username']) ?></td>
                                <td>
                                    <span class="badge-profile"><?= e(profileLabel($v['profile'])) ?></span>
                                </td>
                                <td><?= statusBadge($v['status']) ?></td>
                                <td class="text-muted small"><?= formatDate($v['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card-panel">
            <div class="panel-header">
                <h3><i class="bi bi-lightning me-2"></i>Aksi Cepat</h3>
            </div>
            <div class="panel-body d-flex gap-3 flex-wrap">
                <a href="index.php?page=generate" class="quick-action-btn">
                    <i class="bi bi-plus-circle"></i>
                    Generate Voucher
                </a>
                <a href="index.php?page=vouchers&status=unused" class="quick-action-btn quick-action-btn--outline">
                    <i class="bi bi-list-check"></i>
                    Lihat Tersedia
                </a>
                <a href="index.php?page=sync"
                   class="quick-action-btn quick-action-btn--outline"
                   onclick="return confirm('Sinkronisasi status voucher dengan MikroTik?')">
                    <i class="bi bi-arrow-repeat"></i>
                    Sync MikroTik
                </a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
