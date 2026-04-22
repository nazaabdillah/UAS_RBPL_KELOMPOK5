<?php
/**
 * controllers/ProfileController.php
 * Mengelola user profile hotspot langsung dari/ke MikroTik
 */

class ProfileController {

    // =========================================================
    // Profile List — ambil langsung dari MikroTik
    // =========================================================
    public function index(): void {
        requireLogin();

        $profiles = [];
        $mtError  = null;

        try {
            $api      = getMikrotikConnection();
            $rawProf  = $api->getProfiles();
            $api->disconnect();

            foreach ($rawProf as $p) {
                if (!isset($p['name'])) continue;
                $profiles[] = [
                    'name'            => $p['name'],
                    'session-timeout' => $p['session-timeout'] ?? '0s',
                    'shared-users'    => $p['shared-users']    ?? '1',
                    'rate-limit'      => $p['rate-limit']      ?? '-',
                    'idle-timeout'    => $p['idle-timeout']    ?? '-',
                    'keepalive-timeout' => $p['keepalive-timeout'] ?? '-',
                    '.id'             => $p['.id'] ?? '',
                ];
            }
        } catch (\RuntimeException $e) {
            $mtError = $e->getMessage();
        }

        $pageTitle      = 'Profile List';
        $currentPage    = 'profiles';
        $breadcrumbParent = 'User Profile';
        $breadcrumb     = 'Profile List';

        require __DIR__ . '/../views/profiles/list.php';
    }

    // =========================================================
    // Add Profile — form + kirim ke MikroTik
    // =========================================================
    public function addProfile(): void {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $pageTitle      = 'Add Profile';
            $currentPage    = 'add-profile';
            $breadcrumbParent = 'User Profile';
            $breadcrumb     = 'Add Profile';
            require __DIR__ . '/../views/profiles/add.php';
            return;
        }

