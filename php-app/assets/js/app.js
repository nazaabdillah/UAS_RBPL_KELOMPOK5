/**
 * assets/js/app.js
 * JavaScript utama untuk VoucherNet
 */

document.addEventListener('DOMContentLoaded', function () {

    // =========================================================
    // Sidebar Toggle (Mobile) + Overlay
    // =========================================================
    const sidebarToggle  = document.getElementById('sidebarToggle');
    const sidebar        = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar?.classList.add('open');
        sidebarOverlay?.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar?.classList.remove('open');
        sidebarOverlay?.classList.remove('show');
        document.body.style.overflow = '';
    }

    sidebarToggle?.addEventListener('click', function () {
        sidebar?.classList.contains('open') ? closeSidebar() : openSidebar();
    });
    sidebarOverlay?.addEventListener('click', closeSidebar);

    // =========================================================
    // Submenu Accordion Toggle
    // =========================================================
    document.querySelectorAll('.nav-parent[data-target]').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();

            const targetId  = this.dataset.target;
            const submenu   = document.getElementById(targetId);
            const parentLi  = this.closest('.has-submenu');

            if (!submenu || !parentLi) return;

            const isOpen = parentLi.classList.contains('open');

            // Tutup semua submenu lain (accordion behavior)
            document.querySelectorAll('.has-submenu.open').forEach(function (el) {
                if (el !== parentLi) {
                    el.classList.remove('open');
                    const sub = el.querySelector('.submenu');
                    if (sub) sub.style.display = 'none';
                }
            });

            // Toggle submenu yang diklik
            if (isOpen) {
                parentLi.classList.remove('open');
                submenu.style.display = 'none';
            } else {
                parentLi.classList.add('open');
                submenu.style.display = 'block';
            }
        });
    });

    // =========================================================
    // Live Clock di Topbar
    // =========================================================
    const clockEl = document.getElementById('clock');
    if (clockEl) {
        function updateClock() {
            const now  = new Date();
            const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
            const day  = days[now.getDay()];
            const hh   = String(now.getHours()).padStart(2, '0');
            const mm   = String(now.getMinutes()).padStart(2, '0');
            const ss   = String(now.getSeconds()).padStart(2, '0');
            clockEl.textContent = `${day} ${hh}:${mm}:${ss}`;
        }
        updateClock();
        setInterval(updateClock, 1000);
    }

    // =========================================================
    // Auto-dismiss Alert setelah 5 detik
    // =========================================================
    const alerts = document.querySelectorAll('.alert.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });
    // =========================================================
    // Dark / Light Mode Toggle
    // =========================================================
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle?.querySelector('i');
    const themeText = themeToggle?.querySelector('span');
    
    // Cek tema yang tersimpan di localStorage
    const savedTheme = localStorage.getItem('vouchernet_theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        if (themeIcon) themeIcon.className = 'bi bi-sun';
        if (themeText) themeText.textContent = 'Mode Terang';
    }
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            if (document.body.classList.contains('dark-mode')) {
                // Switch ke Light Mode
                document.body.classList.remove('dark-mode');
                localStorage.setItem('vouchernet_theme', 'light');
                if (themeIcon) themeIcon.className = 'bi bi-moon-stars';
                if (themeText) themeText.textContent = 'Mode Gelap';
            } else {
                // Switch ke Dark Mode
                document.body.classList.add('dark-mode');
                localStorage.setItem('vouchernet_theme', 'dark');
                if (themeIcon) themeIcon.className = 'bi bi-sun';
                if (themeText) themeText.textContent = 'Mode Terang';
            }
        });
    }
});
