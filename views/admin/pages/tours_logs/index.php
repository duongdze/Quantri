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
        
        <!-- Filter Bar -->
        <div class="card shadow-sm border-0 mb-4 filter-card">
            <div class="card-body py-3">
                <form action="<?= BASE_URL_ADMIN ?>" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="mode" value="admin">
                    <input type="hidden" name="action" value="tours_logs">
                    
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Tìm kiếm tour</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="keyword" class="form-control bg-light border-start-0" placeholder="Nhập tên tour hoặc tên HDV..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
                        </div>
                    </div>

                    <?php if ($userRole === 'admin' && !empty($allGuides)): ?>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Hướng dẫn viên</label>
                        <select name="guide_id" class="form-select bg-light">
                            <option value="">Tất cả HDV</option>
                            <?php foreach ($allGuides as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= (isset($filters['guide_id']) && $filters['guide_id'] == $g['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($g['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Trạng thái đoàn</label>
                        <select name="status" class="form-select bg-light">
                            <option value="">Tất cả trạng thái</option>
                            <option value="active" <?= (isset($filters['status']) && $filters['status'] == 'active') ? 'selected' : '' ?>>Đang đi (Active)</option>
                            <option value="completed" <?= (isset($filters['status']) && $filters['status'] == 'completed') ? 'selected' : '' ?>>Đã xong (Completed)</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i> Lọc
                            </button>
                            <a href="<?= BASE_URL_ADMIN ?>&action=tours_logs" class="btn btn-light" title="Xóa bộ lọc">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

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
                                    <div class="mb-1">
                                        <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i> Khởi hành: <?= date('d/m/Y', strtotime($tour['start_date'])) ?></small>
                                    </div>
                                    <span class="badge bg-light text-dark border">
                                        Chuyến đi #<?= htmlspecialchars($tour['assignment_id']) ?>
                                    </span>
                                </div>
                                <div class="guide-info-quick mb-3 d-flex align-items-center">
                                    <div class="guide-avatar-mini me-2">
                                        <div class="avatar-circle-xs bg-info bg-opacity-10 text-info">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                    </div>
                                    <div class="guide-name-text">
                                        <small class="text-muted d-block">Hướng dẫn viên</small>
                                        <span class="fw-bold text-dark small"><?= htmlspecialchars($tour['guide_name'] ?? 'N/A') ?></span>
                                    </div>
                                    <div class="ms-auto">
                                        <?php if ($tour['status'] === 'active'): ?>
                                            <span class="badge rounded-pill bg-success" style="font-size: 0.65rem;">Đang đi</span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-secondary" style="font-size: 0.65rem;">Đã xong</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="tour-stats mb-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle me-3 p-2">
                                            <i class="fas fa-book-open"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Nhật ký đoàn này</small>
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

                                <a href="<?= BASE_URL_ADMIN . '&action=tours_logs/tour_detail&assignment_id=' . $tour['assignment_id'] ?>" class="btn btn-outline-primary w-100">
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