        // --- Proses POST ---
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid CSRF token.');
            redirect('index.php?page=add-profile');
        }

        $name          = sanitize($_POST['name']           ?? '');
        $sessionTimeout= sanitize($_POST['session_timeout'] ?? '');
        $sharedUsers   = sanitizeInt($_POST['shared_users']  ?? 1, 1);
        $rateLimit     = sanitize($_POST['rate_limit']      ?? '');
        $idleTimeout   = sanitize($_POST['idle_timeout']    ?? '');

        // Validasi
        $errors = [];
        if ($name === '')           $errors[] = 'Nama profile wajib diisi.';
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $name)) $errors[] = 'Nama profile hanya boleh huruf, angka, _ dan -.';
        if ($sessionTimeout === '') $errors[] = 'Session timeout wajib diisi.';

        if (!empty($errors)) {
            setFlash('danger', implode('<br>', $errors));
            redirect('index.php?page=add-profile');
        }

        // Bangun parameter untuk API MikroTik
        $params = [
            "=name=$name",
            "=session-timeout=$sessionTimeout",
            "=shared-users=$sharedUsers",
        ];
        if ($rateLimit !== '')   $params[] = "=rate-limit=$rateLimit";
        if ($idleTimeout !== '') $params[] = "=idle-timeout=$idleTimeout";

        try {
            $api    = getMikrotikConnection();
            $result = $api->query('/ip/hotspot/user/profile/add', $params);

            if ($result === false) {
                throw new \RuntimeException($api->getLastError());
            }

            // Cek response untuk error
            foreach ($result as $row) {
                if (isset($row['type']) && $row['type'] === '!trap') {
                    throw new \RuntimeException($row['message'] ?? 'Unknown error');
                }
            }
            $api->disconnect();

        } catch (\RuntimeException $e) {
            setFlash('danger', 'Gagal menambah profile: ' . $e->getMessage());
            redirect('index.php?page=add-profile');
        }

        // Update juga konstanta MT_PROFILES di config (tulis ke file)
        // Ini opsional — user bisa edit manual jika mau
        logActivity('ADD_PROFILE', "Tambah profile '$name' timeout=$sessionTimeout");
        setFlash('success', "Profile '$name' berhasil ditambahkan ke MikroTik!");
        redirect('index.php?page=profiles');
    }

    // =========================================================
    // Edit Profile — form + update ke MikroTik
    // =========================================================
    public function editProfile(): void {
        requireLogin();

        $name = sanitize($_GET['name'] ?? $_POST['name_original'] ?? '');
        if ($name === '') {
            setFlash('danger', 'Nama profile tidak valid.');
            redirect('index.php?page=profiles');
        }

        // Ambil data profile dari MikroTik
        $profileData = null;
        $mtError     = null;

        try {
            $api    = getMikrotikConnection();
            $result = $api->query('/ip/hotspot/user/profile/print', ["?name=$name"]);
            $api->disconnect();

            foreach ($result as $row) {
                if (!isset($row['name'])) continue;
                $profileData = [
                    'name'            => $row['name'],
                    'session-timeout' => $row['session-timeout'] ?? '0s',
                    'shared-users'    => $row['shared-users']    ?? '1',
                    'rate-limit'      => $row['rate-limit']      ?? '',
                    'idle-timeout'    => $row['idle-timeout']    ?? '',
                    '.id'             => $row['.id']             ?? '',
                ];
                break;
            }

            if ($profileData === null) {
                setFlash('danger', "Profile '$name' tidak ditemukan di MikroTik.");
                redirect('index.php?page=profiles');
            }

        } catch (\RuntimeException $e) {
            $mtError     = $e->getMessage();
            $profileData = ['name' => $name, 'session-timeout' => '', 'shared-users' => '1', 'rate-limit' => '', 'idle-timeout' => ''];
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $pageTitle        = 'Edit Profile';
            $currentPage      = 'profiles';
            $breadcrumbParent = 'User Profile';
            $breadcrumb       = 'Edit Profile';
            require __DIR__ . '/../views/profiles/edit.php';
            return;
        }

        // ── Proses POST ──
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid CSRF token.');
            redirect('index.php?page=edit-profile&name=' . urlencode($name));
        }

        $sessionTimeout = sanitize($_POST['session_timeout'] ?? '');
        $sharedUsers    = sanitizeInt($_POST['shared_users']  ?? 1, 1);
        $rateLimit      = sanitize($_POST['rate_limit']       ?? '');
        $idleTimeout    = sanitize($_POST['idle_timeout']     ?? '');

        if ($sessionTimeout === '') {
            setFlash('danger', 'Session timeout wajib diisi.');
            redirect('index.php?page=edit-profile&name=' . urlencode($name));
        }

        $fields = [
            'session-timeout' => $sessionTimeout,
            'shared-users'    => (string)$sharedUsers,
            'rate-limit'      => $rateLimit,
            'idle-timeout'    => $idleTimeout,
        ];

        try {
            $api = getMikrotikConnection();
            $ok  = $api->updateHotspotProfile($name, $fields);
            $api->disconnect();

            if (!$ok) throw new \RuntimeException($api->getLastError());

        } catch (\RuntimeException $e) {
            setFlash('danger', 'Gagal update profile di MikroTik: ' . $e->getMessage());
            redirect('index.php?page=edit-profile&name=' . urlencode($name));
        }

        logActivity('EDIT_PROFILE', "Edit profile '$name' timeout=$sessionTimeout");
        setFlash('success', "Profile '$name' berhasil diperbarui.");
        redirect('index.php?page=profiles');
    }

    // =========================================================
    // Delete Profile dari MikroTik
    // =========================================================
    public function deleteProfile(): void {
        requireLogin();

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid CSRF token.');
            redirect('index.php?page=profiles');
        }

        $name = sanitize($_POST['name'] ?? '');
        if ($name === '') {
            setFlash('danger', 'Nama profile tidak valid.');
            redirect('index.php?page=profiles');
        }

        // Jangan izinkan hapus profile 'default'
        if ($name === 'default') {
            setFlash('warning', "Profile 'default' tidak bisa dihapus.");
            redirect('index.php?page=profiles');
        }

        try {
            $api    = getMikrotikConnection();

            // Cari .id profile
            $result = $api->query('/ip/hotspot/user/profile/print', ["?name=$name"]);
            $found  = array_filter($result ?? [], fn($r) => isset($r['.id']));

            if (empty($found)) {
                $api->disconnect();
                setFlash('warning', "Profile '$name' tidak ditemukan.");
                redirect('index.php?page=profiles');
            }

            $row = reset($found);
            $delResult = $api->query('/ip/hotspot/user/profile/remove', ["=.id={$row['.id']}"]);
            $api->disconnect();

            foreach ($delResult ?? [] as $r) {
                if (isset($r['type']) && $r['type'] === '!trap') {
                    throw new \RuntimeException($r['message'] ?? 'Delete failed');
                }
            }

        } catch (\RuntimeException $e) {
            setFlash('danger', 'Gagal hapus profile: ' . $e->getMessage());
            redirect('index.php?page=profiles');
        }

        logActivity('DELETE_PROFILE', "Hapus profile '$name'");
        setFlash('success', "Profile '$name' berhasil dihapus.");
        redirect('index.php?page=profiles');
    }
}
