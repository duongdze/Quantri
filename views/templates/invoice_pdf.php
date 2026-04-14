<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn <?= 'BK' . str_pad($data['id'], 6, '0', STR_PAD_LEFT) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; line-height: 1.5; }

        .invoice-wrapper { padding: 30px; }

        /* ── Header ── */
        .invoice-header { display: table; width: 100%; margin-bottom: 25px; border-bottom: 3px solid #1e6fff; padding-bottom: 20px; }
        .invoice-header .logo-col  { display: table-cell; width: 60%; vertical-align: top; }
        .invoice-header .info-col  { display: table-cell; width: 40%; text-align: right; vertical-align: top; }

        .company-name { font-size: 22px; font-weight: bold; color: #1e6fff; margin-bottom: 5px; }
        .company-detail { font-size: 10px; color: #666; }

        .invoice-title { font-size: 28px; font-weight: bold; color: #1e6fff; }
        .invoice-meta { font-size: 10px; color: #666; margin-top: 4px; }
        .invoice-code { display: inline-block; background: #1e6fff; color: #fff; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 13px; margin-top: 6px; }

        /* ── Info boxes ── */
        .info-section { display: table; width: 100%; margin-bottom: 20px; }
        .info-box { display: table-cell; width: 50%; vertical-align: top; padding: 12px 15px; }
        .info-box:first-child { background: #f0f5ff; border-radius: 6px 0 0 6px; }
        .info-box:last-child  { background: #f8f8f8; border-radius: 0 6px 6px 0; }
        .info-box h3 { font-size: 11px; text-transform: uppercase; color: #1e6fff; margin-bottom: 8px; letter-spacing: 0.5px; }
        .info-box p { margin: 3px 0; font-size: 11px; }
        .info-box .label { color: #888; display: inline-block; width: 100px; }

        /* ── Tour Info ── */
        .tour-info { background: linear-gradient(135deg, #1e6fff 0%, #3b82f6 100%); color: #fff; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .tour-info h3 { font-size: 15px; margin-bottom: 5px; }
        .tour-info-row { display: table; width: 100%; }
        .tour-info-cell { display: table-cell; width: 33.33%; font-size: 10px; opacity: .9; padding-top: 5px; }

        /* ── Table ── */
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #1e6fff; color: #fff; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .3px; }
        table.items th:first-child { border-radius: 6px 0 0 0; }
        table.items th:last-child  { border-radius: 0 6px 0 0; }
        table.items td { padding: 10px 12px; border-bottom: 1px solid #eee; font-size: 11px; }
        table.items tr:nth-child(even) td { background: #fafafa; }
        table.items .text-right { text-align: right; }
        table.items .text-center { text-align: center; }

        /* ── Total ── */
        .totals { display: table; width: 100%; margin-bottom: 25px; }
        .totals-left  { display: table-cell; width: 55%; vertical-align: top; }
        .totals-right { display: table-cell; width: 45%; vertical-align: top; }
        .totals-box { background: #f8f8f8; border-radius: 8px; padding: 15px; }
        .totals-row { display: table; width: 100%; margin-bottom: 6px; }
        .totals-label { display: table-cell; width: 60%; font-size: 11px; color: #666; }
        .totals-value { display: table-cell; width: 40%; text-align: right; font-size: 11px; font-weight: bold; }
        .totals-grand { border-top: 2px solid #1e6fff; padding-top: 8px; margin-top: 8px; }
        .totals-grand .totals-label { font-size: 14px; font-weight: bold; color: #1e6fff; }
        .totals-grand .totals-value { font-size: 16px; color: #1e6fff; }

        /* ── Status badge ── */
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 50px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .status-pending   { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-deposited { background: #cce5ff; color: #004085; }
        .status-paid      { background: #d4edda; color: #155724; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        /* ── Footer ── */
        .invoice-footer { border-top: 2px solid #eee; padding-top: 15px; margin-top: 25px; }
        .footer-note { font-size: 10px; color: #888; text-align: center; }
        .footer-note strong { color: #1e6fff; }
        .thank-you { text-align: center; font-size: 14px; color: #1e6fff; font-weight: bold; margin-bottom: 10px; }

        /* ── Payment info ── */
        .payment-note { background: #fffde7; border: 1px solid #fff9c4; border-radius: 6px; padding: 10px 15px; margin-bottom: 20px; font-size: 10px; }
        .payment-note strong { color: #f57f17; }
    </style>
</head>
<body>
<div class="invoice-wrapper">
    <?php
    $bookingCode = 'BK' . str_pad($data['id'], 6, '0', STR_PAD_LEFT);
    $statusMap = [
        'pending'   => ['Chờ xử lý',    'status-pending'],
        'confirmed' => ['Đã xác nhận',   'status-confirmed'],
        'deposited' => ['Đã đặt cọc',    'status-deposited'],
        'paid'      => ['Đã thanh toán',  'status-paid'],
        'completed' => ['Hoàn thành',     'status-completed'],
        'cancelled' => ['Đã hủy',        'status-cancelled'],
    ];
    $statusInfo = $statusMap[$data['status'] ?? 'pending'] ?? ['N/A', ''];
    ?>

    <!-- Header -->
    <div class="invoice-header">
        <div class="logo-col">
            <div class="company-name">🌍 VietTour</div>
            <div class="company-detail">Công ty TNHH Du lịch VietTour</div>
            <div class="company-detail">Địa chỉ: 123 Nguyễn Văn Linh, Quận 7, TP.HCM</div>
            <div class="company-detail">Hotline: 1900 xxxx | Email: info@viettour.vn</div>
        </div>
        <div class="info-col">
            <div class="invoice-title">HÓA ĐƠN</div>
            <div class="invoice-code"><?= $bookingCode ?></div>
            <div class="invoice-meta">Ngày xuất: <?= date('d/m/Y H:i') ?></div>
            <div class="invoice-meta">
                Trạng thái: <span class="status-badge <?= $statusInfo[1] ?>"><?= $statusInfo[0] ?></span>
            </div>
        </div>
    </div>

    <!-- Customer & Booking Info -->
    <div class="info-section">
        <div class="info-box">
            <h3>Thông tin khách hàng</h3>
            <p><span class="label">Họ tên:</span> <strong><?= htmlspecialchars($data['customer_name'] ?? $data['full_name'] ?? 'N/A') ?></strong></p>
            <p><span class="label">Email:</span> <?= htmlspecialchars($data['email'] ?? 'N/A') ?></p>
            <p><span class="label">Điện thoại:</span> <?= htmlspecialchars($data['phone'] ?? 'N/A') ?></p>
        </div>
        <div class="info-box">
            <h3>Thông tin đặt tour</h3>
            <p><span class="label">Mã đơn:</span> <strong><?= $bookingCode ?></strong></p>
            <p><span class="label">Ngày đặt:</span> <?= date('d/m/Y H:i', strtotime($data['booking_date'] ?? $data['created_at'] ?? 'now')) ?></p>
            <p><span class="label">Ngày đi:</span> <strong><?= date('d/m/Y', strtotime($data['departure_date'] ?? 'N/A')) ?></strong></p>
        </div>
    </div>

    <!-- Tour Info -->
    <div class="tour-info">
        <h3>🗺️ <?= htmlspecialchars($data['tour_name'] ?? $data['name'] ?? 'Tour') ?></h3>
        <div class="tour-info-row">
            <div class="tour-info-cell">📅 Ngày khởi hành: <?= date('d/m/Y', strtotime($data['departure_date'] ?? 'now')) ?></div>
            <div class="tour-info-cell">👥 Số khách: <?= ($data['adults'] ?? 1) + ($data['children'] ?? 0) + ($data['infants'] ?? 0) ?> người</div>
            <div class="tour-info-cell">⏱️ Thời gian: <?= $data['duration_days'] ?? 'N/A' ?> ngày</div>
        </div>
    </div>

    <!-- Passenger List -->
    <?php if (!empty($passengerList)): ?>
    <table class="items">
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:35%">Họ tên hành khách</th>
                <th style="width:15%" class="text-center">Loại</th>
                <th style="width:20%">Điện thoại</th>
                <th style="width:25%">Ghi chú</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($passengerList as $i => $p): ?>
            <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td><strong><?= htmlspecialchars($p['full_name'] ?? '') ?></strong></td>
                <td class="text-center">
                    <?php
                    $typeMap = ['adult' => 'Người lớn', 'child' => 'Trẻ em', 'infant' => 'Em bé'];
                    echo $typeMap[$p['passenger_type'] ?? 'adult'] ?? 'N/A';
                    ?>
                </td>
                <td><?= htmlspecialchars($p['phone'] ?? '-') ?></td>
                <td><?= htmlspecialchars($p['note'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Totals -->
    <div class="totals">
        <div class="totals-left">
            <div class="payment-note">
                <strong>⚠️ Lưu ý thanh toán:</strong><br>
                Vui lòng thanh toán trước ngày khởi hành ít nhất 3 ngày. Mọi thắc mắc xin liên hệ hotline 1900 xxxx.
            </div>
        </div>
        <div class="totals-right">
            <div class="totals-box">
                <?php
                $adults   = (int)($data['adults'] ?? 1);
                $children = (int)($data['children'] ?? 0);
                $priceAdult = (float)($data['price_adult'] ?? $data['total_price'] ?? 0);
                $priceChild = (float)($data['price_child'] ?? $priceAdult);
                $totalPrice = (float)($data['total_price'] ?? $data['final_price'] ?? 0);
                ?>
                <div class="totals-row">
                    <div class="totals-label">Người lớn (×<?= $adults ?>)</div>
                    <div class="totals-value"><?= number_format($priceAdult * $adults) ?> ₫</div>
                </div>
                <?php if ($children > 0): ?>
                <div class="totals-row">
                    <div class="totals-label">Trẻ em (×<?= $children ?>)</div>
                    <div class="totals-value"><?= number_format($priceChild * $children) ?> ₫</div>
                </div>
                <?php endif; ?>
                <div class="totals-row totals-grand">
                    <div class="totals-label">TỔNG CỘNG</div>
                    <div class="totals-value"><?= number_format($totalPrice) ?> ₫</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="invoice-footer">
        <div class="thank-you">✨ Cảm ơn quý khách đã tin tưởng VietTour! ✨</div>
        <div class="footer-note">
            Đây là hóa đơn điện tử được tạo tự động bởi hệ thống <strong>VietTour</strong>.<br>
            Mọi thắc mắc xin liên hệ: <strong>info@viettour.vn</strong> | Hotline: <strong>1900 xxxx</strong><br>
            © <?= date('Y') ?> VietTour. All rights reserved.
        </div>
    </div>
</div>
</body>
</html>
