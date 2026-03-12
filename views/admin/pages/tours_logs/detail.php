<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>
<main class="dashboard tour-log-detail-page">
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
                        <a href="<?= BASE_URL_ADMIN ?>&action=tours_logs" class="breadcrumb-link">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Nhật ký Tour</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current">Chi tiết nhật ký</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-file-alt title-icon"></i>
                            Chi tiết nhật ký
                        </h1>
                        <p class="page-subtitle">Ngày: <?= date('d/m/Y', strtotime($log['date'])) ?></p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN . '&action=tours_logs/edit&id=' . $log['id'] ?>" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Chỉnh sửa
                    </a>
                </div>
            </div>
        </header>

        <!-- Log Details -->
        <div class="row g-4">
            <!-- Main Info -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Thông tin chính
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Ngày ghi nhận</label>
                                <p class="fs-5"><?= date('d/m/Y', strtotime($log['date'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Hướng dẫn viên</label>
                                <p class="fs-5"><?= htmlspecialchars($log['guide_name'] ?? 'N/A') ?></p>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted">Mô tả hoạt động</label>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br(htmlspecialchars($log['description'] ?? '')) ?>
                            </div>
                        </div>

                        <?php if (!empty($log['weather'])): ?>
                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">Thời tiết</label>
                                <p><span class="badge bg-info bg-opacity-10 text-info border border-info fs-6">
                                        <i class="fas fa-cloud-sun me-1"></i><?= htmlspecialchars($log['weather']) ?>
                                    </span></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($log['special_activity'])): ?>
                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">Hoạt động đặc biệt</label>
                                <div class="bg-light p-3 rounded">
                                    <?= nl2br(htmlspecialchars($log['special_activity'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Issues & Solutions -->
                <?php if (!empty($log['issue']) || !empty($log['solution'])): ?>
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-danger bg-opacity-10 py-3">
                            <h5 class="card-title mb-0 text-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>Vấn đề & Giải pháp
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($log['issue'])): ?>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-danger">Vấn đề phát sinh</label>
                                    <div class="bg-danger bg-opacity-10 p-3 rounded border border-danger">
                                        <?= nl2br(htmlspecialchars($log['issue'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($log['solution'])): ?>
                                <div>
                                    <label class="form-label fw-bold text-success">Giải pháp đã thực hiện</label>
                                    <div class="bg-success bg-opacity-10 p-3 rounded border border-success">
                                        <?= nl2br(htmlspecialchars($log['solution'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Customer Feedback -->
                <?php if (!empty($log['customer_feedback'])): ?>
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary bg-opacity-10 py-3">
                            <h5 class="card-title mb-0 text-primary">
                                <i class="fas fa-comments me-2"></i>Phản hồi của khách
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="bg-light p-3 rounded">
                                <?= nl2br(htmlspecialchars($log['customer_feedback'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Rating -->
                <?php if (isset($log['guide_rating']) && $log['guide_rating'] > 0): ?>
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body text-center">
                            <label class="form-label fw-bold text-muted">Tự đánh giá HDV</label>
                            <div class="display-4 text-warning my-3">
                                <?= $log['guide_rating'] ?> <i class="fas fa-star"></i>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-warning" style="width: <?= ($log['guide_rating'] / 5) * 100 ?>%"></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Health Status -->
                <?php if (!empty($log['health_status'])): ?>
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-heartbeat me-2 text-danger"></i>Tình trạng sức khỏe
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?= nl2br(htmlspecialchars($log['health_status'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Incident -->
                <?php if (!empty($log['incident'])): ?>
                    <div class="card shadow-sm border-0 mb-4 border-warning">
                        <div class="card-header bg-warning bg-opacity-10 py-3">
                            <h6 class="card-title mb-0 text-warning">
                                <i class="fas fa-exclamation-circle me-2"></i>Sự cố
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?= nl2br(htmlspecialchars($log['incident'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Handling Notes -->
                <?php if (!empty($log['handling_notes'])): ?>
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-clipboard-check me-2 text-success"></i>Ghi chú xử lý
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?= nl2br(htmlspecialchars($log['handling_notes'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>