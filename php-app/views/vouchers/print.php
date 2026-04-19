<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Voucher — VoucherNet</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        /* ====== BASE ====== */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }

        /* ====== PRINT CONTROLS (hanya tampil di layar) ====== */
        .print-controls {
            text-align: center;
            margin-bottom: 24px;
        }
        .print-controls button {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 10px 28px;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            margin: 0 6px;
        }
        .print-controls .btn-back {
            background: #6b7280;
        }

        /* ====== VOUCHER GRID ====== */
        .voucher-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            max-width: 900px;
            margin: 0 auto;
        }

        /* ====== VOUCHER CARD ====== */
        .voucher-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            border: 2px solid #e2e8f0;
            page-break-inside: avoid;
            position: relative;
        }

        .voucher-header {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
            padding: 14px 16px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .voucher-header-icon {
            font-size: 22px;
            color: #fff;
        }
        .voucher-header-text {
            color: #fff;
        }
        .voucher-brand {
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .voucher-profile-label {
            font-size: 11px;
            opacity: 0.85;
            margin-top: 1px;
        }

        .voucher-body {
            padding: 14px 16px;
        }

        .voucher-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .voucher-row:last-child {
            border-bottom: none;
        }
        .voucher-field-label {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .voucher-field-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
        }
        .voucher-field-value.password {
            color: #1e40af;
            font-size: 15px;
        }

        .voucher-footer {
            background: #f8fafc;
            padding: 8px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e2e8f0;
        }
        .voucher-footer-text {
            font-size: 9px;
            color: #94a3b8;
        }
        .voucher-validity {
            background: #dbeafe;
            color: #1e40af;
            font-size: 9px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 99px;
        }

        /* Cut marks */
        .voucher-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            pointer-events: none;
        }

        /* ====== PRINT STYLES ====== */
        @media print {
            body { background: #fff; padding: 0; }
            .print-controls { display: none; }
            .voucher-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
                max-width: 100%;
            }
            .voucher-card {
                border: 1.5px solid #d1d5db;
                box-shadow: none;
                border-radius: 10px;
            }
            @page { size: A4; margin: 15mm; }
        }

        /* ====== EMPTY STATE ====== */
        .empty-print {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        .empty-print h3 { margin-bottom: 8px; color: #475569; }
    </style>
</head>
<body>

<div class="print-controls">
    <button onclick="window.print()">🖨️ Cetak Voucher</button>
    <button class="btn-back" onclick="window.close()">← Kembali</button>
</div>

<?php if (empty($vouchers)): ?>
    <div class="empty-print">
        <h3>Tidak ada voucher untuk dicetak</h3>
        <p>Silakan generate voucher terlebih dahulu.</p>
    </div>
<?php else: ?>
    <div class="voucher-grid">
        <?php foreach ($vouchers as $v): ?>
            <div class="voucher-card">
                <div class="voucher-header">
                    <div class="voucher-header-icon">📶</div>
                    <div class="voucher-header-text">
                        <div class="voucher-brand">VoucherNet</div>
                        <div class="voucher-profile-label">
                            Paket <?= htmlspecialchars(profileLabel($v['profile'])) ?>
                        </div>
                    </div>
                </div>

                <div class="voucher-body">
                    <div class="voucher-row">
                        <span class="voucher-field-label">Username</span>
                        <span class="voucher-field-value"><?= htmlspecialchars($v['username']) ?></span>
                    </div>
                    <div class="voucher-row">
                        <span class="voucher-field-label">Password</span>
                        <span class="voucher-field-value password"><?= htmlspecialchars($v['password']) ?></span>
                    </div>
                </div>

                <div class="voucher-footer">
                    <span class="voucher-footer-text">
                        <?= date('d/m/Y', strtotime($v['created_at'] ?? date('Y-m-d'))) ?>
                    </span>
                    <span class="voucher-validity">
                        <?= htmlspecialchars(profileLabel($v['profile'])) ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</body>
</html>
