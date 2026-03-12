<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

$isEdit = isset($vehicle);
$title = $isEdit ? 'Cập nhật thông tin xe' : 'Thêm xe vào chuyến đi';
$action = $isEdit ? 'update' : 'store';
$formAction = BASE_URL_ADMIN . '&action=tour_vehicles/' . $action;
?>

<main class="dashboard main-content">
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
                        <a href="<?= BASE_URL_ADMIN ?>&action=tour_vehicles&assignment_id=<?= $assignment_id ?>" class="breadcrumb-link">
                            <span>Quản lý xe</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current"><?= $title ?></span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-<?= $isEdit ? 'edit' : 'bus' ?> title-icon"></i>
                            <?= $title ?>
                        </h1>
                        <p class="page-subtitle">Nhập thông tin chi tiết về phương tiện và tài xế cho chuyến đi</p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=tour_vehicles&assignment_id=<?= $assignment_id ?>" class="btn btn-modern btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        Hủy bỏ
                    </a>
                    <button type="submit" form="vehicle-form" class="btn btn-modern btn-primary">
                        <i class="fas fa-save me-2"></i>
                        <?= $isEdit ? 'Cập nhật' : 'Lưu thông tin' ?>
                    </button>
                </div>
            </div>
        </header>

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

        <form action="<?= $formAction ?>" method="POST" id="vehicle-form" class="needs-validation" novalidate>
            <input type="hidden" name="tour_assignment_id" value="<?= $assignment_id ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $vehicle['id'] ?>">
            <?php endif; ?>

            <div class="row">
                <!-- Vehicle Info -->
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0 text-primary"><i class="fas fa-truck-moving me-2"></i>Thông tin phương tiện</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Nhà xe</label>
                                    <select name="bus_company_id" id="bus_company_select" class="form-select">
                                        <option value="">-- Chọn nhà xe / Tự do --</option>
                                        <?php if (!empty($busCompanies)): ?>
                                            <?php foreach ($busCompanies as $company): ?>
                                                <option value="<?= $company['id'] ?>" <?= ($vehicle['bus_company_id'] ?? '') == $company['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($company['company_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>

                                    <!-- History Suggestion Container -->
                                    <div id="vehicle_history_container" class="mt-2 d-none bg-light p-2 rounded border border-dashed">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fas fa-history text-muted me-1 small"></i>
                                            <label class="form-label text-muted small fst-italic mb-0">Gợi ý từ lịch sử:</label>
                                        </div>
                                        <select id="vehicle_history_select" class="form-select form-select-sm">
                                            <option value="">-- Chọn xe đã từng dùng --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Biển số xe <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="vehicle_plate" id="vehicle_plate" required value="<?= htmlspecialchars($vehicle['vehicle_plate'] ?? '') ?>" placeholder="VD: 29B-123.45">
                                    <div class="invalid-feedback">Vui lòng nhập biển số xe</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Loại xe</label>
                                    <input type="text" class="form-control" name="vehicle_type" id="vehicle_type" value="<?= htmlspecialchars($vehicle['vehicle_type'] ?? '') ?>" placeholder="VD: 45 chỗ, Limousine...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Hãng xe / Hiệu xe</label>
                                    <input type="text" class="form-control" name="vehicle_brand" id="vehicle_brand" value="<?= htmlspecialchars($vehicle['vehicle_brand'] ?? '') ?>" placeholder="VD: Hyundai Universe">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0 text-success"><i class="fas fa-user-tie me-2"></i>Thông tin tài xế</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Tên tài xế</label>
                                    <input type="text" class="form-control" name="driver_name" id="driver_name" value="<?= htmlspecialchars($vehicle['driver_name'] ?? '') ?>" placeholder="Họ và tên">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Số điện thoại</label>
                                    <input type="tel" class="form-control" name="driver_phone" id="driver_phone" value="<?= htmlspecialchars($vehicle['driver_phone'] ?? '') ?>" placeholder="SĐT liên hệ">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Giấy phép lái xe</label>
                                    <input type="text" class="form-control" name="driver_license" id="driver_license" value="<?= htmlspecialchars($vehicle['driver_license'] ?? '') ?>" placeholder="Hạng E, FC...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar / Status -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0 text-info"><i class="fas fa-info-circle me-2"></i>Trạng thái & Ghi chú</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-medium">Trạng thái phân công</label>
                                <select name="status" class="form-select">
                                    <option value="assigned" <?= ($vehicle['status'] ?? '') == 'assigned' ? 'selected' : '' ?>>Đã phân công</option>
                                    <option value="confirmed" <?= ($vehicle['status'] ?? '') == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                    <option value="completed" <?= ($vehicle['status'] ?? '') == 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                                    <option value="cancelled" <?= ($vehicle['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>Hủy bỏ</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Ghi chú</label>
                                <textarea class="form-control" name="notes" rows="4" placeholder="Ghi chú thêm về xe, tài xế hoặc yêu cầu đặc biệt..."><?= htmlspecialchars($vehicle['notes'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    // Include the history suggestion script here as it was in the original file
    document.addEventListener('DOMContentLoaded', function() {
        const busCompanySelect = document.getElementById('bus_company_select');
        const historyContainer = document.getElementById('vehicle_history_container');
        const historySelect = document.getElementById('vehicle_history_select');

        // Input fields to autofill
        const inputFields = {
            vehicle_plate: document.getElementById('vehicle_plate'),
            vehicle_type: document.getElementById('vehicle_type'),
            vehicle_brand: document.getElementById('vehicle_brand'),
            driver_name: document.getElementById('driver_name'),
            driver_phone: document.getElementById('driver_phone'),
            driver_license: document.getElementById('driver_license')
        };

        busCompanySelect.addEventListener('change', function() {
            const companyId = this.value;
            if (!companyId) {
                historyContainer.classList.add('d-none');
                return;
            }

            // Fetch history via AJAX
            fetch('<?= BASE_URL_ADMIN ?>&action=tour_vehicles/get-history&bus_company_id=' + companyId)
                .then(response => response.json())
                .then(data => {
                    historySelect.innerHTML = '<option value="">-- Chọn xe đã từng dùng --</option>';

                    if (data && data.length > 0) {
                        data.forEach(item => {
                            const option = document.createElement('option');
                            // Store data in dataset for easy retrieval
                            option.dataset.plate = item.vehicle_plate;
                            option.dataset.type = item.vehicle_type || '';
                            option.dataset.brand = item.vehicle_brand || '';
                            option.dataset.driverName = item.driver_name || '';
                            option.dataset.driverPhone = item.driver_phone || '';
                            option.dataset.driverLicense = item.driver_license || '';

                            option.textContent = `${item.vehicle_plate} - ${item.vehicle_type} (${item.driver_name || 'No driver'})`;
                            historySelect.appendChild(option);
                        });
                        historyContainer.classList.remove('d-none');
                    } else {
                        historyContainer.classList.add('d-none');
                    }
                })
                .catch(err => console.error('Error fetching vehicle history:', err));
        });

        // Auto-fill when history item selected
        historySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption.value && this.selectedIndex === 0) return;

            // Start filling
            if (selectedOption.dataset.plate) inputFields.vehicle_plate.value = selectedOption.dataset.plate;
            if (selectedOption.dataset.type) inputFields.vehicle_type.value = selectedOption.dataset.type;
            if (selectedOption.dataset.brand) inputFields.vehicle_brand.value = selectedOption.dataset.brand;
            if (selectedOption.dataset.driverName) inputFields.driver_name.value = selectedOption.dataset.driverName;
            if (selectedOption.dataset.driverPhone) inputFields.driver_phone.value = selectedOption.dataset.driverPhone;
            if (selectedOption.dataset.driverLicense) inputFields.driver_license.value = selectedOption.dataset.driverLicense;
        });
    });
</script>

<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>