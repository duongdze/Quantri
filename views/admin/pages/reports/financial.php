<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

// Get data from controller
$financialData = $data['financialData'] ?? [];
$tourFinancials = $data['tourFinancials'] ?? [];
$filters = $data['filters'] ?? [];
$filterOptions = $data['filterOptions'] ?? [];
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
                        <span class="breadcrumb-current">Tài Chính</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-money-bill-wave title-icon"></i>
                            Báo Cáo Tài Chính
                        </h1>
                        <p class="page-subtitle">Phân tích doanh thu và lợi nhuận</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Filter Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <form action="" method="GET" class="row g-3 align-items-end">
                    <!-- Ensure mode=admin is preserved -->
                    <input type="hidden" name="mode" value="admin">
                    <!-- Maintain current action -->
                    <input type="hidden" name="action" value="reports/financial">
                    
                    <!-- Date From -->
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted">Từ ngày</label>
                        <input type="date" name="date_from" class="form-control" value="<?= $filters['date_from'] ?? date('Y-m-01') ?>">
                    </div>

                    <!-- Date To -->
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted">Đến ngày</label>
                        <input type="date" name="date_to" class="form-control" value="<?= $filters['date_to'] ?? date('Y-m-d') ?>">
                    </div>

                    <!-- Tour Filter -->
                     <div class="col-12 col-md-4">
                        <label class="form-label small text-muted">Tour</label>
                        <select name="tour_id" class="form-select">
                            <option value="">Tất cả Tour</option>
                            <?php if (!empty($filterOptions['tours'])): ?>
                                <?php foreach ($filterOptions['tours'] as $tour): ?>
                                    <option value="<?= $tour['id'] ?>" <?= ($filters['tour_id'] ?? '') == $tour['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tour['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Lọc</button>
                        <button type="button" onclick="resetFilters()" class="btn btn-outline-secondary"><i class="fas fa-undo"></i></button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <!-- Total Revenue -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-success-subtle text-success rounded p-2 me-3">
                                <i class="fas fa-money-bill-wave fa-lg"></i>
                            </div>
                            <h6 class="text-muted mb-0">Tổng Doanh Thu</h6>
                        </div>
                        <h3 class="fw-bold mb-1"><?= number_format($financialData['total_revenue'] ?? 0, 0, ',', '.') ?> ₫</h3>
                        <small class="text-muted">Từ <?= $financialData['total_bookings'] ?? 0 ?> booking</small>
                    </div>
                </div>
            </div>

            <!-- Total Expense -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-danger-subtle text-danger rounded p-2 me-3">
                                <i class="fas fa-receipt fa-lg"></i>
                            </div>
                            <h6 class="text-muted mb-0">Tổng Chi Phí</h6>
                        </div>
                        <h3 class="fw-bold mb-1"><?= number_format($financialData['total_expense'] ?? 0, 0, ',', '.') ?> ₫</h3>
                        <small class="text-muted"><?= $financialData['cost_count'] ?? 0 ?> khoản chi</small>
                    </div>
                </div>
            </div>

            <!-- Profit -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-primary-subtle text-primary rounded p-2 me-3">
                                <i class="fas fa-chart-line fa-lg"></i>
                            </div>
                            <h6 class="text-muted mb-0">Lợi Nhuận</h6>
                        </div>
                        <h3 class="fw-bold mb-1"><?= number_format($financialData['profit'] ?? 0, 0, ',', '.') ?> ₫</h3>
                        <small class="text-muted">Tỷ suất LN: <?= number_format($financialData['profit_margin'] ?? 0, 1) ?>%</small>
                    </div>
                </div>
            </div>

            <!-- Avg Value -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-info-subtle text-info rounded p-2 me-3">
                                <i class="fas fa-calculator fa-lg"></i>
                            </div>
                            <h6 class="text-muted mb-0">TB/Booking</h6>
                        </div>
                        <h3 class="fw-bold mb-1"><?= number_format($financialData['avg_booking_value'] ?? 0, 0, ',', '.') ?> ₫</h3>
                        <small class="text-muted">Chi phí TB: <?= number_format($financialData['avg_cost'] ?? 0, 0, ',', '.') ?> ₫</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <!-- Monthly Chart -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0 fw-bold">Doanh Thu & Chi Phí Theo Tháng</h5>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="monthlyFinancialChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profit Pie Chart -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0 fw-bold">Lợi Nhuận Top 5 Tours</h5>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="tourProfitChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Row -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">Chi Tiết Tài Chính Theo Tour</h5>
                <span class="badge bg-light text-dark border"><?= count($tourFinancials) ?> tours</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 ps-3">#</th>
                                <th class="border-0">Tên Tour</th>
                                <th class="border-0">Danh Mục</th>
                                <th class="border-0 text-end">Booking</th>
                                <th class="border-0 text-end">Doanh Thu</th>
                                <th class="border-0 text-end">Chi Phí</th>
                                <th class="border-0 text-end">Lợi Nhuận</th>
                                <th class="border-0 text-end pe-3">Tỷ suất LN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tourFinancials)): ?>
                                <?php foreach ($tourFinancials as $index => $tour): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold"><?= $index + 1 ?></td>
                                        <td>
                                            <div class="fw-medium text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($tour['tour_name']) ?>">
                                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                                <?= htmlspecialchars($tour['tour_name']) ?>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($tour['category_name'] ?? 'N/A') ?></span></td>
                                        <td class="text-end"><?= number_format($tour['booking_count'] ?? 0) ?></td>
                                        <td class="text-end fw-bold text-success"><?= number_format($tour['revenue'] ?? 0, 0, ',', '.') ?> ₫</td>
                                        <td class="text-end text-danger"><?= number_format($tour['expense'] ?? 0, 0, ',', '.') ?> ₫</td>
                                        <td class="text-end fw-bold <?= ($tour['profit'] ?? 0) >= 0 ? 'text-primary' : 'text-danger' ?>">
                                            <?= number_format($tour['profit'] ?? 0, 0, ',', '.') ?> ₫
                                        </td>
                                        <td class="text-end pe-3">
                                            <?php $margin = $tour['profit_margin'] ?? 0; ?>
                                            <span class="badge rounded-pill <?= $margin >= 20 ? 'bg-success' : ($margin >= 10 ? 'bg-warning' : 'bg-danger') ?>">
                                                <?= number_format($margin, 1) ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                                            <p>Chưa có dữ liệu phù hợp với bộ lọc.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($tourFinancials)): ?>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end ps-3">TỔNG CỘNG</td>
                                    <td class="text-end"><?= number_format(array_sum(array_column($tourFinancials, 'booking_count'))) ?></td>
                                    <td class="text-end text-success"><?= number_format(array_sum(array_column($tourFinancials, 'revenue')), 0, ',', '.') ?> ₫</td>
                                    <td class="text-end text-danger"><?= number_format(array_sum(array_column($tourFinancials, 'expense')), 0, ',', '.') ?> ₫</td>
                                    <td class="text-end text-primary"><?= number_format(array_sum(array_column($tourFinancials, 'profit')), 0, ',', '.') ?> ₫</td>
                                    <td class="text-end pe-3">
                                        <?php
                                        $totalRevenue = array_sum(array_column($tourFinancials, 'revenue'));
                                        $totalProfit = array_sum(array_column($tourFinancials, 'profit'));
                                        $avgMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;
                                        echo number_format($avgMargin, 1) . '%';
                                        ?>
                                    </td>
                                </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Re-use data from PHP
        const monthlyLabels = <?= json_encode($data['monthlyLabels'] ?? []) ?>;
        const monthlyRevenue = <?= json_encode($data['monthlyRevenue'] ?? []) ?>;
        const monthlyExpense = <?= json_encode($data['monthlyExpense'] ?? []) ?>;
        const monthlyProfit = <?= json_encode($data['monthlyProfit'] ?? []) ?>;

        const tourNames = <?= json_encode($data['tourNames'] ?? []) ?>;
        const tourProfits = <?= json_encode($data['tourProfits'] ?? []) ?>;

        // Monthly Financial Chart
        const monthlyCtx = document.getElementById('monthlyFinancialChart');
        if (monthlyCtx && monthlyLabels.length > 0) {
            new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: 'Doanh Thu',
                        data: monthlyRevenue,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: '#10b981',
                        borderWidth: 1,
                        borderRadius: 4
                    }, {
                        label: 'Chi Phí',
                        data: monthlyExpense,
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: '#ef4444',
                        borderWidth: 1,
                        borderRadius: 4
                    }, {
                        label: 'Lợi Nhuận',
                        data: monthlyProfit,
                        backgroundColor: 'rgba(102, 126, 234, 0.7)',
                        borderColor: '#667eea',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' +
                                        new Intl.NumberFormat('vi-VN').format(context.raw) + ' ₫';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) return (value / 1000000).toFixed(0) + 'tr';
                                    if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
                                    return value;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Tour Profit Pie Chart
        const profitCtx = document.getElementById('tourProfitChart');
        if (profitCtx && tourNames.length > 0) {
            new Chart(profitCtx, {
                type: 'doughnut',
                data: {
                    labels: tourNames,
                    datasets: [{
                        data: tourProfits,
                        backgroundColor: [
                            '#667eea',
                            '#10b981',
                            '#f59e0b',
                            '#ef4444',
                            '#8b5cf6',
                            '#ec4899'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = new Intl.NumberFormat('vi-VN').format(context.raw) + ' ₫';
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.raw / total) * 100).toFixed(1) + '%';
                                    return value + ' (' + percentage + ')';
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    function resetFilters() {
        window.location.href = '<?= BASE_URL_ADMIN ?>&action=reports/financial';
    }
</script>

<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>