<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách đoàn - Booking #<?= $booking['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Screen styles */
        body {
            font-family: 'Times New Roman', serif;
            padding: 20px;
        }

        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #000;
            padding-bottom: 20px;
        }

        .print-header h1 {
            font-size: 24pt;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }

        .company-info {
            font-size: 11pt;
            margin-bottom: 10px;
        }

        .tour-info {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
        }

        .tour-info table {
            width: 100%;
        }

        .tour-info td {
            padding: 5px;
            font-size: 12pt;
        }

        .tour-info td:first-child {
            font-weight: bold;
            width: 150px;
        }

        .customer-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .customer-table th,
        .customer-table td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 11pt;
            text-align: left;
        }

        .customer-table th {
            background: #e9ecef;
            font-weight: bold;
            text-align: center;
        }

        .customer-table td.center {
            text-align: center;
        }

        .checkbox-cell {
            width: 30px;
            text-align: center;
        }

        .checkbox {
            width: 18px;
            height: 18px;
            border: 2px solid #000;
            display: inline-block;
        }

        .summary {
            margin: 20px 0;
            font-size: 12pt;
            font-weight: bold;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .no-print {
            margin: 20px 0;
        }

        /* Print styles */
        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .tour-info {
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .customer-table th {
                background: #e9ecef !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Prevent page breaks inside table rows */
            .customer-table tr {
                page-break-inside: avoid;
            }

            /* Page settings */
            @page {
                size: A4;
                margin: 15mm;
            }
        }
    </style>
</head>

<body>
    <!-- Print/Close buttons -->
    <div class="no-print text-center mb-3">
        <button onclick="window.print()" class="btn btn-primary btn-lg">
            <i class="fas fa-print"></i> In danh sách
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg ms-2">
            <i class="fas fa-times"></i> Đóng
        </button>
    </div>

    <!-- Header -->
    <div class="print-header">
        <div class="company-info">
            <strong>CÔNG TY DU LỊCH ABC</strong><br>
            Địa chỉ: 123 Đường ABC, Quận XYZ, TP. Hà Nội<br>
            Điện thoại: 024.1234.5678 | Email: info@dulich.com
        </div>
        <h1>Danh sách đoàn</h1>
    </div>

    <!-- Tour Information -->
    <div class="tour-info">
        <table>
            <tr>
                <td>Tên tour:</td>
                <td><strong><?= htmlspecialchars($tour['name'] ?? 'N/A') ?></strong></td>
            </tr>
            <tr>
                <td>Mã booking:</td>
                <td><strong>#<?= htmlspecialchars($booking['id']) ?></strong></td>
            </tr>
            <tr>
                <td>Ngày khởi hành:</td>
                <td><strong><?= htmlspecialchars($booking['departure_date'] ?? 'N/A') ?></strong></td>
            </tr>
            <tr>
                <td>Số lượng khách:</td>
                <td><strong><?= $stats['total'] ?> khách</strong> (<?= $stats['adults'] ?> NL, <?= $stats['children'] ?> TE, <?= $stats['infants'] ?> SN)</td>
            </tr>
        </table>
    </div>

    <!-- Customer List Table -->
    <table class="customer-table">
        <thead>
            <tr>
                <th style="width: 40px;">STT</th>
                <th style="width: 180px;">Họ và tên</th>
                <th style="width: 60px;">GT</th>
                <th style="width: 100px;">Ngày sinh</th>
                <th style="width: 100px;">CMND/Passport</th>
                <th style="width: 60px;">Booking</th>
                <th style="width: 80px;">Loại khách</th>
                <th style="width: 80px;">Loại phòng</th>
                <th>Yêu cầu đặc biệt</th>
                <th class="checkbox-cell">☐</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="10" class="center">Chưa có khách nào</td>
                </tr>
            <?php else: ?>
                <?php foreach ($customers as $index => $customer): ?>
                    <tr>
                        <td class="center"><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($customer['full_name'] ?? '') ?></td>
                        <td class="center">
                            <?php
                            $gender = $customer['gender'] ?? '';
                            echo $gender === 'male' ? 'Nam' : ($gender === 'female' ? 'Nữ' : '');
                            ?>
                        </td>
                        <td class="center">
                            <?php
                            if (!empty($customer['birth_date'])) {
                                echo date('d/m/Y', strtotime($customer['birth_date']));
                            }
                            ?>
                        </td>
                        <td class="center"><?= htmlspecialchars($customer['id_card'] ?? '') ?></td>
                        <td class="center">#<?= htmlspecialchars($customer['booking_code'] ?? '') ?></td>
                        <td class="center">
                            <?php
                            $type = $customer['passenger_type'] ?? 'adult';
                            $typeLabels = [
                                'adult' => 'NL',
                                'child' => 'TE',
                                'infant' => 'SN'
                            ];
                            echo $typeLabels[$type] ?? 'NL';

                            if (!empty($customer['is_foc'])) {
                                echo ' (FOC)';
                            }
                            ?>
                        </td>
                        <td class="center">
                            <?php
                            $roomType = $customer['room_type'] ?? '';
                            $roomLabels = [
                                'single' => 'Đơn',
                                'double' => 'Đôi',
                                'twin' => 'Twin',
                                'triple' => 'Ba'
                            ];
                            echo $roomLabels[$roomType] ?? '';
                            ?>
                        </td>
                        <td><?= htmlspecialchars($customer['special_request'] ?? '') ?></td>
                        <td class="checkbox-cell"><span class="checkbox"></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Summary -->
    <div class="summary">
        Tổng cộng: <?= $stats['total'] ?> khách
        (<?= $stats['adults'] ?> Người lớn, <?= $stats['children'] ?> Trẻ em, <?= $stats['infants'] ?> Sơ sinh)
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div><strong>Hướng dẫn viên</strong></div>
            <div class="signature-line">(Ký và ghi rõ họ tên)</div>
        </div>
        <div class="signature-box">
            <div><strong>Ngày in: <?= date('d/m/Y') ?></strong></div>
            <div class="signature-line">Trưởng đoàn</div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>

</html>