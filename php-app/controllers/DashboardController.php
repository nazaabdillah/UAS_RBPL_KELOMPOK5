<?php
/**
 * controllers/DashboardController.php
 * Data untuk halaman dashboard
 */

class DashboardController {
    private VoucherModel $voucherModel;

    public function __construct() {
        $this->voucherModel = new VoucherModel();
    }

    public function index(): void {
        requireLogin();

        $stats          = $this->voucherModel->getStats();
        $profileStats   = $this->voucherModel->getStatsByProfile();
        $recentVouchers = $this->voucherModel->getRecent(8);

        // Test koneksi MikroTik (non-blocking, timeout pendek)
        $mtStatus = 'unknown';
        try {
            require_once __DIR__ . '/../mikrotik/RouterosAPI.php';
            require_once __DIR__ . '/../config/mikrotik.php';
            $api = new RouterosAPI(MT_HOST, MT_USER, MT_PASS, MT_PORT, 3);
            $mtStatus = $api->connect() ? 'online' : 'offline';
            $api->disconnect();
        } catch (\Exception $e) {
            $mtStatus = 'offline';
        }

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
