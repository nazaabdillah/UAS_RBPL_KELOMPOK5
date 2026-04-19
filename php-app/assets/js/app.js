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
    // Animasi Progress Bar (Dashboard)
    // =========================================================
    const progressBars = document.querySelectorAll('.progress-bar');
    if (progressBars.length > 0) {
        // Reset width ke 0 dulu, lalu animasikan
        progressBars.forEach(function (bar) {
            const targetWidth = bar.style.width;
            bar.style.width   = '0%';
            setTimeout(function () {
                bar.style.transition = 'width 1s ease';
                bar.style.width      = targetWidth;
            }, 200);
        });
    }

    // =========================================================
    // Konfirmasi Hapus dengan tombol lebih baik
    // =========================================================
    // Sudah pakai inline onclick confirm(), tidak perlu override

    // =========================================================
    // Highlight baris tabel yang baru saja diaksi
    // =========================================================
    const urlParams = new URLSearchParams(window.location.search);
    const highlight = urlParams.get('highlight');
    if (highlight) {
        const rows = document.querySelectorAll('tr[data-id="' + highlight + '"]');
        rows.forEach(function (row) {
            row.style.background = 'rgba(59,130,246,0.08)';
            setTimeout(function () {
                row.style.transition = 'background 1s ease';
                row.style.background = '';
            }, 2000);
        });
    }

    // =========================================================
    // Stat Card: Animasi angka counter
    // =========================================================
    function animateCounter(el) {
        const target = parseInt(el.textContent.replace(/\D/g, ''), 10);
        if (isNaN(target) || target === 0) return;

        let current  = 0;
        const step   = Math.max(1, Math.ceil(target / 40));
        const timer  = setInterval(function () {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = current.toLocaleString('id-ID');
        }, 20);
    }

    document.querySelectorAll('.stat-value').forEach(function (el) {
        // Hanya angka murni (bukan teks seperti "Online")
        if (/^\d[\d.,]*$/.test(el.textContent.trim())) {
            animateCounter(el);
        }
    });

    // =========================================================
    // Select All / Deselect All Checkbox
    // =========================================================
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        // Sinkronisasi state checkAll jika semua row dicentang manual
        document.querySelectorAll('.row-check').forEach(function (cb) {
            cb.addEventListener('change', function () {
                const all     = document.querySelectorAll('.row-check');
                const checked = document.querySelectorAll('.row-check:checked');
                checkAll.indeterminate = checked.length > 0 && checked.length < all.length;
                checkAll.checked       = checked.length === all.length;
            });
        });
    }

    // =========================================================
    // Form Validation Feedback
    // =========================================================
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // =========================================================
    // Copy to Clipboard (username/password)
    // =========================================================
    document.querySelectorAll('[data-copy]').forEach(function (el) {
        el.style.cursor = 'pointer';
        el.title        = 'Klik untuk menyalin';
        el.addEventListener('click', function () {
            navigator.clipboard.writeText(el.dataset.copy).then(function () {
                const orig      = el.textContent;
                el.textContent  = '✓ Disalin!';
                el.style.color  = '#22c55e';
                setTimeout(function () {
                    el.textContent = orig;
                    el.style.color = '';
                }, 1500);
            });
        });
    });

    // =========================================================
    // Tooltip Bootstrap (jika ada elemen [data-bs-toggle="tooltip"])
    // =========================================================
    const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipEls.forEach(function (el) {
        new bootstrap.Tooltip(el);
    });

});
