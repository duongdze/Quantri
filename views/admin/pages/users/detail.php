<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

$roleMap = [
    'customer' => ['text' => 'Khách hàng', 'class' => 'success', 'icon' => 'user']
];
$roleInfo = $roleMap[$user['role']] ?? ['text' => 'Khách hàng', 'class' => 'success', 'icon' => 'user'];
?>

<main class="dashboard user-detail-page">
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
                        <a href="<?= BASE_URL_ADMIN ?>&action=users" class="breadcrumb-link">
                            <i class="fas fa-users"></i>
                            <span>Quản lý User</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current">Chi tiết User</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-user-circle title-icon"></i>
                            <?= htmlspecialchars($user['full_name']) ?>
                        </h1>
                        <p class="page-subtitle"><?= $roleInfo['text'] ?></p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=users/edit&id=<?= $user['user_id'] ?>" class="btn btn-modern btn-secondary">
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa
                    </a>
                    <a href="<?= BASE_URL_ADMIN ?>&action=users" class="btn btn-modern btn-primary">
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

        <!-- Statistics Cards -->
        <section class="stats-section">
            <div class="stats-grid">
                <div class="stat-card stat-<?= $roleInfo['class'] ?>">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-<?= $roleInfo['icon'] ?>"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $roleInfo['text'] ?></div>
                        <div class="stat-label">Vai trò</div>
                    </div>
                </div>

                <div class="stat-card stat-primary">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= date('d/m/Y', strtotime($user['created_at'])) ?></div>
                        <div class="stat-label">Ngày tạo</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">Hoạt động</div>
                        <div class="stat-label">Trạng thái</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">
                            <?php
                            $days = floor((time() - strtotime($user['created_at'])) / 86400);
                            echo $days . ' ngày';
                            ?>
                        </div>
                        <div class="stat-label">Thời gian tham gia</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content Grid -->
        <div class="row">
            <!-- Main Column (Left) -->
            <div class="col-lg-8">
                <!-- User Information Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user text-primary me-2"></i>
                            Thông tin người dùng
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">ID User</label>
                                    <div class="info-value">#<?= $user['user_id'] ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Họ và tên</label>
                                    <div class="info-value"><?= htmlspecialchars($user['full_name']) ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Email</label>
                                    <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Số điện thoại</label>
                                    <div class="info-value"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Vai trò</label>
                                    <div class="info-value">
                                        <span class="badge bg-<?= $roleInfo['class'] ?>">
                                            <i class="fas fa-<?= $roleInfo['icon'] ?> me-1"></i>
                                            <?= $roleInfo['text'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Ngày tạo</label>
                                    <div class="info-value"><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></div>
                                </div>
                            </div>
                            <?php if (!empty($user['updated_at'])): ?>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">Cập nhật lần cuối</label>
                                        <div class="info-value"><?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar (Right) -->
            <div class="col-lg-4">
                <!-- Avatar Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-image text-primary me-2"></i>
                            Avatar
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="avatar-placeholder rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                            style="width: 150px; height: 150px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-<?= $roleInfo['icon'] ?> fa-4x text-white"></i>
                        </div>
                        <h5><?= htmlspecialchars($user['full_name']) ?></h5>
                        <p class="text-muted mb-0"><?= $roleInfo['text'] ?></p>
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
                                    <span><?= htmlspecialchars($user['email']) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-phone text-success me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Điện thoại</small>
                                    <span><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt text-warning me-2"></i>
                            Thao tác nhanh
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?= BASE_URL_ADMIN ?>&action=users/edit&id=<?= $user['user_id'] ?>" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>
                                Chỉnh sửa thông tin
                            </a>
                            <?php if ($user['role'] === 'customer'): ?>
                                <a href="<?= BASE_URL_ADMIN ?>&action=bookings/create&customer_id=<?= $user['user_id'] ?>" class="btn btn-success">
                                    <i class="fas fa-calendar-plus me-2"></i>
                                    Tạo booking
                                </a>
                            <?php endif; ?>
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