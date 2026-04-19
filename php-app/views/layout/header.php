<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'WiFi Voucher Manager') ?> — VoucherNet</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php
$cp = $currentPage ?? '';
function navActive(string $page): string { global $cp; return $cp === $page ? 'active' : ''; }
$groupUsers    = in_array($cp, ['users', 'add-user', 'generate']);
$groupProfiles = in_array($cp, ['profiles', 'add-profile']);
$groupVouchers = in_array($cp, ['vouchers', 'quick-print']);
?>

<div class="wrapper">
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-wifi"></i></div>
        <div class="brand-text">
            <div class="brand-name">VoucherNet</div>
            <div class="brand-sub">WiFi Management</div>
        </div>
    </div>

    <div class="sidebar-scroll">
        <ul class="sidebar-nav">

            <li class="nav-label">UTAMA</li>
            <li>
                <a href="index.php?page=dashboard" class="nav-link-item <?= navActive('dashboard') ?>">
                    <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                </a>
            </li>

            <li class="nav-label">HOTSPOT</li>

            <!-- USERS submenu -->
            <li class="has-submenu <?= $groupUsers ? 'open' : '' ?>">
                <a href="#" class="nav-link-item nav-parent <?= $groupUsers ? 'active-parent' : '' ?>" data-target="sub-users">
                    <i class="bi bi-people"></i><span>Users</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <ul class="submenu" id="sub-users" <?= $groupUsers ? 'style="display:block"' : '' ?>>
                    <li><a href="index.php?page=users" class="submenu-link <?= navActive('users') ?>">
                        <i class="bi bi-list-ul"></i>User List</a></li>
                    <li><a href="index.php?page=add-user" class="submenu-link <?= navActive('add-user') ?>">
                        <i class="bi bi-person-plus"></i>Add User</a></li>
                    <li><a href="index.php?page=generate" class="submenu-link <?= navActive('generate') ?>">
                        <i class="bi bi-lightning-charge"></i>Generate User</a></li>
                </ul>
            </li>

            <!-- USER PROFILE submenu -->
            <li class="has-submenu <?= $groupProfiles ? 'open' : '' ?>">
                <a href="#" class="nav-link-item nav-parent <?= $groupProfiles ? 'active-parent' : '' ?>" data-target="sub-profiles">
                    <i class="bi bi-pie-chart"></i><span>User Profile</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <ul class="submenu" id="sub-profiles" <?= $groupProfiles ? 'style="display:block"' : '' ?>>
                    <li><a href="index.php?page=profiles" class="submenu-link <?= navActive('profiles') ?>">
                        <i class="bi bi-list-ul"></i>Profile List</a></li>
                    <li><a href="index.php?page=add-profile" class="submenu-link <?= navActive('add-profile') ?>">
                        <i class="bi bi-plus-circle"></i>Add Profile</a></li>
                </ul>
            </li>

            <li class="nav-label">VOUCHER</li>

            <!-- VOUCHER submenu -->
            <li class="has-submenu <?= $groupVouchers ? 'open' : '' ?>">
                <a href="#" class="nav-link-item nav-parent <?= $groupVouchers ? 'active-parent' : '' ?>" data-target="sub-vouchers">
                    <i class="bi bi-ticket-perforated"></i><span>Voucher</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <ul class="submenu" id="sub-vouchers" <?= $groupVouchers ? 'style="display:block"' : '' ?>>
                    <li><a href="index.php?page=vouchers" class="submenu-link <?= navActive('vouchers') ?>">
                        <i class="bi bi-list-ul"></i>Voucher List</a></li>
                    <li><a href="index.php?page=quick-print" class="submenu-link <?= navActive('quick-print') ?>" target="_blank">
                        <i class="bi bi-printer"></i>Quick Print</a></li>
                </ul>
            </li>

            <li class="nav-label">SISTEM</li>
            <li>
                <a href="index.php?page=sync" class="nav-link-item"
                   onclick="return confirm('Sinkronisasi status voucher dengan MikroTik?')">
                    <i class="bi bi-arrow-repeat"></i><span>Sync MikroTik</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=logout" class="nav-link-item"
                   onclick="return confirm('Yakin ingin logout?')">
                    <i class="bi bi-box-arrow-right"></i><span>Logout</span>
                </a>
            </li>

        </ul>
    </div>

    <div class="sidebar-footer">
        <div class="admin-info">
            <div class="admin-avatar"><i class="bi bi-person-fill"></i></div>
            <div>
                <div class="admin-name"><?= e($_SESSION['admin_name'] ?? 'Admin') ?></div>
                <div class="admin-role">Administrator</div>
            </div>
        </div>
    </div>
</nav>

<div id="main-content">
    <header class="topbar">
        <button class="btn-toggle-sidebar" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <div class="topbar-title">
            <h1><?= e($pageTitle ?? 'Dashboard') ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php?page=dashboard">Home</a></li>
                    <?php if (isset($breadcrumbParent)): ?><li class="breadcrumb-item"><?= e($breadcrumbParent) ?></li><?php endif; ?>
                    <?php if (isset($breadcrumb)): ?><li class="breadcrumb-item active"><?= e($breadcrumb) ?></li><?php endif; ?>
                </ol>
            </nav>
        </div>
        <div class="topbar-right">
            <div class="topbar-time" id="clock"></div>
        </div>
    </header>

    <div class="px-4 pt-3"><?= getFlash() ?></div>
    <main class="content-area">
