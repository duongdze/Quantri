<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

$bookingStats = $data['bookingStats'] ?? [];
$bookings = $data['bookings'] ?? [];
$topTours = $data['topTours'] ?? [];
$filters = $data['filters'] ?? [];
$tours = $data['tours'] ?? [];
?>

<main class="dashboard">
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-modern">
                        <a href="<?= BASE_URL_ADMIN ?>&action=/" class="breadcrumb-link">
                            <i class="fas fa-home"></i> <span>Dashboard</span>
                        </a>
                        <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
                        <a href="<?= BASE_URL_ADMIN ?>&action=reports" class="breadcrumb-link">
                            <span>Báo Cáo</span>
                        </a>
                        <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
                        <span class="breadcrumb-current">Booking</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-calendar-check title-icon"></i>
                            Báo Cáo Booking
                        </h1>
                        <p class="page-subtitle">Thống kê số lượng và trạng thái booking</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Filter Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <form action="" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="mode" value="admin">
                    <input type="hidden" name="action" value="reports/bookings">
                    
                    <!-- Date From -->
                    <div class="col-12 col-md-2">
                        <label class="form-label small text-muted">Từ ngày</label>
                        <input type="date" name="date_from" class="form-control" value="<?= $filters['date_from'] ?? date('Y-m-01') ?>">
                    </div>

                    <!-- Date To -->
                    <div class="col-12 col-md-2">
                        <label class="form-label small text-muted">Đến ngày</label>
                        <input type="date" name="date_to" class="form-control" value="<?= $filters['date_to'] ?? date('Y-m-d') ?>">
                    </div>

                    <!-- Status Filter -->
                    <div class="col-12 col-md-2">
                        <label class="form-label small text-muted">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="cho_xac_nhan" <?= ($filters['status'] ?? '') == 'cho_xac_nhan' ? 'selected' : '' ?>>Chờ xác nhận</option>
                            <option value="da_coc" <?= ($filters['status'] ?? '') == 'da_coc' ? 'selected' : '' ?>>Đã cọc</option>
                            <option value="hoan_tat" <?= ($filters['status'] ?? '') == 'hoan_tat' ? 'selected' : '' ?>>Hoàn tất</option>
                            <option value="da_huy" <?= ($filters['status'] ?? '') == 'da_huy' ? 'selected' : '' ?>>Đã hủy</option>
                        </select>
                    </div>

                    <!-- Tour Filter -->
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted">Tour</label>
                        <select name="tour_id" class="form-select">
                             <option value="">Tất cả Tour</option>
                            <?php if (!empty($tours)): ?>
                                <?php foreach ($tours as $tour): ?>
                                    <option value="<?= $tour['id'] ?>" <?= ($filters['tour_id'] ?? '') == $tour['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tour['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                     <!-- Buttons -->
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Lọc</button>
                        <a href="<?= BASE_URL_ADMIN ?>&action=reports/bookings" class="btn btn-outline-secondary"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>
        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Tổng Booking</h6>
                        <h3 class="fw-bold text-primary mb-0"><?= number_format($bookingStats['total_bookings'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Hoàn Tất</h6>
                        <h3 class="fw-bold text-success mb-1"><?= number_format($bookingStats['successful_bookings'] ?? 0) ?></h3>
                        <small class="text-muted">Tỷ lệ: <?= number_format($bookingStats['success_rate'] ?? 0, 1) ?>%</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Đã Hủy</h6>
                        <h3 class="fw-bold text-danger mb-1"><?= number_format($bookingStats['cancelled_bookings'] ?? 0) ?></h3>
                        <small class="text-muted">Tỷ lệ: <?= number_format($bookingStats['cancellation_rate'] ?? 0, 1) ?>%</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Tổng Khách Hàng</h6>
                        <h3 class="fw-bold text-info mb-0"><?= number_format($bookingStats['total_customers'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0 fw-bold">Booking Theo Tháng</h5>
                    </div>
                    <div class="card-body">
                         <div style="height: 300px;">
                            <canvas id="monthlyBookingsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0 fw-bold">Nguồn Booking</h5>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="sourceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Tours -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-bold">Top Tours Được Đặt Nhiều Nhất</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3 border-0">#</th>
                            <th class="border-0">Tên Tour</th>
                            <th class="border-0 text-end">Booking</th>
                            <th class="border-0 text-end">Tổng Khách</th>
                            <th class="border-0 text-end pe-3">Doanh Thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($topTours)): ?>
                            <?php foreach ($topTours as $index => $tour): ?>
                                <tr>
                                    <td class="ps-3 fw-bold"><?= $index + 1 ?></td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($tour['tour_name']) ?>">
                                            <?= htmlspecialchars($tour['tour_name']) ?>
                                        </div>
                                    </td>
                                    <td class="text-end"><?= number_format($tour['booking_count']) ?></td>
                                    <td class="text-end"><?= number_format($tour['total_passengers'] ?? 0) ?></td>
                                    <td class="text-end pe-3 fw-bold text-success"><?= number_format($tour['total_revenue'] ?? 0, 0, ',', '.') ?> ₫</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">Chưa có dữ liệu</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($data['monthlyBookings'])): ?>
    new Chart(document.getElementById('monthlyBookingsChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($data['monthlyLabels'] ?? []) ?>,
            datasets: [{
                label: 'Tổng Booking',
                data: <?= json_encode($data['monthlyBookings'] ?? []) ?>,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Hoàn Tất',
                data: <?= json_encode($data['monthlySuccessfulBookings'] ?? []) ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true } }
        }
    });
    <?php endif; ?>

    <?php if (isset($data['sourceNames'])): ?>
    new Chart(document.getElementById('sourceChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($data['sourceNames'] ?? []) ?>,
            datasets: [{
                data: <?= json_encode($data['sourceCounts'] ?? []) ?>,
                backgroundColor: ['#667eea', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'right' } }
        }
    });
    <?php endif; ?>
});
</script>
<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>
