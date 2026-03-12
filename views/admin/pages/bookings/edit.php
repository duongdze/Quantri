<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>

<main class="dashboard booking-edit-page">
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
                        <span class="breadcrumb-current">Chỉnh sửa #<?= $booking['id'] ?></span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-edit title-icon"></i>
                            Chỉnh sửa Booking #<?= $booking['id'] ?>
                        </h1>
                        <p class="page-subtitle">Cập nhật thông tin đơn đặt tour</p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=bookings" class="btn btn-modern btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        Hủy bỏ
                    </a>
                    <a href="<?= BASE_URL_ADMIN ?>&action=bookings/detail&id=<?= $booking['id'] ?>" class="btn btn-modern btn-info">
                        <i class="fas fa-eye me-2"></i>
                        Xem chi tiết
                    </a>
                    <button type="submit" form="booking-edit-form" class="btn btn-modern btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Lưu thay đổi
                    </button>
                </div>
            </div>
        </header>

        <!-- Alert Messages -->
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

        <!-- Progress Steps -->
        <div class="progress-steps-wrapper mb-4">
            <div class="progress-steps">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Thông tin booking</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Phân công nhân sự</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Xác nhận</div>
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <form method="POST" action="<?= BASE_URL_ADMIN ?>&action=bookings/update" id="booking-edit-form">
            <input type="hidden" name="id" value="<?= $booking['id'] ?>">

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Step 1: Booking Information -->
                    <div class="form-step active" id="step-1">
                        <!-- Customer Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user text-primary me-2"></i>
                                    Thông tin khách hàng
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="customer_id" class="form-label">Khách hàng <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select class="form-select" id="customer_id" name="customer_id" required>
                                                <option value="">-- Chọn khách hàng --</option>
                                                <?php if (!empty($customers)): ?>
                                                    <?php foreach ($customers as $c): ?>
                                                        <option value="<?= htmlspecialchars($c['user_id']) ?>"
                                                            <?= $c['user_id'] == $booking['customer_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($c['full_name']) ?> (<?= htmlspecialchars($c['email']) ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <a href="<?= BASE_URL_ADMIN ?>&action=users/create" target="_blank" class="btn btn-outline-primary" title="Tạo khách hàng mới">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>Chọn khách hàng từ danh sách hoặc click nút "+" để tạo mới
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tour Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-route text-success me-2"></i>
                                    Thông tin tour
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <select class="form-select" id="tour_id" name="tour_id" required>
                                                <option value="">-- Chọn tour --</option>
                                                <?php if (!empty($tours)): ?>
                                                    <?php foreach ($tours as $t): ?>
                                                        <option value="<?= htmlspecialchars($t['id']) ?>"
                                                            data-price="<?= htmlspecialchars($t['base_price']) ?>"
                                                            <?= $t['id'] == $booking['tour_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($t['name']) ?> - <?= number_format($t['base_price'], 0, ',', '.') ?> ₫
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <label for="tour_id">Tour <span class="text-danger">*</span></label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-floating">
                                            <select class="form-select" id="version_id" name="version_id">
                                                <option value="">-- Chọn phiên bản --</option>
                                                <?php if (!empty($versions)): ?>
                                                    <?php foreach ($versions as $v): ?>
                                                        <option value="<?= htmlspecialchars($v['id']) ?>"
                                                            data-price-adult="<?= htmlspecialchars($v['price_adult'] ?? 0) ?>"
                                                            data-price-child="<?= htmlspecialchars($v['price_child'] ?? 0) ?>"
                                                            data-price-infant="<?= htmlspecialchars($v['price_infant'] ?? 0) ?>"
                                                            <?= ($v['id'] == ($booking['version_id'] ?? '')) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($v['name']) ?>
                                                            <?php if (!empty($v['description'])): ?>
                                                                - <?= htmlspecialchars($v['description']) ?>
                                                            <?php endif; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <label for="version_id">Phiên bản Tour</label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Chọn phiên bản để áp dụng giá theo mùa/sự kiện (tùy chọn)
                                        </small>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select" id="departure_id" name="departure_id" required>
                                                <option value="">-- Chọn ngày khởi hành --</option>
                                            </select>
                                            <label for="departure_id">Ngày khởi hành <span class="text-danger">*</span></label>
                                        </div>
                                        <small class="text-muted d-block mt-2" id="departure-info"></small>
                                        <input type="hidden" id="booking_date" name="booking_date" value="<?= date('Y-m-d', strtotime($booking['booking_date'])) ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" id="total_price" name="total_price"
                                                value="<?= $booking['total_price'] ?>" min="0" step="50000" placeholder=" " required>
                                            <label for="total_price">Tổng giá tiền (VNĐ) <span class="text-danger">*</span></label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="">-- Chọn trạng thái --</option>
                                                <option value="cho_xac_nhan" <?= $booking['status'] == 'cho_xac_nhan' ? 'selected' : '' ?>>Chờ xác nhận</option>
                                                <option value="da_coc" <?= $booking['status'] == 'da_coc' ? 'selected' : '' ?>>Đã cọc</option>
                                                <option value="hoan_tat" <?= $booking['status'] == 'hoan_tat' ? 'selected' : '' ?>>Hoàn tất</option>
                                                <option value="da_huy" <?= $booking['status'] == 'da_huy' ? 'selected' : '' ?>>Đã hủy</option>
                                            </select>
                                            <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Supplier Management -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-building text-info me-2"></i>
                                    Quản lý Nhà cung cấp
                                </h5>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addSupplierRow()">
                                    <i class="fas fa-plus me-1"></i>
                                    Thêm supplier
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="suppliers-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="20%">Loại dịch vụ</th>
                                                <th width="30%">Nhà cung cấp</th>
                                                <th width="10%">Số lượng</th>
                                                <th width="15%">Giá (VNĐ)</th>
                                                <th width="20%">Ghi chú</th>
                                                <th width="5%">Xóa</th>
                                            </tr>
                                        </thead>
                                        <tbody id="suppliers-tbody">
                                            <?php if (!empty($bookingSuppliers)): ?>
                                                <?php foreach ($bookingSuppliers as $index => $bs): ?>
                                                    <tr>
                                                        <td>
                                                            <select name="suppliers[<?= $index ?>][service_type]" class="form-select form-select-sm" required>
                                                                <option value="tour_operator" <?= $bs['service_type'] == 'tour_operator' ? 'selected' : '' ?>>Tour Operator</option>
                                                                <option value="hotel" <?= $bs['service_type'] == 'hotel' ? 'selected' : '' ?>>Khách sạn</option>
                                                                <option value="transport" <?= $bs['service_type'] == 'transport' ? 'selected' : '' ?>>Vận chuyển</option>
                                                                <option value="restaurant" <?= $bs['service_type'] == 'restaurant' ? 'selected' : '' ?>>Nhà hàng</option>
                                                                <option value="guide" <?= $bs['service_type'] == 'guide' ? 'selected' : '' ?>>Hướng dẫn viên</option>
                                                                <option value="other" <?= $bs['service_type'] == 'other' ? 'selected' : '' ?>>Khác</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select name="suppliers[<?= $index ?>][supplier_id]" class="form-select form-select-sm" required>
                                                                <?php foreach ($suppliers as $s): ?>
                                                                    <option value="<?= $s['id'] ?>" <?= $bs['supplier_id'] == $s['id'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['type'] ?? '') ?>)
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="suppliers[<?= $index ?>][quantity]"
                                                                value="<?= $bs['quantity'] ?>" class="form-control form-control-sm" min="1" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="suppliers[<?= $index ?>][price]"
                                                                value="<?= $bs['price'] ?>" class="form-control form-control-sm" min="0" step="10000">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="suppliers[<?= $index ?>][notes]"
                                                                value="<?= htmlspecialchars($bs['notes'] ?? '') ?>" class="form-control form-control-sm" placeholder="Ghi chú...">
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeSupplierRow(this)" title="Xóa">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (empty($bookingSuppliers)): ?>
                                    <div class="text-center text-muted py-3" id="empty-suppliers-message">
                                        <i class="fas fa-building fa-2x mb-2"></i>
                                        <p>Chưa có nhà cung cấp nào. Click "Thêm supplier" để thêm.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-comment text-warning me-2"></i>
                                    Ghi chú
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-floating">
                                    <textarea class="form-control" id="notes" name="notes" style="height: 120px" placeholder=" "><?= htmlspecialchars($booking['notes'] ?? '') ?></textarea>
                                    <label for="notes">Ghi chú thêm về đơn đặt</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Staff Assignment -->
                    <div class="form-step" id="step-2">
                        <!-- Bus Company Assignment -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bus text-primary me-2"></i>
                                    Phân công Nhà xe
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-floating">
                                    <select class="form-select" id="bus_company_id" name="bus_company_id">
                                        <option value="">-- Chưa phân công --</option>
                                        <?php if (!empty($busCompanies)): ?>
                                            <?php foreach ($busCompanies as $company): ?>
                                                <option value="<?= $company['id'] ?>"
                                                    <?= ($company['id'] == ($booking['bus_company_id'] ?? '')) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($company['company_name']) ?>
                                                    (<?= htmlspecialchars($company['phone']) ?> - <?= number_format($company['total_vehicles']) ?> xe)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <label for="bus_company_id">Nhà xe</label>
                                </div>
                                <small class="text-muted d-block mt-2">Có thể để trống nếu chưa phân công</small>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Confirmation -->
                    <div class="form-step" id="step-3">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Xác nhận thông tin
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Vui lòng kiểm tra lại thông tin trước khi lưu thay đổi
                                </div>

                                <div class="booking-summary">
                                    <h6 class="mb-3">Thông tin booking</h6>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Booking ID:</strong></div>
                                        <div class="col-8">#<?= $booking['id'] ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Khách hàng:</strong></div>
                                        <div class="col-8" id="summary-customer">--</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Tour:</strong></div>
                                        <div class="col-8" id="summary-tour">--</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Ngày đặt:</strong></div>
                                        <div class="col-8" id="summary-date">--</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Trạng thái:</strong></div>
                                        <div class="col-8" id="summary-status">--</div>
                                    </div>

                                    <hr>
                                    <h6 class="mb-3">Phân công nhà xe</h6>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Nhà xe:</strong></div>
                                        <div class="col-8" id="summary-bus-company">Chưa phân công</div>
                                    </div>

                                    <hr>
                                    <div class="row">
                                        <div class="col-4"><strong>Tổng giá:</strong></div>
                                        <div class="col-8">
                                            <span class="text-danger fw-bold fs-5" id="summary-price">0 ₫</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Form Actions -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thao tác</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" form="booking-edit-form" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Lưu thay đổi
                                </button>
                                <a href="<?= BASE_URL_ADMIN ?>&action=bookings/detail&id=<?= $booking['id'] ?>" class="btn btn-info">
                                    <i class="fas fa-eye me-2"></i>
                                    Xem chi tiết
                                </a>
                                <a href="<?= BASE_URL_ADMIN ?>&action=bookings" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Hủy bỏ
                                </a>
                            </div>

                            <hr class="my-3">

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="previousStep()" id="prev-btn" style="display: none;">
                                    <i class="fas fa-chevron-left me-1"></i>
                                    Quay lại
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" onclick="nextStep()" id="next-btn">
                                    Tiếp theo
                                    <i class="fas fa-chevron-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Summary -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-receipt me-2"></i>
                                Tóm tắt
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Booking ID:</span>
                                <span>#<?= $booking['id'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tour:</span>
                                <span id="quick-tour">--</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Khách hàng:</span>
                                <span id="quick-customer">--</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Tổng:</strong>
                                <strong class="text-danger" id="quick-price">0 ₫</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    // Booking Edit JavaScript
    let currentStep = 1;
    const totalSteps = 3;

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        initializeForm();
        setupEventListeners();
        updateSummary(); // Initial summary update
    });

    function initializeForm() {
        updateStepDisplay();
        updateNavigationButtons();

        // Auto-select "Bình thường" version (ID=10) if no version is selected
        const versionSelect = document.getElementById('version_id');
        if (versionSelect && !versionSelect.value) {
            // Try to find and select version with ID=10
            const defaultOption = versionSelect.querySelector('option[value="10"]');
            if (defaultOption) {
                versionSelect.value = '10';
                // Trigger change event to update price
                versionSelect.dispatchEvent(new Event('change'));
            }
        }
    }

    function setupEventListeners() {
        // Auto-update price when tour is selected
        document.getElementById('tour_id').addEventListener('change', function() {
            const tourId = this.value;
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price');

            // Reset departure selection
            const departureSelect = document.getElementById('departure_id');
            departureSelect.innerHTML = '<option value="">-- Chọn ngày khởi hành --</option>';
            document.getElementById('departure-info').textContent = '';
            document.getElementById('booking_date').value = '';

            if (tourId) {
                // Fetch departures for this tour
                fetchDepartures(tourId);

                // Reset version selection when tour changes
                const versionSelect = document.getElementById('version_id');
                if (versionSelect) {
                    versionSelect.value = '';
                }
                document.getElementById('total_price').value = price;
                updateSummary();
            }
        });

        // Handle departure selection
        document.getElementById('departure_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const departureDate = selectedOption.getAttribute('data-date');
                const availableSeats = selectedOption.getAttribute('data-seats');
                const maxSeats = selectedOption.getAttribute('data-max-seats');

                // Update hidden booking_date field
                document.getElementById('booking_date').value = departureDate;

                // Show departure info
                document.getElementById('departure-info').innerHTML =
                    `<i class="fas fa-info-circle me-1"></i>Còn ${availableSeats}/${maxSeats} chỗ trống`;

                updateSummary();
            } else {
                document.getElementById('booking_date').value = '';
                document.getElementById('departure-info').textContent = '';
            }
        });

        // Auto-update price when version is selected
        const versionSelect = document.getElementById('version_id');
        if (versionSelect) {
            versionSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const priceAdult = selectedOption.getAttribute('data-price-adult');

                if (priceAdult && priceAdult > 0) {
                    // Nếu có chọn version → dùng giá của version
                    document.getElementById('total_price').value = priceAdult;
                } else {
                    // Nếu không chọn version → quay về giá gốc của tour
                    const tourSelect = document.getElementById('tour_id');
                    const tourPrice = tourSelect.options[tourSelect.selectedIndex].getAttribute('data-price');
                    if (tourPrice) {
                        document.getElementById('total_price').value = tourPrice;
                    }
                }
                updateSummary();
            });
        }

        // Update summary on field changes
        ['customer_id', 'tour_id', 'version_id', 'departure_id', 'status', 'total_price', 'bus_company_id'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('change', updateSummary);
            }
        });

        // Load departures for the current tour on page load
        const tourId = document.getElementById('tour_id').value;
        if (tourId) {
            fetchDepartures(tourId, '<?= $booking['booking_date'] ?? '' ?>');
        }
    }

    // Fetch departures for a tour
    function fetchDepartures(tourId, selectedDate = null) {
        const departureSelect = document.getElementById('departure_id');
        const infoDiv = document.getElementById('departure-info');

        // Show loading
        infoDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang tải lịch khởi hành...';

        fetch(`<?= BASE_URL_ADMIN ?>&action=bookings/get-departures&tour_id=${tourId}`)
            .then(response => response.json())
            .then(data => {
                infoDiv.textContent = '';

                if (data.success && data.departures && data.departures.length > 0) {
                    data.departures.forEach(dep => {
                        const option = document.createElement('option');
                        option.value = dep.id;
                        option.textContent = dep.formatted_date;
                        option.setAttribute('data-date', dep.departure_date);
                        option.setAttribute('data-price-adult', dep.price_adult);
                        option.setAttribute('data-price-child', dep.price_child);
                        option.setAttribute('data-seats', dep.available_seats);
                        option.setAttribute('data-max-seats', dep.max_seats);

                        if (dep.version_name) {
                            option.textContent += ` (${dep.version_name})`;
                        }

                        // Select the current booking's date if it matches
                        if (selectedDate && dep.departure_date === selectedDate) {
                            option.selected = true;
                            // Trigger change to update info
                            setTimeout(() => {
                                departureSelect.dispatchEvent(new Event('change'));
                            }, 100);
                        }

                        departureSelect.appendChild(option);
                    });

                    infoDiv.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i>Tìm thấy ${data.departures.length} lịch khởi hành`;
                } else {
                    infoDiv.innerHTML = '<i class="fas fa-exclamation-triangle text-warning me-1"></i>Không có lịch khởi hành nào';
                }
            })
            .catch(error => {
                console.error('Error fetching departures:', error);
                infoDiv.innerHTML = '<i class="fas fa-exclamation-circle text-danger me-1"></i>Lỗi khi tải lịch khởi hành';
            });
    }

    // Step Navigation
    function nextStep() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepDisplay();
                updateNavigationButtons();
                updateSummary();
            }
        }
    }

    function previousStep() {
        if (currentStep > 1) {
            currentStep--;
            updateStepDisplay();
            updateNavigationButtons();
        }
    }

    function updateStepDisplay() {
        // Update progress steps
        document.querySelectorAll('.step').forEach(step => {
            step.classList.remove('active', 'completed');
            const stepNum = parseInt(step.dataset.step);
            if (stepNum === currentStep) {
                step.classList.add('active');
            } else if (stepNum < currentStep) {
                step.classList.add('completed');
            }
        });

        // Update form sections
        document.querySelectorAll('.form-step').forEach(step => {
            step.classList.remove('active');
        });
        document.getElementById(`step-${currentStep}`).classList.add('active');
    }

    function updateNavigationButtons() {
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');

        prevBtn.style.display = currentStep === 1 ? 'none' : 'block';
        nextBtn.style.display = currentStep === totalSteps ? 'none' : 'block';
    }

    // Validation
    function validateCurrentStep() {
        const currentStepElement = document.getElementById(`step-${currentStep}`);
        const requiredFields = currentStepElement.querySelectorAll('[required]');

        for (let field of requiredFields) {
            if (!field.value.trim()) {
                field.focus();
                alert('Vui lòng điền đầy đủ thông tin bắt buộc');
                return false;
            }
        }

        return true;
    }

    // Update Summary
    function updateSummary() {
        const customerSelect = document.getElementById('customer_id');
        const tourSelect = document.getElementById('tour_id');
        const dateInput = document.getElementById('booking_date');
        const statusSelect = document.getElementById('status');
        const priceInput = document.getElementById('total_price');
        const busCompanySelect = document.getElementById('bus_company_id');

        // Update quick summary
        const customerText = customerSelect.options[customerSelect.selectedIndex]?.text || '--';
        const tourText = tourSelect.options[tourSelect.selectedIndex]?.text || '--';
        const busCompanyText = busCompanySelect?.options[busCompanySelect.selectedIndex]?.text || 'Chưa phân công';
        const price = priceInput.value || '0';

        document.getElementById('quick-customer').textContent = customerText.split('(')[0].trim();
        document.getElementById('quick-tour').textContent = tourText.split('-')[0].trim();
        document.getElementById('quick-price').textContent = new Intl.NumberFormat('vi-VN').format(price) + ' ₫';

        // Update confirmation summary
        document.getElementById('summary-customer').textContent = customerText;
        document.getElementById('summary-tour').textContent = tourText;
        document.getElementById('summary-date').textContent = dateInput.value || '--';
        document.getElementById('summary-status').textContent = statusSelect.options[statusSelect.selectedIndex]?.text || '--';
        if (document.getElementById('summary-bus-company')) {
            document.getElementById('summary-bus-company').textContent = busCompanyText;
        }
        document.getElementById('summary-price').textContent = new Intl.NumberFormat('vi-VN').format(price) + ' ₫';
    }

    // Supplier Management Functions
    let supplierIndex = <?= count($bookingSuppliers ?? []) ?>;

    function addSupplierRow() {
        const tbody = document.getElementById('suppliers-tbody');
        const emptyMessage = document.getElementById('empty-suppliers-message');

        if (emptyMessage) {
            emptyMessage.remove();
        }

        const row = `
            <tr>
                <td>
                    <select name="suppliers[${supplierIndex}][service_type]" class="form-select form-select-sm" required>
                        <option value="tour_operator">Tour Operator</option>
                        <option value="hotel">Khách sạn</option>
                        <option value="transport">Vận chuyển</option>
                        <option value="restaurant">Nhà hàng</option>
                        <option value="guide">Hướng dẫn viên</option>
                        <option value="other">Khác</option>
                    </select>
                </td>
                <td>
                    <select name="suppliers[${supplierIndex}][supplier_id]" class="form-select form-select-sm" required>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['type'] ?? '') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input type="number" name="suppliers[${supplierIndex}][quantity]" value="1" class="form-control form-control-sm" min="1" required>
                </td>
                <td>
                    <input type="number" name="suppliers[${supplierIndex}][price]" value="0" class="form-control form-control-sm" min="0" step="10000">
                </td>
                <td>
                    <input type="text" name="suppliers[${supplierIndex}][notes]" class="form-control form-control-sm" placeholder="Ghi chú...">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeSupplierRow(this)" title="Xóa">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        tbody.insertAdjacentHTML('beforeend', row);
        supplierIndex++;
    }

    function removeSupplierRow(btn) {
        const row = btn.closest('tr');
        row.remove();

        // Show empty message if no rows left
        const tbody = document.getElementById('suppliers-tbody');
        if (tbody.children.length === 0) {
            const tableContainer = tbody.closest('.table-responsive');
            tableContainer.insertAdjacentHTML('afterend', `
                <div class="text-center text-muted py-3" id="empty-suppliers-message">
                    <i class="fas fa-building fa-2x mb-2"></i>
                    <p>Chưa có nhà cung cấp nào. Click "Thêm supplier" để thêm.</p>
                </div>
            `);
        }
    }

    // ========== Passenger Type Pricing ==========
    // Hidden inputs for prices
    const priceAdult = <?= $versionPrices['price_adult'] ?? 0 ?>;
    const priceChild = <?= $versionPrices['price_child'] ?? 0 ?>;
    const priceInfant = <?= $versionPrices['price_infant'] ?? 0 ?>;
</script>

<!-- Include Passenger Pricing Module -->
<script src="<?= BASE_URL ?>assets/admin/js/passenger-pricing.js"></script>
<script>
    // Initialize passenger pricing when document is ready
    $(document).ready(function() {
        if (typeof PassengerPricing !== 'undefined') {
            PassengerPricing.init(priceAdult, priceChild, priceInfant);
        }
    });
</script>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>