<?php
/**
 * models/AdminModel.php
 * Query database untuk tabel admins
 */

class AdminModel {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Mencari admin berdasarkan username
     */
    public function findByUsername(string $username): array|false {
        $stmt = $this->db->prepare(
            'SELECT id, username, password, full_name FROM admins WHERE username = :username LIMIT 1'
        );
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    /**
     * Verifikasi password admin (bcrypt)
     */
    public function verifyPassword(string $inputPassword, string $hash): bool {
        return password_verify($inputPassword, $hash);
    }

    /**
     * Update password admin
     */
    public function updatePassword(int $adminId, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            'UPDATE admins SET password = :password WHERE id = :id'
        );
        return $stmt->execute([':password' => $hash, ':id' => $adminId]);
    }
}
