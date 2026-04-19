<?php
/**
 * helpers/functions.php
 * Fungsi-fungsi utilitas yang dipakai di seluruh aplikasi
 */

// =========================================================
// Session & Auth Helpers
// =========================================================

/**
 * Memastikan user sudah login, redirect ke login jika belum
 */
function requireLogin(): void {
    if (!isset($_SESSION['admin_id'])) {
        redirect('index.php?page=login');
    }
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn(): bool {
    return isset($_SESSION['admin_id']);
}

/**
 * Redirect ke URL tertentu
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

// =========================================================
// Flash Message Helpers
// =========================================================

/**
 * Menyimpan pesan flash ke session
 * @param string $type  'success' | 'danger' | 'warning' | 'info'
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Mengambil dan menghapus pesan flash dari session
 * Mengembalikan string HTML atau empty string
 */
function getFlash(): string {
    if (!isset($_SESSION['flash'])) return '';

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    // Whitelist tipe yang valid agar tidak ada CSS injection
    $allowedTypes = ['success', 'danger', 'warning', 'info'];
    $type = in_array($flash['type'], $allowedTypes, true) ? $flash['type'] : 'info';

    // Map tipe ke nama icon Bootstrap Icons yang benar
    // (tidak ada bi-danger-circle atau bi-warning-circle di Bootstrap Icons)
    $iconMap = [
        'success' => 'check-circle',
        'danger'  => 'x-circle',
        'warning' => 'exclamation-triangle',
        'info'    => 'info-circle',
    ];
    $icon = $iconMap[$type];

    // Jangan escape message karena bisa mengandung HTML valid (misal <br> dari multiple error)
    // Tapi pastikan hanya tag aman yang bisa masuk — strip semua tag kecuali <br>
    $message = strip_tags($flash['message'], '<br>');

    return <<<HTML
    <div class="alert alert-{$type} alert-dismissible fade show" role="alert">
        <i class="bi bi-{$icon} me-2"></i>{$message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    HTML;
}

// =========================================================
// Voucher Generator
// =========================================================

/**
 * Generate username voucher unik
 * Format: WF + 6 karakter alphanumeric uppercase
 */
function generateVoucherUsername(): string {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Hindari I, O, 0, 1 (mudah tertukar)
    $code  = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return 'WF' . $code;
}

/**
 * Generate password voucher
 * Format: 8 karakter alphanumeric (mixed case)
 */
function generateVoucherPassword(): string {
    $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789@#!';
    $pass  = '';
    for ($i = 0; $i < 8; $i++) {
        $pass .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $pass;
}

// =========================================================
// Input Sanitization
// =========================================================

/**
 * Sanitasi string input dari user
 */
function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitasi integer
 */
function sanitizeInt(mixed $value, int $default = 0): int {
    $val = filter_var($value, FILTER_VALIDATE_INT);
    return $val !== false ? (int)$val : $default;
}

// =========================================================
// MikroTik Connection Helper
// =========================================================

/**
 * Membuat instance RouterosAPI yang sudah terkoneksi
 * Melempar Exception jika gagal konek
 */
function getMikrotikConnection(): \RouterosAPI {
    require_once __DIR__ . '/../mikrotik/RouterosAPI.php';
    require_once __DIR__ . '/../config/mikrotik.php';

    $api = new RouterosAPI(MT_HOST, MT_USER, MT_PASS, MT_PORT, MT_TIMEOUT);

    if (!$api->connect()) {
        throw new \RuntimeException('MikroTik connection failed: ' . $api->getLastError());
    }

    return $api;
}

// =========================================================
// Activity Logger
// =========================================================

/**
 * Mencatat aktivitas admin ke database dan file log
 */
function logActivity(string $action, string $description = ''): void {
    try {
        $db = getDB();
        $stmt = $db->prepare(
            'INSERT INTO activity_logs (admin_id, action, description, ip_address)
             VALUES (:admin_id, :action, :description, :ip)'
        );
        $stmt->execute([
            ':admin_id'    => $_SESSION['admin_id'] ?? null,
            ':action'      => $action,
            ':description' => $description,
            ':ip'          => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ]);
    } catch (\Exception $e) {
        error_log('logActivity failed: ' . $e->getMessage());
    }

    // Juga tulis ke file log
    $logFile = __DIR__ . '/../logs/activity.log';
    $line    = sprintf(
        "[%s] [Admin:%s] [IP:%s] %s — %s\n",
        date('Y-m-d H:i:s'),
        $_SESSION['admin_username'] ?? '-',
        $_SERVER['REMOTE_ADDR'] ?? '-',
        $action,
        $description
    );
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

// =========================================================
// Formatting Helpers
// =========================================================

/**
 * Format tanggal ke format Indonesia
 */
function formatDate(string $datetime): string {
    $bulan = [
        1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
        7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'
    ];
    $ts = strtotime($datetime);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y H:i', $ts);
}

/**
 * Badge HTML untuk status voucher
 */
function statusBadge(string $status): string {
    return $status === 'unused'
        ? '<span class="badge badge-unused">Tersedia</span>'
        : '<span class="badge badge-used">Terpakai</span>';
}

/**
 * Escape output untuk HTML
 */
function e(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Label profile MikroTik dari konstanta MT_PROFILES
 */
function profileLabel(string $profile): string {
    $profiles = defined('MT_PROFILES') ? MT_PROFILES : [];
    return $profiles[$profile] ?? ucfirst($profile);
}
