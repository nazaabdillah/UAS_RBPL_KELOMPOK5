-- ============================================
-- WiFi Voucher Management System
-- Database Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS wifi_voucher CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wifi_voucher;

-- ============================================
-- Tabel: admins
-- Menyimpan data akun administrator
-- ============================================
CREATE TABLE IF NOT EXISTS admins (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- bcrypt hash
    full_name   VARCHAR(100) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin: username=admin, password=admin123
INSERT INTO admins (username, password, full_name) VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'Administrator'
);

-- ============================================
-- Tabel: vouchers
-- Menyimpan data voucher hotspot yang digenerate
-- ============================================
CREATE TABLE IF NOT EXISTS vouchers (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) NOT NULL UNIQUE,
    password    VARCHAR(50) NOT NULL,
    profile     VARCHAR(50) NOT NULL,           -- nama profile di MikroTik
    status      ENUM('unused','used') DEFAULT 'unused',
    comment     VARCHAR(255) DEFAULT NULL,
    created_by  INT UNSIGNED DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at     TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- Tabel: activity_logs
-- Menyimpan log aktivitas admin (opsional)
-- ============================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id    INT UNSIGNED DEFAULT NULL,
    action      VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    ip_address  VARCHAR(45) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- Index untuk performa query
-- ============================================
CREATE INDEX idx_vouchers_status ON vouchers(status);
CREATE INDEX idx_vouchers_profile ON vouchers(profile);
CREATE INDEX idx_activity_logs_admin ON activity_logs(admin_id);
