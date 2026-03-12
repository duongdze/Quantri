<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

// Helper for price formatting
function formatPrice($price)
{
    if ($price >= 1000000000) {
        return round($price / 1000000000, ($price / 1000000000) >= 10 ? 0 : 1) . ' tỷ';
    } elseif ($price >= 1000000) {
        return round($price / 1000000, 1) . ' tr';
    } else {
        return number_format($price, 0, ',', '.') . 'đ';
    }
}

// Status mapping
$statusMap = [
    'cho_xac_nhan' => ['text' => 'Chờ xác nhận', 'class' => 'warning', 'icon' => 'clock'],
    'da_coc' => ['text' => 'Đã cọc', 'class' => 'info', 'icon' => 'credit-card'],
    'hoan_tat' => ['text' => 'Hoàn tất', 'class' => 'success', 'icon' => 'check-circle'],
    'da_huy' => ['text' => 'Đã hủy', 'class' => 'danger', 'icon' => 'times-circle']
];

$currentStatus = $statusMap[$booking['status']] ?? ['text' => 'Unknown', 'class' => 'secondary', 'icon' => 'question'];

// Check edit permission - Chỉ admin mới được edit
$userRole = $_SESSION['user']['role'] ?? 'customer';
$canEdit = ($userRole === 'admin');
?>

