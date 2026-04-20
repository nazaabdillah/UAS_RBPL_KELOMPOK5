<?php
/**
 * config/mikrotik.php
 * Konfigurasi koneksi ke MikroTik Router
 * Gunakan environment variable di production untuk keamanan
 */

define('MT_HOST',     getenv('MT_HOST')     ?: '192.168.20.1');    // IP Router MikroTik
define('MT_USER',     getenv('MT_USER')     ?: 'admin');          // Username API MikroTik
define('MT_PASS',     getenv('MT_PASS')     ?: '277353');               // Password API MikroTik
define('MT_PORT',     getenv('MT_PORT')     ?: 8728);             // Port API (default: 8728)
define('MT_TIMEOUT',  10);                                         // Timeout koneksi dalam detik

/**
 * Daftar profile hotspot yang tersedia
 * Sesuaikan dengan nama profile yang ada di MikroTik Anda
 * Format: 'nama_profile' => 'Label Tampilan'
 */
define('MT_PROFILES', [
    '1jam'   => '1 Jam',
    '3jam'   => '3 Jam',
    '1hari'  => '1 Hari',
    '3hari'  => '3 Hari',
    '1minggu'=> '1 Minggu',
]);
