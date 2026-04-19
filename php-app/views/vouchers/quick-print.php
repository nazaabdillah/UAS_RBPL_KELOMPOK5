<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Print Voucher — VoucherNet</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }

        /* ====== CONTROLS (layar saja) ====== */
        .print-controls {
            max-width: 960px;
            margin: 0 auto 20px;
            background: #fff;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }

        .ctrl-left { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }

        .ctrl-title {
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 800;
            color: #1e293b;
        }

        .ctrl-badge {
            background: #dbeafe;
            color: #1e40af;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px;
        }

        .ctrl-right { display: flex; gap: 8px; flex-wrap: wrap; }

        .btn-ctrl {
            padding: 8px 18px;
            border-radius: 8px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-print:hover { background: #1d4ed8; }
        .btn-back  { background: #e2e8f0; color: #475569; }
        .btn-back:hover { background: #cbd5e1; }

        /* Filter row */
        .filter-row {
            max-width: 960px;
            margin: 0 auto 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .filter-row select, .filter-row input {
            padding: 6px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
            background: #fff;
            color: #1e293b;
        }

        /* ====== VOUCHER GRID ====== */
        .voucher-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            max-width: 960px;
            margin: 0 auto;
        }

        /* ====== VOUCHER CARD ====== */
        .voucher-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            border: 1.5px solid #e2e8f0;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .vc-header {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 60%, #3b82f6 100%);
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .vc-header-icon { font-size: 20px; color: #fff; }

        .vc-brand {
            font-family: 'Syne', sans-serif;
            font-size: 14px;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.3px;
        }
        .vc-profile-label {
            font-size: 10px;
            color: rgba(255,255,255,0.78);
            margin-top: 2px;
        }

        .vc-body { padding: 12px 14px; }

        .vc-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .vc-row:last-child { border-bottom: none; }
        .vc-label {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .vc-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
        }
        .vc-value.password { color: #1e40af; font-size: 14px; }

        .vc-footer {
            background: #f8fafc;
            padding: 7px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e2e8f0;
        }
        .vc-date { font-size: 9px; color: #94a3b8; }
        .vc-validity {
            background: #dbeafe;
            color: #1e40af;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 99px;
        }

        /* Empty state */
        .empty-print {
            max-width: 960px;
            margin: 40px auto;
            text-align: center;
            background: #fff;
            padding: 60px 20px;
            border-radius: 12px;
            color: #94a3b8;
        }
        .empty-print h3 { color: #475569; margin-bottom: 8px; }

        /* ====== PRINT STYLES ====== */
        @media print {
            body { background: #fff; padding: 0; }
            .print-controls, .filter-row { display: none; }
            .voucher-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
                max-width: 100%;
                margin: 0;
            }
            .voucher-card {
                border: 1px solid #d1d5db;
                border-radius: 8px;
            }
            @page { size: A4; margin: 12mm; }
        }

        @media (max-width: 600px) {
            .voucher-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<!-- Controls -->
<div class="print-controls">
    <div class="ctrl-left">
        <div>
            <div class="ctrl-title">📶 Quick Print Voucher</div>
        </div>
        <span class="ctrl-badge" id="countBadge">
            <?= count($vouchers) ?> voucher
        </span>
        <span style="font-size:12px;color:#94a3b8;">Status: Tersedia (unused)</span>
    </div>
    <div class="ctrl-right">
        <button class="btn-ctrl btn-print" onclick="window.print()">
            🖨️ Cetak
        </button>
        <a class="btn-ctrl btn-back" href="javascript:history.back()">
            ← Kembali
        </a>
    </div>
</div>

<!-- Filter (layar saja) -->
<?php if (!empty($allProfiles)): ?>
<div class="filter-row">
    <label style="font-size:13px;color:#475569;align-self:center;">Filter Profile:</label>
    <select id="filterProfile" onchange="filterCards()">
        <option value="">Semua Profile</option>
        <?php foreach ($allProfiles as $pf): ?>
            <option value="<?= htmlspecialchars($pf) ?>"><?= htmlspecialchars(profileLabel($pf)) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="number" id="limitInput" min="1" max="<?= count($vouchers) ?>"
           placeholder="Maks jumlah..." style="width:160px"
           onchange="filterCards()">
</div>
<?php endif; ?>

<!-- Grid Voucher -->
<?php if (empty($vouchers)): ?>
    <div class="empty-print">
        <h3>Tidak ada voucher tersedia</h3>
        <p>Semua voucher sudah terpakai, atau belum ada voucher yang digenerate.</p>
        <a href="index.php?page=generate" style="color:#2563eb;font-size:14px;">→ Generate Voucher</a>
    </div>
<?php else: ?>
    <div class="voucher-grid" id="voucherGrid">
        <?php foreach ($vouchers as $v): ?>
            <div class="voucher-card" data-profile="<?= htmlspecialchars($v['profile']) ?>">
                <div class="vc-header">
                    <div class="vc-header-icon">📶</div>
                    <div>
                        <div class="vc-brand">VoucherNet</div>
                        <div class="vc-profile-label">Paket <?= htmlspecialchars(profileLabel($v['profile'])) ?></div>
                    </div>
                </div>
                <div class="vc-body">
                    <div class="vc-row">
                        <span class="vc-label">Username</span>
                        <span class="vc-value"><?= htmlspecialchars($v['username']) ?></span>
                    </div>
                    <div class="vc-row">
                        <span class="vc-label">Password</span>
                        <span class="vc-value password"><?= htmlspecialchars($v['password']) ?></span>
                    </div>
                </div>
                <div class="vc-footer">
                    <span class="vc-date"><?= date('d/m/Y', strtotime($v['created_at'])) ?></span>
                    <span class="vc-validity"><?= htmlspecialchars(profileLabel($v['profile'])) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function filterCards() {
    const profile = document.getElementById('filterProfile')?.value ?? '';
    const limit   = parseInt(document.getElementById('limitInput')?.value) || 999999;
    const cards   = document.querySelectorAll('.voucher-card');
    let shown = 0;
    cards.forEach(card => {
        const matchProfile = profile === '' || card.dataset.profile === profile;
        const withinLimit  = shown < limit;
        if (matchProfile && withinLimit) {
            card.style.display = '';
            shown++;
        } else {
            card.style.display = 'none';
        }
    });
    const badge = document.getElementById('countBadge');
    if (badge) badge.textContent = shown + ' voucher';
}
</script>

</body>
</html>
