<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>

<main class="dashboard">
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-modern">
                        <a href="<?= BASE_URL_ADMIN ?>&action=/" class="breadcrumb-link">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <a href="<?= BASE_URL_ADMIN ?>&action=guides/available-tours" class="breadcrumb-link">
                            <i class="fas fa-route"></i>
                            <span>Tour Khả Dụng</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current">Chi tiết Booking</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-calendar-check title-icon"></i>
                            <?= htmlspecialchars($tour['name'] ?? 'Tour') ?>
                        </h1>
                        <p class="page-subtitle">Chọn booking để nhận phụ trách</p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=guides/available-tours" class="btn btn-modern btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Quay lại
                    </a>
                </div>
            </div>
        </header>

        <!-- Tour Info Card -->
        <section class="mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Thông tin Tour
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Tên tour:</strong> <?= htmlspecialchars($tour['name'] ?? 'N/A') ?></p>
                            <p><strong>Giá cơ bản:</strong> <?= number_format($tour['base_price'] ?? 0, 0, ',', '.') ?> ₫</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tổng booking:</strong> <span class="badge bg-primary"><?= count($bookings) ?></span></p>
                            <p><strong>Booking chưa có HDV:</strong> <span class="badge bg-warning"><?= count($bookings) ?></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bookings List -->
        <section class="tours-section">
            <div class="tours-header">
                <div class="tours-info">
                    <div class="select-all-wrapper">
                        <i class="fas fa-list"></i>
                        <label class="select-all-label">
                            Danh sách Booking
                        </label>
                    </div>
                    <div class="tours-count">
                        <span class="count-info">
                            <?= count($bookings) ?> booking
                        </span>
                    </div>
                </div>
            </div>

            <div class="tours-container">
                <?php if (!empty($bookings)): ?>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Mã Booking</th>
                                    <th>Khách hàng</th>
                                    <th>Ngày đặt</th>
                                    <th>Số khách</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><strong>#<?= $booking['id'] ?></strong></td>
                                        <td>
                                            <div class="customer-info">
                                                <i class="fas fa-user-circle me-2 text-primary"></i>
                                                <?= htmlspecialchars($booking['customer_name'] ?? 'N/A') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar me-2 text-muted"></i>
                                            <?= date('d/m/Y', strtotime($booking['booking_date'])) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= $booking['total_customers'] ?? 1 ?> người
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                <?= number_format($booking['final_price'] ?? $booking['total_price'] ?? 0, 0, ',', '.') ?> ₫
                                            </strong>
                                        </td>
                                        <td>
                                            <?php
                                            $statusText = 'Chờ Xác Nhận';
                                            $statusClass = 'warning';
                                            $statusIcon = 'clock';

                                            if ($booking['status'] === 'da_coc') {
                                                $statusText = 'Đã Cọc';
                                                $statusClass = 'info';
                                                $statusIcon = 'money-bill-wave';
                                            }
                                            ?>
                                            <span class="badge bg-<?= $statusClass ?>">
                                                <i class="fas fa-<?= $statusIcon ?> me-1"></i>
                                                <?= $statusText ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button"
                                                class="btn btn-sm btn-primary accept-booking-btn"
                                                data-booking-id="<?= $booking['id'] ?>"
                                                data-tour-id="<?= $tour['id'] ?>">
                                                <i class="fas fa-hand-paper me-1"></i>
                                                Nhận Booking
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Không có booking nào cần phân công HDV
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle accept booking
        document.querySelectorAll('.accept-booking-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const bookingId = this.dataset.bookingId;
                const tourId = this.dataset.tourId;

                if (confirm('Bạn có chắc muốn nhận booking này?')) {
                    // Send AJAX request
                    fetch('<?= BASE_URL_ADMIN ?>&action=guides/accept-booking', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `booking_id=${bookingId}&tour_id=${tourId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Nhận booking thành công!');
                                window.location.reload();
                            } else {
                                alert('Lỗi: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Có lỗi xảy ra!');
                        });
                }
            });
        });
    });
</script>

<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>