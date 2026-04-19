<?php
/**
 * models/VoucherModel.php
 * Query database untuk tabel vouchers
 */

class VoucherModel {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    // =========================================================
    // READ
    // =========================================================

    /**
     * Ambil semua voucher dengan filter opsional
     * @param array $filters ['status'=>'unused'|'used', 'profile'=>'...']
     */
    public function getAll(array $filters = [], int $limit = 200, int $offset = 0): array {
        $where  = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]         = 'v.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['profile'])) {
            $where[]          = 'v.profile = :profile';
            $params[':profile'] = $filters['profile'];
        }
        if (!empty($filters['search'])) {
            $where[]          = '(v.username LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT v.*, a.username AS admin_username
                FROM vouchers v
                LEFT JOIN admins a ON a.id = v.created_by
                $whereSQL
                ORDER BY v.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Ambil satu voucher berdasarkan ID
     */
    public function findById(int $id): array|false {
        $stmt = $this->db->prepare(
            'SELECT * FROM vouchers WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Ambil voucher berdasarkan username
     */
    public function findByUsername(string $username): array|false {
        $stmt = $this->db->prepare(
            'SELECT * FROM vouchers WHERE username = :username LIMIT 1'
        );
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    /**
     * Statistik voucher untuk dashboard
     */
    public function getStats(): array {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*)                            AS total,
                COALESCE(SUM(status = 'unused'), 0) AS unused,
                COALESCE(SUM(status = 'used'),   0) AS used
             FROM vouchers"
        );
        $row = $stmt->fetch();

        // Pastikan semua nilai adalah integer, bukan NULL
        return [
            'total'  => (int)($row['total']  ?? 0),
            'unused' => (int)($row['unused'] ?? 0),
            'used'   => (int)($row['used']   ?? 0),
        ];
    }

    /**
     * Statistik per profile
     */
    public function getStatsByProfile(): array {
        $stmt = $this->db->query(
            "SELECT profile,
                    COUNT(*) AS total,
                    COALESCE(SUM(status = 'unused'), 0) AS unused
             FROM vouchers
             GROUP BY profile
             ORDER BY profile"
        );
        return $stmt->fetchAll();
    }

    /**
     * Aktivitas terbaru (voucher terbaru)
     */
    public function getRecent(int $limit = 10): array {
        $stmt = $this->db->prepare(
            "SELECT v.*, a.username AS admin_username
             FROM vouchers v
             LEFT JOIN admins a ON a.id = v.created_by
             ORDER BY v.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Total count dengan filter
     */
    public function count(array $filters = []): int {
        $where  = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]           = 'status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['profile'])) {
            $where[]            = 'profile = :profile';
            $params[':profile'] = $filters['profile'];
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM vouchers $whereSQL");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // =========================================================
    // WRITE
    // =========================================================

    /**
     * Simpan satu voucher baru
     */
    public function create(array $data): int|false {
        $stmt = $this->db->prepare(
            'INSERT INTO vouchers (username, password, profile, comment, created_by)
             VALUES (:username, :password, :profile, :comment, :created_by)'
        );
        $ok = $stmt->execute([
            ':username'   => $data['username'],
            ':password'   => $data['password'],
            ':profile'    => $data['profile'],
            ':comment'    => $data['comment']    ?? null,
            ':created_by' => $data['created_by'] ?? null,
        ]);
        return $ok ? (int)$this->db->lastInsertId() : false;
    }

    /**
     * Simpan banyak voucher sekaligus (bulk insert)
     * Lebih efisien daripada looping create()
     */
    public function bulkCreate(array $vouchers): bool {
        if (empty($vouchers)) return true;

        $placeholders = [];
        $params       = [];

        foreach ($vouchers as $i => $v) {
            $placeholders[] = "(:u$i, :p$i, :pr$i, :co$i, :cb$i)";
            $params[":u$i"]  = $v['username'];
            $params[":p$i"]  = $v['password'];
            $params[":pr$i"] = $v['profile'];
            $params[":co$i"] = $v['comment']    ?? null;
            $params[":cb$i"] = $v['created_by'] ?? null;
        }

        $sql  = 'INSERT INTO vouchers (username, password, profile, comment, created_by) VALUES '
              . implode(', ', $placeholders);
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Hapus voucher berdasarkan ID
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM vouchers WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Update status voucher (used/unused)
     */
    public function updateStatus(int $id, string $status): bool {
        $usedAt = $status === 'used' ? date('Y-m-d H:i:s') : null;
        $stmt   = $this->db->prepare(
            'UPDATE vouchers SET status = :status, used_at = :used_at WHERE id = :id'
        );
        return $stmt->execute([':status' => $status, ':used_at' => $usedAt, ':id' => $id]);
    }

    /**
     * Cek apakah username sudah dipakai di database lokal
     */
    public function usernameExists(string $username): bool {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM vouchers WHERE username = :username'
        );
        $stmt->execute([':username' => $username]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
