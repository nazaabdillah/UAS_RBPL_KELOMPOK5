<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — VoucherNet</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">

<div class="login-wrapper">
    <!-- Left: Branding Panel -->
    <div class="login-left">
        <div class="login-brand-content">
            <div class="login-logo">
                <i class="bi bi-wifi"></i>
            </div>
            <h1 class="login-brand-name">VoucherNet</h1>
            <p class="login-brand-tagline">Sistem Manajemen Voucher WiFi<br>Terintegrasi MikroTik RouterOS</p>

            <div class="login-features">
                <div class="feat-item">
                    <i class="bi bi-lightning-charge-fill"></i>
                    <span>Generate voucher instan</span>
                </div>
                <div class="feat-item">
                    <i class="bi bi-router-fill"></i>
                    <span>Sync langsung ke MikroTik</span>
                </div>
                <div class="feat-item">
                    <i class="bi bi-printer-fill"></i>
                    <span>Cetak voucher siap pakai</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Login Form -->
    <div class="login-right">
        <div class="login-box">
            <div class="login-header">
                <h2>Selamat Datang</h2>
                <p>Masuk ke panel administrator</p>
            </div>

            <?= getFlash() ?>

            <form method="POST" action="index.php?page=login" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                <div class="form-group-custom">
                    <label for="username">Username</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-person input-icon"></i>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control"
                            placeholder="Masukkan username"
                            value="<?= e($_POST['username'] ?? '') ?>"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="form-group-custom mt-3">
                    <label for="password">Password</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-lock input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Masukkan password"
                            required
                        >
                        <button type="button" class="btn-toggle-pass" id="togglePass">
                            <i class="bi bi-eye" id="togglePassIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-login mt-4 w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Masuk
                </button>
            </form>

            <div class="login-hint">
                Default: <code>admin</code> / <code>password</code>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle password visibility
    document.getElementById('togglePass').addEventListener('click', function() {
        const pass = document.getElementById('password');
        const icon = document.getElementById('togglePassIcon');
        if (pass.type === 'password') {
            pass.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            pass.type = 'password';
            icon.className = 'bi bi-eye';
        }
    });
</script>
</body>
</html>
