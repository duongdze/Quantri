<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

$conversionData = $data['conversionData'] ?? [];
$topTours = $data['topTours'] ?? [];
$sourceConversion = $data['sourceConversion'] ?? [];
$filters = $data['filters'] ?? [];
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
                        <span class="breadcrumb-current">Chuyển Đổi</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-chart-pie title-icon"></i>
                            Báo Cáo Tỷ Lệ Chuyển Đổi
                        </h1>
                        <p class="page-subtitle">Phân tích hiệu quả kinh doanh và tỷ lệ chốt đơn</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- KPI Cards -->
        <div class="row g-3 mb-4">
            <!-- Total Inquiries -->
            <div class="col-12 col-sm-6 col-xl-6">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                           <div class="bg-info-subtle text-info rounded p-2 me-3">
                                <i class="fas fa-eye fa-lg"></i>
                            </div>
                            <h6 class="text-muted mb-0">Tổng Inquiry</h6>
                        </div>
                        <h3 class="fw-bold mb-0"><?= number_format($conversionData['total_inquiries'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>

            <!-- Overall Rate -->
            <div class="col-12 col-sm-6 col-xl-6">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                    <div class="card-body">
                         <div class="d-flex align-items-center mb-2">
                           <div class="bg-primary-subtle text-primary rounded p-2 me-3">
                                <i class="fas fa-percentage fa-lg"></i>
                            </div>
                            <h6 class="text-muted mb-0">Tỷ Lệ Tổng Thể</h6>
                        </div>
                        <h3 class="fw-bold mb-1"><?= number_format($conversionData['conversion_rates']['overall'] ?? 0, 1) ?>%</h3>
                        <small class="text-muted">Liên hệ → Thanh toán</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Funnel Chart -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-bold">Phễu Chuyển Đổi</h5>
            </div>
            <div class="card-body">
                <div style="height: 350px;">
                    <canvas id="funnelChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Converting Tours -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-bold">Top Tours Theo Tỷ Lệ Chuyển Đổi</h5>
            </div>
             <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3 border-0">#</th>
                            <th class="border-0">Tên Tour</th>
                            <th class="border-0 text-center">Liên hệ</th>
                            <th class="border-0 text-center">Đặt tour</th>
                            <th class="border-0 text-center">Thanh toán</th>
                            <th class="border-0 text-end pe-3">Tỷ Lệ Chuyển Đổi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($topTours)): ?>
                            <?php foreach (array_slice($topTours, 0, 10) as $index => $tour): ?>
                                <tr>
                                    <td class="ps-3 fw-bold"><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($tour['tour_name']) ?></td>
                                    <td class="text-center"><?= number_format($tour['inquiries'] ?? 0) ?></td>
                                    <td class="text-center"><?= number_format($tour['bookings'] ?? 0) ?></td>
                                    <td class="text-center"><?= number_format($tour['payments'] ?? 0) ?></td>
                                    <td class="text-end pe-3">
                                        <?php $rate = $tour['conversion_rate'] ?? 0; ?>
                                        <span class="badge rounded-pill <?= $rate >= 50 ? 'bg-success' : ($rate >= 25 ? 'bg-warning' : 'bg-danger') ?>">
                                            <?= number_format($rate, 1) ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có dữ liệu</td></tr>
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
    new Chart(document.getElementById('funnelChart'), {
        type: 'bar',
        data: {
            labels: ['Liên hệ', 'Đặt tour', 'Thanh toán', 'Hoàn tất'],
            datasets: [{
                label: 'Số lượng',
                data: [
                    <?= $conversionData['total_inquiries'] ?? 0 ?>,
                    <?= $conversionData['total_bookings'] ?? 0 ?>,
                    <?= $conversionData['total_payments'] ?? 0 ?>,
                    <?= $conversionData['total_completed'] ?? 0 ?>
                ],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(102, 126, 234, 0.7)'
                ],
                borderColor: [
                    '#3b82f6',
                    '#f59e0b',
                    '#10b981',
                    '#667eea'
                ],
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true } }
        }
    });
});
</script>
<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>
