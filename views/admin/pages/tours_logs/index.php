<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>
<main class="dashboard tour-logs-page">
    <div class="dashboard-container">
        <!-- Page Header -->
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
                        <span class="breadcrumb-current">Nhật ký Tour</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-clipboard-list title-icon"></i>
                            Quản lý Nhật ký Tour
                        </h1>
                        <p class="page-subtitle">Theo dõi và quản lý nhật ký hoạt động của các tour du lịch</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Tours Grid -->
        <div class="row g-4">
            <?php if (!empty($tours)): ?>
                <?php foreach ($tours as $tour): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0 tour-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title fw-bold text-primary mb-0">
                                        <?= htmlspecialchars($tour['name']) ?>
                                    </h5>
                                    <span class="badge bg-light text-dark border">
                                        #<?= htmlspecialchars($tour['id']) ?>
                                    </span>
                                </div>
                                
                                <div class="tour-stats mb-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle me-3 p-2">
                                            <i class="fas fa-book-open"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Tổng số nhật ký</small>
                                            <span class="fw-bold fs-5"><?= $tour['log_count'] ?></span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-info bg-opacity-10 text-info rounded-circle me-3 p-2">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Cập nhật lần cuối</small>
                                            <span class="fw-medium">
                                                <?= $tour['last_log_date'] ? date('d/m/Y', strtotime($tour['last_log_date'])) : 'Chưa có' ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <a href="<?= BASE_URL_ADMIN . '&action=tours_logs/tour_detail&id=' . $tour['id'] ?>" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-eye me-2"></i>Xem chi tiết nhật ký
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="empty-state">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có tour nào</h5>
                            <p class="text-muted">Hiện tại chưa có tour nào để ghi nhật ký.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>