<main class="dashboard booking-detail-page">
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
                        <a href="<?= BASE_URL_ADMIN ?>&action=bookings" class="breadcrumb-link">
                            <i class="fas fa-calendar-check"></i>
                            <span>Quản lý Booking</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current">Chi tiết #<?= $booking['id'] ?></span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-file-invoice title-icon"></i>
                            Booking #<?= $booking['id'] ?>
                        </h1>
                        <p class="page-subtitle"><?= htmlspecialchars($booking['tour_name'] ?? 'Tour') ?></p>
                    </div>
                </div>
                <div class="header-right">
                    <?php if ($canEdit): ?>
                        <a href="<?= BASE_URL_ADMIN ?>&action=bookings/edit&id=<?= $booking['id'] ?>" class="btn btn-modern btn-secondary">
                            <i class="fas fa-edit me-2"></i>
                            Chỉnh sửa
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL_ADMIN ?>&action=bookings" class="btn btn-modern btn-primary">
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
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= formatPrice($booking['total_price'] ?? 0) ?></div>
                        <div class="stat-label">Tổng tiền</div>
                    </div>
                </div>

                <div class="stat-card stat-<?= $currentStatus['class'] ?>">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-<?= $currentStatus['icon'] ?>"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $currentStatus['text'] ?></div>
                        <div class="stat-label">Trạng thái</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= date('d/m/Y', strtotime($booking['booking_date'])) ?></div>
                        <div class="stat-label">Ngày đặt</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= count($companions) + 1 ?></div>
                        <div class="stat-label">Số khách</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content Grid -->
        <div class="row">
            <!-- Main Column (Left) -->
            <div class="col-lg-8">
                <!-- Booking Information Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Thông tin đơn đặt
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Mã Booking</label>
                                    <div class="info-value">#<?= htmlspecialchars($booking['id']) ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Ngày đặt</label>
                                    <div class="info-value"><?= date('d/m/Y H:i', strtotime($booking['booking_date'])) ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Tổng giá</label>
                                    <div class="info-value text-danger fw-bold"><?= number_format($booking['total_price'], 0, ',', '.') ?> ₫</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Trạng thái</label>
                                    <div class="info-value">
                                        <span id="status-badge" class="badge bg-<?= $currentStatus['class'] ?>" data-status="<?= $booking['status'] ?>">
                                            <i class="fas fa-<?= $currentStatus['icon'] ?> me-1"></i>
                                            <?= $currentStatus['text'] ?>
                                        </span>
                                        <?php if ($canEdit): ?>
                                            <div class="dropdown d-inline-block ms-2">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item status-change-btn" href="#" data-status="cho_xac_nhan" data-booking-id="<?= $booking['id'] ?>">Chờ xác nhận</a></li>
                                                    <li><a class="dropdown-item status-change-btn" href="#" data-status="da_coc" data-booking-id="<?= $booking['id'] ?>">Đã cọc</a></li>
                                                    <li><a class="dropdown-item status-change-btn" href="#" data-status="hoan_tat" data-booking-id="<?= $booking['id'] ?>">Hoàn tất</a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li><a class="dropdown-item status-change-btn text-danger" href="#" data-status="da_huy" data-booking-id="<?= $booking['id'] ?>">Hủy</a></li>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guests List Card -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users text-success me-2"></i>
                            Danh sách khách (<?= count($companions) + 1 ?>)
                        </h5>
                        <?php if ($canEdit): ?>
                            <button type="button" class="btn btn-sm btn-primary" id="add-companion-btn" data-booking-id="<?= $booking['id'] ?>">
                                <i class="fas fa-plus me-1"></i>
                                Thêm khách
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <!-- Main Customer -->
                        <div class="guest-item mb-3">
                            <div class="guest-header">
                                <span class="badge bg-primary">Khách chính</span>
                                <h6 class="mb-0 ms-2">
                                    <?php
                                    if (!empty($booking['customer_name'])) {
                                        echo htmlspecialchars($booking['customer_name']);
                                    } elseif (!empty($companions[0]['full_name'])) {
                                        echo htmlspecialchars($companions[0]['full_name']) . ' (Khách vãng lai)';
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </h6>
                            </div>
                            <div class="guest-details mt-2">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <small class="text-muted">Email:</small>
                                        <div>
                                            <?php
                                            if (!empty($booking['customer_email'])) {
                                                echo htmlspecialchars($booking['customer_email']);
                                            } else {
                                                // Try to match email from notes
                                                preg_match('/Email: (.*)/', $booking['notes'] ?? '', $matches);
                                                echo !empty($matches[1]) ? htmlspecialchars(trim($matches[1])) : 'Xem ghi chú';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Điện thoại:</small>
                                        <div>
                                            <?php
                                            if (!empty($booking['customer_phone'])) {
                                                echo htmlspecialchars($booking['customer_phone']);
                                            } elseif (!empty($companions[0]['phone'])) {
                                                echo htmlspecialchars($companions[0]['phone']);
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Companions -->
                        <?php if (!empty($companions)): ?>
                            <hr>
                            <div class="companions-list">
                                <?php foreach ($companions as $index => $companion): ?>
                                    <div class="guest-item mb-3">
                                        <div class="guest-header">
                                            <span class="badge bg-secondary">Khách #<?= $index + 1 ?></span>
                                            <h6 class="mb-0 ms-2"><?= htmlspecialchars($companion['full_name']) ?></h6>
                                            <?php if ($canEdit): ?>
                                                <div class="ms-auto">
                                                    <button class="btn btn-sm btn-outline-primary edit-companion-btn me-1"
                                                        data-companion-id="<?= $companion['id'] ?>"
                                                        data-companion='<?= json_encode([
                                                                            "name" => $companion["full_name"] ?? "",
                                                                            "gender" => $companion["gender"] ?? "",
                                                                            "birth_date" => $companion["birth_date"] ?? "",
                                                                            "phone" => $companion["phone"] ?? "",
                                                                            "id_card" => $companion["id_card"] ?? "",
                                                                            "room_type" => $companion["room_type"] ?? "",
                                                                            "passenger_type" => $companion["passenger_type"] ?? "adult",
                                                                            "special_request" => $companion["special_request"] ?? ""
                                                                        ]) ?>'>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger delete-companion-btn" data-companion-id="<?= $companion['id'] ?>" data-booking-id="<?= $booking['id'] ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="guest-details mt-2">
                                            <div class="row g-2">
                                                <?php if (!empty($companion['gender'])): ?>
                                                    <div class="col-md-4">
                                                        <small class="text-muted">Giới tính:</small>
                                                        <div><?= htmlspecialchars($companion['gender']) ?></div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($companion['birth_date'])): ?>
                                                    <div class="col-md-4">
                                                        <small class="text-muted">Ngày sinh:</small>
                                                        <div><?= htmlspecialchars($companion['birth_date']) ?></div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($companion['phone'])): ?>
                                                    <div class="col-md-4">
                                                        <small class="text-muted">Điện thoại:</small>
                                                        <div><?= htmlspecialchars($companion['phone']) ?></div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($companion['id_card'])): ?>
                                                    <div class="col-md-6">
                                                        <small class="text-muted">CMND/Hộ chiếu:</small>
                                                        <div><?= htmlspecialchars($companion['id_card']) ?></div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($companion['room_type'])): ?>
                                                    <div class="col-md-6">
                                                        <small class="text-muted">Loại phòng:</small>
                                                        <div><?= htmlspecialchars($companion['room_type']) ?></div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($companion['special_request']) || $canEdit): ?>
                                                    <div class="col-12">
                                                        <small class="text-muted">Yêu cầu đặc biệt:</small>
                                                        <div class="d-flex align-items-start">
                                                            <div class="flex-grow-1" id="special-request-<?= $companion['id'] ?>">
                                                                <?= !empty($companion['special_request']) ? htmlspecialchars($companion['special_request']) : '<span class="text-muted">Chưa có</span>' ?>
                                                            </div>
                                                            <?php if ($canEdit): ?>
                                                                <button class="btn btn-sm btn-outline-primary ms-2 edit-special-request-btn"
                                                                    data-companion-id="<?= $companion['id'] ?>"
                                                                    data-booking-id="<?= $booking['id'] ?>"
                                                                    data-current-request="<?= htmlspecialchars($companion['special_request'] ?? '') ?>"
                                                                    title="Sửa yêu cầu đặc biệt">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-user-plus fa-2x mb-2"></i>
                                <p class="mb-0">Chưa có khách đi kèm</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Price Calculator Card -->
                <?php if ($canEdit && !empty($booking['version_id'])): ?>
                    <?php
                    // Calculate current pricing
                    $bookingCustomerModel = new BookingCustomer();
                    $calculation = $bookingCustomerModel->calculateTotalPrice($booking['id'], $booking['tour_id'], $booking['version_id']);
                    $breakdown = $calculation['breakdown'];
                    ?>
                    <div class="card mb-4" style="background-color: #fff9e6; border-left: 4px solid #ffc107;">
                        <div class="card-header" style="background-color: #fff3cd; border-bottom: 1px solid #ffc107;">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-calculator text-warning me-2"></i>
                                Tính giá theo phiên bản tour
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="price-breakdown">
                                <?php if ($breakdown['adults']['count'] > 0): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2" style="background-color: #fff;">
                                        <span>
                                            <i class="fas fa-user text-primary me-2"></i>
                                            <strong>Người lớn</strong> × <?= $breakdown['adults']['count'] ?>
                                        </span>
                                        <span class="text-muted">
                                            <?= number_format($breakdown['adults']['price']) ?> ₫ × <?= $breakdown['adults']['count'] ?> =
                                            <strong class="text-primary"><?= number_format($breakdown['adults']['subtotal']) ?> ₫</strong>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($breakdown['children']['count'] > 0): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2" style="background-color: #fff;">
                                        <span>
                                            <i class="fas fa-child text-info me-2"></i>
                                            <strong>Trẻ em</strong> × <?= $breakdown['children']['count'] ?>
                                        </span>
                                        <span class="text-muted">
                                            <?= number_format($breakdown['children']['price']) ?> ₫ × <?= $breakdown['children']['count'] ?> =
                                            <strong class="text-info"><?= number_format($breakdown['children']['subtotal']) ?> ₫</strong>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($breakdown['infants']['count'] > 0): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2" style="background-color: #fff;">
                                        <span>
                                            <i class="fas fa-baby text-success me-2"></i>
                                            <strong>Em bé</strong> × <?= $breakdown['infants']['count'] ?>
                                        </span>
                                        <span class="text-muted">
                                            <?= number_format($breakdown['infants']['price']) ?> ₫ × <?= $breakdown['infants']['count'] ?> =
                                            <strong class="text-success"><?= number_format($breakdown['infants']['subtotal']) ?> ₫</strong>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <hr style="border-color: #ffc107;">

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-muted">Tổng cộng:</strong>
                                    <h4 class="mb-0 text-danger"><?= number_format($calculation['total']) ?> ₫</h4>
                                </div>
                                <form method="POST" action="<?= BASE_URL_ADMIN ?>&action=bookings/detail&id=<?= $booking['id'] ?>" style="display: inline;">
                                    <input type="hidden" name="update_price" value="1">
                                    <input type="hidden" name="calculated_total" value="<?= $calculation['total'] ?>">
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Cập nhật giá booking thành <?= number_format($calculation['total']) ?> ₫?')">
                                        <i class="fas fa-sync-alt me-1"></i>
                                        Cập nhật giá
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Notes Card -->
                <?php if (!empty($booking['notes'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-comment text-warning me-2"></i>
                                Ghi chú
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?= nl2br(htmlspecialchars($booking['notes'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar (Right) -->
            <div class="col-lg-4">
                <!-- Customer Info Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user text-primary me-2"></i>
                            Thông tin khách hàng
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="customer-info">
                            <h6 class="mb-3"><?= htmlspecialchars($booking['customer_name'] ?? 'N/A') ?></h6>
                            <div class="info-row mb-2">
                                <i class="fas fa-envelope text-muted me-2"></i>
                                <span><?= htmlspecialchars($booking['customer_email'] ?? 'N/A') ?></span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-phone text-muted me-2"></i>
                                <span><?= htmlspecialchars($booking['customer_phone'] ?? 'N/A') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tour Info Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-route text-success me-2"></i>
                            Thông tin tour
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-3"><?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?></h6>
                        <div class="info-row mb-2">
                            <span class="text-muted">Giá cơ bản:</span>
                            <span class="fw-bold"><?= number_format($booking['tour_base_price'] ?? 0, 0, ',', '.') ?> ₫</span>
                        </div>
                        <a href="<?= BASE_URL_ADMIN ?>&action=tours/detail&id=<?= $booking['tour_id'] ?>" class="btn btn-sm btn-outline-primary w-100 mt-2">
                            <i class="fas fa-eye me-1"></i>
                            Xem chi tiết tour
                        </a>
                    </div>
                </div>

                <!-- Staff Assignment Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users-cog text-info me-2"></i>
                            Phân công nhân sự
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="staff-item mb-3">
                            <label class="text-muted small">Hướng dẫn viên</label>
                            <div class="fw-medium">
                                <?php if (!empty($booking['guide_name'])): ?>
                                    <i class="fas fa-user-tie text-info me-1"></i>
                                    <?= htmlspecialchars($booking['guide_name']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Chưa phân công</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="staff-item">
                            <label class="text-muted small">Tài xế</label>
                            <div class="fw-medium">
                                <?php if (!empty($booking['driver_name'])): ?>
                                    <i class="fas fa-car text-secondary me-1"></i>
                                    <?= htmlspecialchars($booking['driver_name']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Chưa phân công</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Suppliers Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-building text-warning me-2"></i>
                            Nhà cung cấp
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Load booking suppliers
                        $bsaModel = new BookingSupplierAssignment();
                        $bookingSuppliers = $bsaModel->getByBookingId($booking['id']);

                        // Load tour default supplier for comparison
                        $tourDefaultSupplier = null;
                        if (!empty($booking['tour_supplier_id'])) {
                            $supplierModel = new Supplier();
                            $tourDefaultSupplier = $supplierModel->find('*', 'id = :id', ['id' => $booking['tour_supplier_id']]);
                        }
                        ?>

                        <?php if ($tourDefaultSupplier): ?>
                            <!-- Tour Default Supplier -->
                            <div class="alert alert-light border mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-route text-primary me-2"></i>
                                    <strong>Supplier mặc định từ tour:</strong>
                                </div>
                                <div class="ps-4">
                                    <div class="mb-1">
                                        <i class="fas fa-building text-muted me-2"></i>
                                        <strong><?= htmlspecialchars($tourDefaultSupplier['name']) ?></strong>
                                        <?php if (!empty($tourDefaultSupplier['type'])): ?>
                                            <span class="badge bg-secondary ms-2"><?= htmlspecialchars($tourDefaultSupplier['type']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($tourDefaultSupplier['phone'])): ?>
                                        <div class="small text-muted">
                                            <i class="fas fa-phone me-1"></i>
                                            <?= htmlspecialchars($tourDefaultSupplier['phone']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <h6 class="text-muted mb-3">
                                <i class="fas fa-list me-2"></i>
                                Suppliers được sử dụng cho booking này:
                            </h6>
                        <?php endif; ?>

                        <?php if (!empty($bookingSuppliers)): ?>
                            <div class="suppliers-list">
                                <?php foreach ($bookingSuppliers as $bs): ?>
                                    <div class="supplier-item mb-3 pb-3 border-bottom">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">
                                                <i class="fas fa-handshake text-info me-1"></i>
                                                <?= htmlspecialchars($bs['supplier_name']) ?>
                                            </h6>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($bs['service_type']) ?></span>
                                        </div>

                                        <?php if (!empty($bs['supplier_type'])): ?>
                                            <div class="small text-muted mb-1">
                                                <i class="fas fa-tag me-1"></i>
                                                <?= htmlspecialchars($bs['supplier_type']) ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="row g-2 mt-2 small">
                                            <div class="col-6">
                                                <span class="text-muted">Số lượng:</span>
                                                <span class="fw-medium"><?= $bs['quantity'] ?></span>
                                            </div>
                                            <?php if (!empty($bs['price'])): ?>
                                                <div class="col-6">
                                                    <span class="text-muted">Giá:</span>
                                                    <span class="fw-medium text-primary"><?= number_format($bs['price'], 0, ',', '.') ?> ₫</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($bs['notes'])): ?>
                                            <div class="small text-muted mt-2">
                                                <i class="fas fa-sticky-note me-1"></i>
                                                <?= htmlspecialchars($bs['notes']) ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($bs['supplier_phone']) || !empty($bs['supplier_email'])): ?>
                                            <div class="mt-2 pt-2 border-top small">
                                                <?php if (!empty($bs['supplier_phone'])): ?>
                                                    <div class="mb-1">
                                                        <i class="fas fa-phone text-muted me-1"></i>
                                                        <a href="tel:<?= $bs['supplier_phone'] ?>"><?= htmlspecialchars($bs['supplier_phone']) ?></a>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($bs['supplier_email'])): ?>
                                                    <div>
                                                        <i class="fas fa-envelope text-muted me-1"></i>
                                                        <a href="mailto:<?= $bs['supplier_email'] ?>"><?= htmlspecialchars($bs['supplier_email']) ?></a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php
                            // Calculate total supplier cost
                            $totalSupplierCost = $bsaModel->getTotalCostByBookingId($booking['id']);
                            if ($totalSupplierCost > 0):
                            ?>
                                <div class="alert alert-info mb-0 mt-3">
                                    <strong>Tổng chi phí suppliers:</strong><br>
                                    <span class="fs-5"><?= number_format($totalSupplierCost, 0, ',', '.') ?> ₫</span>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-building fa-2x mb-2"></i>
                                <p class="mb-0 small">Chưa có nhà cung cấp nào</p>
                                <?php if ($canEdit): ?>
                                    <a href="<?= BASE_URL_ADMIN ?>&action=bookings/edit&id=<?= $booking['id'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fas fa-plus me-1"></i>
                                        Thêm supplier
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Companion Modal -->
    <div class="modal fade" id="companionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="companionModalTitle">Thêm Khách Đi Kèm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="companionForm">
                        <input type="hidden" id="companion-id" name="companion_id">
                        <input type="hidden" id="companion-booking-id" name="booking_id" value="<?= $booking['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="companion-name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Giới tính</label>
                                <select class="form-select" id="companion-gender" name="gender">
                                    <option value="">Chọn</option>
                                    <option value="Nam">Nam</option>
                                    <option value="Nữ">Nữ</option>
                                    <option value="Khác">Khác</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ngày sinh</label>
                                <input type="date" class="form-control" id="companion-birth-date" name="birth_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Điện thoại</label>
                                <input type="tel" class="form-control" id="companion-phone" name="phone">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">CMND/Hộ chiếu</label>
                                <input type="text" class="form-control" id="companion-id-card" name="id_card">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Loại phòng</label>
                                <select class="form-select" id="companion-room-type" name="room_type">
                                    <option value="">Chọn loại phòng</option>
                                    <option value="đơn">Phòng đơn</option>
                                    <option value="đôi">Phòng đôi</option>
                                    <option value="ghép">Ghép phòng</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Loại khách <span class="text-danger">*</span></label>
                                <select class="form-select" id="companion-passenger-type" name="passenger_type" required>
                                    <option value="">Chọn loại khách</option>
                                    <option value="adult" selected>Người lớn</option>
                                    <option value="child">Trẻ em</option>
                                    <option value="infant">Em bé</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Yêu cầu đặc biệt</label>
                                <textarea class="form-control" id="companion-special-request" name="special_request" rows="3"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="saveCompanionBtn">Lưu</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="specialRequestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cập Nhật Yêu Cầu Đặc Biệt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="specialRequestForm">
                        <input type="hidden" id="sr-companion-id">
                        <input type="hidden" id="sr-booking-id">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Yêu cầu đặc biệt</label>
                            <textarea class="form-control" id="sr-special-request" rows="4"
                                placeholder="Ví dụ: Ăn chay, dị ứng hải sản, cần xe lăn..."></textarea>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Ghi chú các yêu cầu đặc biệt của khách để phục vụ tốt hơn
                            </small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="saveSpecialRequestBtn">
                        <i class="fas fa-save me-1"></i>Lưu
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Special Request Update
    document.addEventListener('DOMContentLoaded', function() {
        const specialRequestModal = new bootstrap.Modal(document.getElementById('specialRequestModal'));

        // Open modal
        document.querySelectorAll('.edit-special-request-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const companionId = this.dataset.companionId;
                const bookingId = this.dataset.bookingId;
                const currentRequest = this.dataset.currentRequest;

                document.getElementById('sr-companion-id').value = companionId;
                document.getElementById('sr-booking-id').value = bookingId;
                document.getElementById('sr-special-request').value = currentRequest;

                specialRequestModal.show();
            });
        });

        // Save special request
        document.getElementById('saveSpecialRequestBtn').addEventListener('click', function() {
            const companionId = document.getElementById('sr-companion-id').value;
            const bookingId = document.getElementById('sr-booking-id').value;
            const specialRequest = document.getElementById('sr-special-request').value;

            // AJAX call
            fetch('<?= BASE_URL_ADMIN ?>&action=bookings/updateSpecialRequest', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        companion_id: companionId,
                        booking_id: bookingId,
                        special_request: specialRequest
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update UI
                        const displayElement = document.getElementById('special-request-' + companionId);
                        if (displayElement) {
                            displayElement.innerHTML = specialRequest || '<span class="text-muted">Chưa có</span>';
                        }

                        // Update button data
                        const btn = document.querySelector(`[data-companion-id="${companionId}"]`);
                        if (btn) {
                            btn.dataset.currentRequest = specialRequest;
                        }

                        // Show success message
                        alert(data.message);
                        specialRequestModal.hide();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi cập nhật');
                });
        });
    });
</script>

<script>
    // Companion Management
    document.addEventListener('DOMContentLoaded', function() {
        const companionModalEl = document.getElementById('companionModal');
        const companionModal = new bootstrap.Modal(companionModalEl, {
            backdrop: true, // Allow closing by clicking backdrop
            keyboard: true // Allow closing with ESC key
        });

        // Close button listeners (X button and Cancel button)
        const closeButtons = companionModalEl.querySelectorAll('[data-bs-dismiss="modal"]');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                companionModal.hide();
            });
        });

        // Add companion button
        const addBtn = document.getElementById('add-companion-btn');
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                // Reset form for adding
                document.getElementById('companionModalTitle').textContent = 'Thêm Khách Đi Kèm';
                document.getElementById('companionForm').reset();
                document.getElementById('companion-id').value = '';
                companionModal.show();
            });
        }

        // Edit companion buttons
        document.querySelectorAll('.edit-companion-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const companionId = this.dataset.companionId;
                const data = JSON.parse(this.dataset.companion);

                // Set modal title
                document.getElementById('companionModalTitle').textContent = 'Chỉnh Sửa Khách Đi Kèm';

                // Set companion ID for update
                document.getElementById('companion-id').value = companionId;

                // Populate form with companion data
                document.getElementById('companion-name').value = data.name || '';
                document.getElementById('companion-gender').value = data.gender || '';
                document.getElementById('companion-birth-date').value = data.birth_date || '';
                document.getElementById('companion-phone').value = data.phone || '';
                document.getElementById('companion-id-card').value = data.id_card || '';
                document.getElementById('companion-room-type').value = data.room_type || '';
                document.getElementById('companion-passenger-type').value = data.passenger_type || 'adult';
                document.getElementById('companion-special-request').value = data.special_request || '';

                // Show modal
                companionModal.show();
            });
        });

        // Delete companion buttons
        document.querySelectorAll('.delete-companion-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const companionId = this.dataset.companionId;
                const bookingId = this.dataset.bookingId;
                const card = this.closest('.guest-item');
                const name = card.querySelector('h6').textContent.trim();

                if (!confirm(`Bạn có chắc muốn xóa khách "${name}"?`)) {
                    return;
                }

                // Delete via AJAX
                fetch('<?= BASE_URL_ADMIN ?>&action=bookings/deleteCompanion', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            companion_id: companionId,
                            booking_id: bookingId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Lỗi: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi xóa khách');
                    });
            });
        });

        // Save companion button
        const saveBtn = document.getElementById('saveCompanionBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                const companionId = document.getElementById('companion-id').value;
                const bookingId = document.getElementById('companion-booking-id').value;
                const name = document.getElementById('companion-name').value.trim();
                const passengerType = document.getElementById('companion-passenger-type').value;

                if (!name) {
                    alert('Vui lòng nhập họ tên khách');
                    return;
                }

                if (!passengerType) {
                    alert('Vui lòng chọn loại khách');
                    return;
                }

                const formData = new URLSearchParams({
                    booking_id: bookingId,
                    name: name,
                    gender: document.getElementById('companion-gender').value,
                    birth_date: document.getElementById('companion-birth-date').value,
                    phone: document.getElementById('companion-phone').value,
                    id_card: document.getElementById('companion-id-card').value,
                    room_type: document.getElementById('companion-room-type').value,
                    passenger_type: passengerType,
                    special_request: document.getElementById('companion-special-request').value
                });

                const url = companionId ?
                    '<?= BASE_URL_ADMIN ?>&action=bookings/updateCompanion' :
                    '<?= BASE_URL_ADMIN ?>&action=bookings/addCompanion';

                if (companionId) {
                    formData.append('companion_id', companionId);
                }

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Don't show alert here, page reload will show session message
                            companionModal.hide();
                            location.reload();
                        } else {
                            alert('Lỗi: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Even if JSON parse fails, the update likely succeeded
                        // Just reload the page to show updated data
                        location.reload();
                    });
            });
        }
    });
</script>

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

    .guest-item {
        padding: 16px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 3px solid #0d6efd;
    }

    .guest-header {
        display: flex;
        align-items: center;
    }

    .guest-details {
        font-size: 0.875rem;
    }

    .info-row {
        display: flex;
        align-items: center;
        font-size: 0.875rem;
    }

    .staff-item {
        padding: 12px;
        background: #f8f9fa;
        border-radius: 6px;
    }
</style>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>