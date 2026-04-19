<?php
/**
 * controllers/UserController.php
 * Mengelola user hotspot langsung dari/ke MikroTik
 */

class UserController {

    // =========================================================
    // User List — ambil langsung dari MikroTik
    // =========================================================
    public function index(): void {
        requireLogin();

        $mtUsers  = [];
        $mtError  = null;
        $mtOnline = []; // username yang sedang aktif

        try {
            $api     = getMikrotikConnection();
            $rawUsers  = $api->getHotspotUsers();
            $rawActive = $api->query('/ip/hotspot/active/print') ?: [];
            $api->disconnect();

            // Normalisasi data user
            foreach ($rawUsers as $u) {
                if (!isset($u['name'])) continue;
                $mtUsers[] = [
                    'name'     => $u['name'],
                    'password' => $u['password'] ?? '-',
                    'profile'  => $u['profile']  ?? '-',
                    'comment'  => $u['comment']  ?? '',
                    'disabled' => ($u['disabled'] ?? 'false') === 'true',
                    'bytes-in' => $u['bytes-in']  ?? '0',
                    'bytes-out'=> $u['bytes-out'] ?? '0',
                    '.id'      => $u['.id'] ?? '',
                ];
            }

            // Buat set username yang sedang aktif/online
            foreach ($rawActive as $a) {
                if (isset($a['user'])) {
                    $mtOnline[$a['user']] = true;
                }
            }

        } catch (\RuntimeException $e) {
            $mtError = $e->getMessage();
        }

        // Filter pencarian
        $search = sanitize($_GET['search'] ?? '');
        if ($search !== '') {
            $mtUsers = array_filter($mtUsers, fn($u) =>
                stripos($u['name'], $search) !== false ||
                stripos($u['profile'], $search) !== false
            );
        }

        $pageTitle      = 'User List';
        $currentPage    = 'users';
        $breadcrumbParent = 'Users';
        $breadcrumb     = 'User List';

        require __DIR__ . '/../views/users/list.php';
    }

    // =========================================================
    // Add User — form + simpan ke MikroTik & DB
    // =========================================================
    public function addUser(): void {
        requireLogin();

        // Ambil daftar profile dari MikroTik untuk dropdown
        $profiles = [];
        $mtError  = null;
        try {
            $api      = getMikrotikConnection();
            $rawProf  = $api->getProfiles();
            $api->disconnect();
            foreach ($rawProf as $p) {
                if (isset($p['name'])) $profiles[] = $p['name'];
            }
        } catch (\RuntimeException $e) {
            $mtError = $e->getMessage();
            // Fallback: gunakan profile dari config
            $profiles = array_keys(defined('MT_PROFILES') ? MT_PROFILES : []);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $pageTitle      = 'Add User';
            $currentPage    = 'add-user';
            $breadcrumbParent = 'Users';
            $breadcrumb     = 'Add User';
            require __DIR__ . '/../views/users/add.php';
            return;
        }

        // --- Proses POST ---
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid CSRF token.');
            redirect('index.php?page=add-user');
        }

        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $profile  = sanitize($_POST['profile'] ?? '');
        $comment  = sanitize($_POST['comment']  ?? '');

        // Validasi
        $errors = [];
        if ($username === '')      $errors[] = 'Username wajib diisi.';
        if (strlen($username) < 3) $errors[] = 'Username minimal 3 karakter.';
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) $errors[] = 'Username hanya boleh huruf, angka, _ dan -.';
        if ($password === '')      $errors[] = 'Password wajib diisi.';
        if ($profile  === '')      $errors[] = 'Profile wajib dipilih.';

        if (!empty($errors)) {
            setFlash('danger', implode('<br>', $errors));
            redirect('index.php?page=add-user');
        }

        // Kirim ke MikroTik
        try {
            $api = getMikrotikConnection();
            $ok  = $api->addHotspotUser($username, $password, $profile, $comment);
            if (!$ok) {
                throw new \RuntimeException($api->getLastError());
            }
            $api->disconnect();
        } catch (\RuntimeException $e) {
            setFlash('danger', 'Gagal menambah user ke MikroTik: ' . $e->getMessage());
            redirect('index.php?page=add-user');
        }

        // Simpan juga ke database lokal
        $voucherModel = new VoucherModel();
        if (!$voucherModel->usernameExists($username)) {
            $voucherModel->create([
                'username'   => $username,
                'password'   => $password,
                'profile'    => $profile,
                'comment'    => $comment,
                'created_by' => $_SESSION['admin_id'],
            ]);
        }

        logActivity('ADD_USER', "Tambah user '$username' profile=$profile");
        setFlash('success', "User '$username' berhasil ditambahkan ke MikroTik!");
        redirect('index.php?page=users');
    }

    // =========================================================
    // Delete User dari MikroTik (via POST)
    // =========================================================
    public function deleteUser(): void {
        requireLogin();

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid CSRF token.');
            redirect('index.php?page=users');
        }

        $username = sanitize($_POST['username'] ?? '');
        if ($username === '') {
            setFlash('danger', 'Username tidak valid.');
            redirect('index.php?page=users');
        }

        try {
            $api = getMikrotikConnection();
            $ok  = $api->removeHotspotUser($username);
            $api->disconnect();

            if (!$ok) {
                setFlash('warning', "User '$username' tidak ditemukan di MikroTik, mungkin sudah dihapus.");
            } else {
                // Hapus juga dari DB lokal jika ada
                $voucherModel = new VoucherModel();
                $v = $voucherModel->findByUsername($username);
                if ($v) $voucherModel->delete((int)$v['id']);

                logActivity('DELETE_USER', "Hapus user '$username' dari MikroTik");
                setFlash('success', "User '$username' berhasil dihapus dari MikroTik.");
            }
        } catch (\RuntimeException $e) {
            setFlash('danger', 'Gagal hapus user: ' . $e->getMessage());
        }

        redirect('index.php?page=users');
    }
}
