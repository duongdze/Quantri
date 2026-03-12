<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

// Calculate stats (you may need to pass these from controller)
$totalTours = $guide['total_tours'] ?? 0;
$experienceYears = $guide['experience_years'] ?? 0;
$rating = $guide['rating'] ?? 4.5;
?>

<main class="dashboard guide-detail-page">
    <div class="dashboard-container">
        <!-- Modern Page Header -->
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
                        <a href="<?= BASE_URL_ADMIN ?>&action=guides" class="breadcrumb-link">
                            <i class="fas fa-user-tie"></i>
                            <span>Quản lý HDV</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current">Chi tiết HDV</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-user-tie title-icon"></i>
                            <?= htmlspecialchars($guide['full_name']) ?>
                        </h1>
                        <p class="page-subtitle">Hướng dẫn viên <?= htmlspecialchars($guide['guide_type'] ?? 'Nội địa') ?></p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=guides/edit&id=<?= $guide['id'] ?>" class="btn btn-modern btn-secondary">
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa
                    </a>
                    <a href="<?= BASE_URL_ADMIN ?>&action=guides" class="btn btn-modern btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Quay lại
                    </a>
                </div>
            </div>
        </header>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-modern alert-success alert-dismissible fade show" role="alert">
                <div class="alert-content">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <span><?= $_SESSION['success'] ?></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-modern alert-danger alert-dismissible fade show" role="alert">
                <div class="alert-content">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <span><?= $_SESSION['error'] ?></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <section class="stats-section">
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-route"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $totalTours ?></div>
                        <div class="stat-label">Tổng tour</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $experienceYears ?></div>
                        <div class="stat-label">Năm kinh nghiệm</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($rating, 1) ?></div>
                        <div class="stat-label">Đánh giá</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">Hoạt động</div>
                        <div class="stat-label">Trạng thái</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content Grid -->
        <div class="row">
            <!-- Main Column (Left) -->
            <div class="col-lg-8">
                <!-- Professional Information Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-briefcase text-primary me-2"></i>
                            Thông tin chuyên môn
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Loại hướng dẫn viên</label>
                                    <div class="info-value">
                                        <?php
                                        $typeMap = [
                                            'domestic' => 'Nội địa',
                                            'international' => 'Quốc tế',
                                            'specialized' => 'Chuyên môn'
                                        ];
                                        echo $typeMap[$guide['guide_type'] ?? 'domestic'] ?? 'Nội địa';
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Chuyên môn</label>
                                    <div class="info-value"><?= htmlspecialchars($guide['specialization'] ?? 'N/A') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Ngôn ngữ sử dụng</label>
                                    <div class="info-value"><?= htmlspecialchars($guide['languages'] ?? 'N/A') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Số năm kinh nghiệm</label>
                                    <div class="info-value"><?= $experienceYears ?> năm</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Tình trạng sức khỏe</label>
                                    <div class="info-value">
                                        <?php
                                        $healthStatus = $guide['health_status'] ?? 'Tốt';
                                        $healthClass = $healthStatus === 'Tốt' ? 'success' : ($healthStatus === 'Khá' ? 'info' : 'warning');
                                        ?>
                                        <span class="badge bg-<?= $healthClass ?>"><?= htmlspecialchars($healthStatus) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Card -->
                <?php if (!empty($guide['notes'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-comment text-warning me-2"></i>
                                Ghi chú
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?= nl2br(htmlspecialchars($guide['notes'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar (Right) -->
            <div class="col-lg-4">
                <!-- Avatar Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-image text-primary me-2"></i>
                            Ảnh đại diện
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($guide['avatar'])): ?>
                            <img src="<?= htmlspecialchars($guide['avatar']) ?>"
                                alt="Avatar"
                                class="img-fluid rounded-circle mb-3"
                                style="width: 200px; height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="avatar-placeholder rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                                style="width: 200px; height: 200px; background: #e9ecef;">
                                <i class="fas fa-user fa-5x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <h5><?= htmlspecialchars($guide['full_name']) ?></h5>
                        <p class="text-muted mb-2">Hướng dẫn viên</p>
                        <div class="rating mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $rating ? 'text-warning' : 'text-muted' ?>"></i>
                            <?php endfor; ?>
                            <span class="text-muted ms-1">(<?= number_format($rating, 1) ?>)</span>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-address-card text-success me-2"></i>
                            Thông tin liên hệ
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="contact-item mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Email</small>
                                    <span><?= htmlspecialchars($guide['email'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-phone text-success me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Điện thoại</small>
                                    <span><?= htmlspecialchars($guide['phone'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .info-item {
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .info-label {
        display: block;
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 500;
        color: #212529;
    }

    .contact-item {
        padding: 10px;
        background: #f8f9fa;
        border-radius: 6px;
    }
</style>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>