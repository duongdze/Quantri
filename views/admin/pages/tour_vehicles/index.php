<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>

<main class="dashboard">
    <div class="dashboard-container">
        <!-- Modern Header -->
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
                        <a href="<?= BASE_URL_ADMIN ?>&action=available-tours" class="breadcrumb-link">
                            <span>Tour khả dụng</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current">Quản lý xe</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-bus title-icon"></i>
                            Danh sách xe - Tour #<?= $assignment_id ?>
                        </h1>
                        <p class="page-subtitle">Quản lý phương tiện di chuyển cho chuyến đi này</p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=guide/schedule" class="btn btn-outline-secondary btn-lg me-2">
                        <i class="fas fa-arrow-left me-2"></i>
                        Quay lại
                    </a>
                    <a href="<?= BASE_URL_ADMIN ?>&action=tour_vehicles/create&assignment_id=<?= $assignment_id ?>" class="btn btn-modern btn-primary btn-lg">
                        <i class="fas fa-plus-circle me-2"></i>
                        Thêm xe mới
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

        <!-- Content -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Biển số</th>
                                <th class="py-3 text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Nhà xe</th>
                                <th class="py-3 text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Loại xe</th>
                                <th class="py-3 text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Tài xế</th>
                                <th class="py-3 text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Liên hệ</th>
                                <th class="py-3 text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Trạng thái</th>
                                <th class="text-end pe-4 py-3 text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($vehicles)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <div style="width: 60px; height: 60px; background: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                                <i class="fas fa-bus fa-2x text-secondary opacity-50"></i>
                                            </div>
                                            <p class="mb-0 fw-medium">Chưa có xe nào được phân công</p>
                                            <p class="small text-muted">Vui lòng thêm xe mới cho chuyến đi này</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($vehicles as $v): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center border">
                                                    <i class="fas fa-bus text-primary"></i>
                                                </div>
                                                <span class="fw-bold text-dark"><?= htmlspecialchars($v['vehicle_plate']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-secondary text-sm font-weight-bold"><?= htmlspecialchars($v['company_name'] ?? '---') ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($v['vehicle_type']) ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($v['driver_name'])): ?>
                                                <div class="d-flex flex-column">
                                                    <span class="text-dark fw-bold text-sm"><?= htmlspecialchars($v['driver_name']) ?></span>
                                                    <?php if (!empty($v['driver_license'])): ?>
                                                        <span class="text-xs text-secondary"><i class="far fa-id-card me-1"></i><?= htmlspecialchars($v['driver_license']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted text-sm">---</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($v['driver_phone'])): ?>
                                                <a href="tel:<?= htmlspecialchars($v['driver_phone']) ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1">
                                                    <i class="fas fa-phone-alt me-1"></i><?= htmlspecialchars($v['driver_phone']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">---</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusMap = [
                                                'assigned' => ['class' => 'bg-info', 'label' => 'Đã phân công'],
                                                'confirmed' => ['class' => 'bg-primary', 'label' => 'Đã xác nhận'],
                                                'completed' => ['class' => 'bg-success', 'label' => 'Hoàn thành'],
                                                'cancelled' => ['class' => 'bg-danger', 'label' => 'Hủy']
                                            ];
                                            $s = $statusMap[$v['status']] ?? ['class' => 'bg-secondary', 'label' => $v['status']];
                                            ?>
                                            <span class="badge <?= $s['class'] ?> bg-gradient-<?= str_replace('bg-', '', $s['class']) ?>"><?= $s['label'] ?></span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <a href="<?= BASE_URL_ADMIN ?>&action=tour_vehicles/edit&id=<?= $v['id'] ?>" 
                                                   class="btn btn-link text-primary px-3 mb-0" title="Sửa">
                                                    <i class="fas fa-pencil-alt me-2"></i>Sửa
                                                </a>
                                                <a href="<?= BASE_URL_ADMIN ?>&action=tour_vehicles/delete&id=<?= $v['id'] ?>&assignment_id=<?= $assignment_id ?>"
                                                    class="btn btn-link text-danger px-3 mb-0"
                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa xe này?');" title="Xóa">
                                                    <i class="far fa-trash-alt me-2"></i>Xóa
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>