<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'WiFi Voucher Manager') ?> — VoucherNet</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- [BUG FIX] Style tambahan untuk form button di sidebar agar konsisten -->
    <style>
        #logoutForm .nav-link-item,
        #syncForm .nav-link-item {
            cursor: pointer;
    }
    
    /* FORCE ACTIVE MENU UNGU */
    .sidebar-nav li a.active,
    .sidebar-nav li button.active,
    .nav-link-item.active {
        background: #8b5cf6 !important;
        color: #ffffff !important;
        border-radius: 8px !important;
    }
    
    .sidebar-nav li a.active i,
    .sidebar-nav li button.active i {
        color: #ffffff !important;
    }
</style>
</head>
<body>

<?php
$currentPage = $currentPage ?? 'dashboard';
$pageTitle = $pageTitle ?? 'Dashboard';


function navActive(string $page): string { 
    global $currentPage; 
    return $currentPage === $page ? 'active' : ''; 
}

$groupUsers    = in_array($currentPage, ['users', 'add-user', 'generate']);
$groupProfiles = in_array($currentPage, ['profiles', 'add-profile']);
$groupVouchers = in_array($currentPage, ['vouchers', 'quick-print']);
?>

<div class="wrapper">
<div class="sidebar-overlay" id="sidebarOverlay"></div>

        <nav id="sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon"><i class="bi bi-wifi"></i></div>
                <div class="brand-text">
                    <div class="brand-name">VoucherNet</div>
                    <div class="brand-sub">Panel Manajemen Voucher</div>
                </div>
            </div>

            <div class="sidebar-scroll">
                <ul class="sidebar-nav">

            <li class="nav-label">UTAMA</li>
            <li>
                <a href="index.php?page=dashboard" class="nav-link-item <?= navActive('dashboard') ?>" style="<?= $currentPage == 'dashboard' ? 'background: #4922a5 !important; color: #ffffff !important; border-radius: 8px !important;' : '' ?>">
                    <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                </a>
            </li>

            <li class="nav-label">HOTSPOT</li>

            <!-- USERS submenu -->
            <li class="has-submenu <?= $groupUsers ? 'open' : '' ?>">
                <a href="#" class="nav-link-item nav-parent <?= $groupUsers ? 'active-parent' : '' ?>" data-target="sub-users" style="<?= ($currentPage == 'users' || $currentPage == 'add-user' || $currentPage == 'generate') ? 'background: #4922a5 !important; color: #ffffff !important; border-radius: 8px !important;' : '' ?>">
                    <i class="bi bi-people"></i><span>Users</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <ul class="submenu" id="sub-users" <?= $groupUsers ? 'style="display:block"' : '' ?>>
                    <li><a href="index.php?page=users" class="submenu-link <?= navActive('users') ?>" style="<?= $currentPage == 'users' ? 'background: #7861ad !important; color: #ffffff !important; border-radius: 8px !important;' : '' ?>">
                        <i class="bi bi-list-ul"></i>User List</a></li>
                    <li><a href="index.php?page=add-user" class="submenu-link <?= navActive('add-user') ?>" style="<?= $currentPage == 'add-user' ? 'background: #7861ad !important; color: #ffffff !important; border-radius: 8px !important;' : '' ?>">
                        <i class="bi bi-person-plus"></i>Add User</a></li>
                    <li><a href="index.php?page=generate" class="submenu-link <?= navActive('generate') ?>" style="<?= $currentPage == 'generate' ? 'background: #7861ad !important; color: #ffffff !important; border-radius: 8px !important;' : '' ?>">
                        <i class="bi bi-lightning-charge"></i>Generate User</a></li>
                </ul>
            </li>

            <!-- USER PROFILE submenu -->
            <li class="has-submenu <?= $groupProfiles ? 'open' : '' ?>">
                <a href="#" class="nav-link-item nav-parent <?= $groupProfiles ? 'active-parent' : '' ?>" data-target="sub-profiles" style="<?= ($currentPage == 'profiles' || $currentPage == 'add-profile') ? 'background: #4922a5 !important; color: #ffffff !important; border-radius: 8px !important;' : '' ?>">
                    <i class="bi bi-pie-chart"></i><span>User Profile</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <ul class="submenu" id="sub-profiles" <?= $groupProfiles ? 'style="display:block"' : '' ?>>
                    <li><a href="index.php?page=profiles" class="submenu-link <?= navActive('profiles') ?>" style="<?= $currentPage == 'profiles' ? 'background: #7861ad !important; color: #ffffff !important; border-radius: 8px !important;' : '' ?>">
                        <i class="bi bi-list-ul"></i>Profile List</a></li>
                    <li><a href="index.php?page=add-profile" class="submenu-link <?= navActive('add-profile') ?>" style="<?= $currentPage == 'add-profile' ? 'background: #7861ad !important; color: #ffffff !important; border-radius: 8px !important;' : '' ?>">
                        <i class="bi bi-plus-circle"></i>Add Profile</a></li>
                </ul>
            </li>

            <li class="nav-label">VOUCHER</li>

            <!-- VOUCHER submenu -->
            <li class="has-submenu <?= $groupVouchers ? 'open' : '' ?>">
                <a href="#" class="nav-link-item nav-parent <?= $groupVouchers ? 'active-parent' : '' ?>" data-target="sub-vouchers" style="<?= ($currentPage == 'vouchers' || $currentPage == 'quick-print') ? 'background: #4922a5 !important; color: #ffffff !important; border-radius: 8px !important;' : '' ?>">
                    <i class="bi bi-ticket-perforated"></i><span>Voucher</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <ul class="submenu" id="sub-vouchers" <?= $groupVouchers ? 'style="display:block"' : '' ?>>
                    <li><a href="index.php?page=vouchers" class="submenu-link <?= navActive('vouchers') ?>" style="<?= $currentPage == 'vouchers' ? 'background: #7861ad !important; color: #ffffff !important; border-radius: 8px !important;' : '' ?>">
                        <i class="bi bi-list-ul"></i>Voucher List</a></li>
                    <li><a href="index.php?page=quick-print" class="submenu-link <?= navActive('quick-print') ?>" style="<?= $currentPage == 'quick-print' ? 'background: #7861ad !important; color: #ffffff !important; border-radius: 8px !important;' : '' ?>" target="_blank">
                        <i class="bi bi-printer"></i>Quick Print</a></li>
                </ul>
            </li>
            

            <li class="nav-label">SISTEM</li>

            <!-- Toggle Dark/Light Mode -->
            <li>
                <button type="button" class="nav-link-item w-100 text-start border-0 bg-transparent" id="themeToggle">
                    <i class="bi bi-moon-stars"></i><span>Mode Gelap</span>
                </button>
            </li>

            <li>
                <a href="index.php?page=captive-portal" class="nav-link-item <?= navActive('captive-portal') ?>">
                    <i class="bi bi-globe2"></i><span>Captive Portal</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=captive-portal" class="nav-link-item <?= navActive('captive-portal') ?>" style="<?= $currentPage == 'captive-portal' ? 'background: #9380c1 !important; color: #ffffff !important; border-radius: 8px !important;' : '' ?>">
                    <i class="bi bi-globe2"></i><span>Captive Portal</span>
                </a>
            </li>
            
            <li>
                <form method="POST" action="index.php?page=sync" id="syncForm" style="margin:0">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="button" class="nav-link-item w-100 text-start border-0 bg-transparent" style="<?= $currentPage == 'sync' ? 'background: #9380c1 !important; color: #ffffff !important; border-radius: 8px !important;' : '' ?>"
                            onclick="if(confirm('Sinkronisasi status voucher dengan MikroTik?')) document.getElementById('syncForm').submit()">
                        <i class="bi bi-arrow-repeat"></i><span>Sync MikroTik</span>
                    </button>
                </form>
            </li>
            
            <li>
                <form method="POST" action="index.php?page=logout" id="logoutForm" style="margin:0">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="button" class="nav-link-item w-100 text-start border-0" 
                            style="background: #dc2626 !important; color: #ffffff !important; border-radius: 8px !important; margin-top: 16px;"
                            onclick="if(confirm('Yakin ingin logout?')) document.getElementById('logoutForm').submit()">
                        <i class="bi bi-box-arrow-right"></i><span>Logout</span>
                    </button>
                </form>
            </li>

        </ul>
    </div>

    <div class="sidebar-footer">
        <div class="admin-info">
            <div class="admin-avatar"><i class="bi bi-person-fill"></i></div>
            <div>
                <div class="admin-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></div>
                <div class="admin-role">Administrator</div>
            </div>
        </div>
    </div>
</nav>

<div id="main-content">
    <header class="topbar">
        <button class="btn-toggle-sidebar" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <div class="topbar-title">
            <h1><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php?page=dashboard">Home</a></li>
                    <?php if (isset($breadcrumbParent)): ?><li class="breadcrumb-item"><?= htmlspecialchars($breadcrumbParent) ?></li><?php endif; ?>
                    <?php if (isset($breadcrumb)): ?><li class="breadcrumb-item active"><?= htmlspecialchars($breadcrumb) ?></li><?php endif; ?>
                </ol>
            </nav>
        </div>
        <div class="topbar-right">
            <div class="topbar-time" id="clock"></div>
        </div>
    </header>

    <div class="px-4 pt-3"><?= getFlash() ?></div>
    <main class="content-area">