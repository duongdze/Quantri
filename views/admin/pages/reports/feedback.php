<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

$feedbackStats = $data['feedbackStats'] ?? [];
$feedbacks = $data['feedbacks'] ?? [];
$topRatedTours = $data['topRatedTours'] ?? [];
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
                        <span class="breadcrumb-current">Phản Hồi</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-comments title-icon"></i>
                            Báo Cáo Phản Hồi & Đánh Giá
                        </h1>
                        <p class="page-subtitle">Quản lý phản hồi và đánh giá từ khách hàng</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <!-- Avg Rating -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                           <div class="bg-warning-subtle text-warning rounded p-2 me-3">
                                <i class="fas fa-star fa-lg"></i>
                            </div>
                            <h6 class="text-muted mb-0">Đánh Giá TB</h6>
                        </div>
                        <h3 class="fw-bold mb-1"><?= number_format($feedbackStats['avg_rating'] ?? 0, 1) ?>/5.0</h3>
                        <?php if (isset($feedbackStats['rating_growth'])): ?>
                            <?php $isPositive = $feedbackStats['rating_growth'] >= 0; ?>
                            <small class="<?= $isPositive ? 'text-success' : 'text-danger' ?>">
                                <i class="fas fa-arrow-<?= $isPositive ? 'up' : 'down' ?>"></i>
                                <?= number_format(abs($feedbackStats['rating_growth']), 1) ?>% so với kỳ trước
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Total Feedback -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                    <div class="card-body">
                         <div class="d-flex align-items-center mb-2">
                           <div class="bg-info-subtle text-info rounded p-2 me-3">
                                <i class="fas fa-comments fa-lg"></i>
                            </div>
                            <h6 class="text-muted mb-0">Tổng Phản Hồi</h6>
                        </div>
                        <h3 class="fw-bold mb-1"><?= number_format($feedbackStats['total_feedbacks'] ?? 0) ?></h3>
                        <small class="text-muted">Tỷ lệ phản hồi: <?= number_format($feedbackStats['feedback_rate'] ?? 0, 1) ?>%</small>
                    </div>
                </div>
            </div>

            <!-- Positive Feedback -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                           <div class="bg-success-subtle text-success rounded p-2 me-3">
                                <i class="fas fa-smile fa-lg"></i>
                            </div>
                            <h6 class="text-muted mb-0">Tích Cực</h6>
                        </div>
                        <h3 class="fw-bold mb-1"><?= number_format($feedbackStats['positive_feedbacks'] ?? 0) ?></h3>
                        <small class="text-success"><?= number_format($feedbackStats['positive_rate'] ?? 0, 1) ?>%</small>
                    </div>
                </div>
            </div>

            <!-- Negative Feedback -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                           <div class="bg-danger-subtle text-danger rounded p-2 me-3">
                                <i class="fas fa-frown fa-lg"></i>
                            </div>
                            <h6 class="text-muted mb-0">Tiêu Cực</h6>
                        </div>
                        <h3 class="fw-bold mb-1"><?= number_format($feedbackStats['negative_feedbacks'] ?? 0) ?></h3>
                        <small class="text-danger"><?= number_format($feedbackStats['negative_rate'] ?? 0, 1) ?>%</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                     <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0 fw-bold">Phân Bổ Đánh Giá</h5>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="ratingDistChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0 fw-bold">Loại Phản Hồi</h5>
                    </div>
                    <div class="card-body">
                         <div style="height: 300px;">
                            <canvas id="feedbackTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Rated Tours -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-bold">Top Tours Được Đánh Giá Cao Nhất</h5>
            </div>
             <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3 border-0">#</th>
                            <th class="border-0">Tên Tour</th>
                            <th class="border-0 text-center">Số Đánh Giá</th>
                            <th class="border-0 text-end pe-3">Rating TB</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($topRatedTours)): ?>
                            <?php foreach ($topRatedTours as $index => $tour): ?>
                                <tr>
                                    <td class="ps-3 fw-bold"><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($tour['tour_name']) ?></td>
                                    <td class="text-center"><?= number_format($tour['feedback_count']) ?></td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex align-items-center justify-content-end text-warning">
                                            <?php
                                            $rating = $tour['avg_rating'] ?? 0;
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= floor($rating)) echo '<i class="fas fa-star"></i>';
                                                elseif ($i - 0.5 <= $rating) echo '<i class="fas fa-star-half-alt"></i>';
                                                else echo '<i class="far fa-star"></i>';
                                            }
                                            ?>
                                            <span class="ms-2 text-dark fw-bold"><?= number_format($rating, 1) ?></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">Chưa có dữ liệu</td></tr>
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
    <?php if (isset($data['ratingDistribution'])): ?>
    new Chart(document.getElementById('ratingDistChart'), {
        type: 'bar',
        data: {
            labels: ['5 Sao', '4 Sao', '3 Sao', '2 Sao', '1 Sao'],
            datasets: [{
                label: 'Số lượng',
                data: <?= json_encode($data['ratingDistribution'] ?? [0,0,0,0,0]) ?>,
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#991b1b'],
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
    <?php endif; ?>

    <?php if (isset($data['feedbackTypeLabels'])): ?>
    new Chart(document.getElementById('feedbackTypeChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($data['feedbackTypeLabels'] ?? []) ?>,
            datasets: [{
                data: <?= json_encode($data['feedbackTypeCounts'] ?? []) ?>,
                backgroundColor: ['#667eea', '#10b981', '#f59e0b'],
                borderWidth: 0
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
