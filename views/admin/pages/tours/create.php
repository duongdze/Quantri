<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

// Load data for form
$categories = $categories ?? [];
$policies = $policies ?? [];
?>
<main class="dashboard tour-create-page">
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
                        <a href="<?= BASE_URL_ADMIN ?>&action=tours" class="breadcrumb-link">
                            <i class="fas fa-route"></i>
                            <span>Quản lý Tour</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current">Tạo Tour Mới</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-plus-circle title-icon"></i>
                            Tạo Tour Mới
                        </h1>
                        <p class="page-subtitle">Điền thông tin chi tiết để tạo tour du lịch mới</p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=tours" class="btn btn-modern btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        Hủy bỏ
                    </a>
                    <button type="submit" form="tour-form" class="btn btn-modern btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Lưu Tour
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

        <!-- Progress Steps -->
        <div class="progress-steps-wrapper mb-4">
            <div class="progress-steps">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Thông tin cơ bản</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Hình ảnh</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Lịch trình</div>
                </div>
                <div class="step" data-step="4">
                    <div class="step-number">4</div>
                    <div class="step-label">Khởi hành</div>
                </div>
                <div class="step" data-step="5">
                    <div class="step-number">5</div>
                    <div class="step-label">Hoàn tất</div>
                </div>
            </div>
        </div>

        <!-- Tour Form -->
        <form method="POST" action="<?= BASE_URL_ADMIN ?>&action=tours/store" enctype="multipart/form-data" id="tour-form">
            <!-- Hidden inputs for dynamic data -->
            <input type="hidden" name="tour_itinerary" id="tour_itinerary" value="[]">
            <input type="hidden" name="tour_pricing_options" id="tour_pricing_options" value="[]">
            <input type="hidden" name="tour_departures" id="tour_departures" value="[]">
            <input type="hidden" name="tour_partners" id="tour_partners" value="[]">

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Step 1: Basic Information -->
                    <div class="form-step active" id="step-1">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle text-primary me-2"></i>
                                    Thông tin cơ bản
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" name="name" id="tour-name" class="form-control" required placeholder=" ">
                                            <label for="tour-name">Tên Tour <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select name="category_id" id="category_id" class="form-select" required>
                                                <option value="">-- Chọn danh mục --</option>
                                                <?php if (!empty($categories)): ?>
                                                    <?php foreach ($categories as $cat): ?>
                                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <label for="category_id">Danh mục <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select name="supplier_id" id="supplier_id" class="form-select">
                                                <option value="">-- Không chọn --</option>
                                                <?php if (!empty($suppliers)): ?>
                                                    <?php foreach ($suppliers as $supplier): ?>
                                                        <option value="<?= $supplier['id'] ?>">
                                                            <?= htmlspecialchars($supplier['name']) ?> - <?= htmlspecialchars($supplier['type'] ?? '') ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <label for="supplier_id">Nhà cung cấp (tùy chọn)</label>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-info-circle me-1"></i>Chọn nhà cung cấp chính cho tour này
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="number" name="base_price" id="base_price" class="form-control" required min="0" step="1000" placeholder=" ">
                                            <label for="base_price">Giá cơ bản (VNĐ) <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea name="description" id="description" class="form-control" style="height: 150px" placeholder=" "></textarea>
                                            <label for="description">Mô tả chi tiết</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Images -->
                    <div class="form-step" id="step-2">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-images text-success me-2"></i>
                                    Hình ảnh Tour
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Main Image -->
                                <div class="mb-4">
                                    <h6 class="mb-3">Ảnh đại diện</h6>
                                    <div class="main-image-upload">
                                        <div class="upload-area" id="mainImageDropZone">
                                            <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-primary"></i>
                                            <p class="mb-1">Kéo thả hoặc click để chọn ảnh đại diện</p>
                                            <span class="text-muted small">JPG, PNG, WEBP. Tối đa 5MB</span>
                                            <input type="file" name="main_image" id="main_image" accept="image/*">
                                        </div>
                                        <div class="main-image-preview" id="mainImagePreview" style="display: none;">
                                            <img src="" alt="Main Image Preview" class="img-fluid rounded">
                                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeMainImage()">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Gallery Images -->
                                <div>
                                    <h6 class="mb-3">Thư viện ảnh</h6>
                                    <div class="gallery-upload-zone">
                                        <div class="upload-area" id="galleryDropZone" onclick="document.getElementById('gallery_images').click()">
                                            <i class="fas fa-images fa-3x mb-3 text-primary"></i>
                                            <p class="mb-1">Kéo thả hoặc click để chọn nhiều ảnh</p>
                                            <span class="text-muted small">JPG, PNG, WEBP. Tối đa 5MB mỗi ảnh</span>
                                            <input type="file" name="gallery_images[]" id="gallery_images" multiple accept="image/*">
                                        </div>
                                        <div class="gallery-preview-grid" id="galleryPreview">
                                            <!-- Gallery previews will appear here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Itinerary -->
                    <div class="form-step" id="step-3">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-map-marked-alt text-warning me-2"></i>
                                    Lịch trình Tour
                                </h5>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addItineraryDay()">
                                    <i class="fas fa-plus me-1"></i>
                                    Thêm ngày
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="itinerary-list" class="itinerary-list">
                                    <!-- Itinerary items will be added here dynamically -->
                                </div>
                                <div class="text-center text-muted py-4" id="itinerary-empty">
                                    <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                                    <p>Chưa có lịch trình nào</p>
                                    <button type="button" class="btn btn-outline-primary" onclick="addItineraryDay()">
                                        <i class="fas fa-plus me-2"></i>
                                        Thêm ngày đầu tiên
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Pricing & Departures -->
                    <div class="form-step" id="step-4">

                        <!-- Tour Departures -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-alt text-danger me-2"></i>
                                    Lịch khởi hành
                                </h5>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addDeparture()">
                                    <i class="fas fa-plus me-1"></i>
                                    Thêm lịch
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="departures-list" class="departures-list">
                                    <!-- Departures will be added here dynamically -->
                                </div>
                                <div class="text-center text-muted py-4" id="departures-empty">
                                    <i class="fas fa-calendar-plus fa-3x mb-3"></i>
                                    <p>Chưa có lịch khởi hành nào</p>
                                    <button type="button" class="btn btn-outline-primary" onclick="addDeparture()">
                                        <i class="fas fa-plus me-2"></i>
                                        Thêm lịch đầu tiên
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Final Details -->
                    <div class="form-step" id="step-5">
                        <!-- Partners -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-handshake text-secondary me-2"></i>
                                    Đối tác dịch vụ
                                </h5>
                                <div class="d-flex gap-2">
                                    <select class="form-select form-select-sm" id="supplier-select" style="width: 250px;">
                                        <option value="">-- Chọn nhà cung cấp --</option>
                                        <?php if (!empty($suppliers)): ?>
                                            <?php foreach ($suppliers as $supplier): ?>
                                                <option value="<?= $supplier['id'] ?>"
                                                    data-name="<?= htmlspecialchars($supplier['name']) ?>"
                                                    data-type="<?= $supplier['type'] ?? '' ?>"
                                                    data-contact="<?= htmlspecialchars($supplier['phone'] ?? $supplier['email'] ?? '') ?>">
                                                    <?= htmlspecialchars($supplier['name']) ?> (<?= strtoupper($supplier['type'] ?? '') ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="addPartnerFromSupplier()">
                                        <i class="fas fa-plus me-1"></i>
                                        Thêm
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Supplier Selection -->
                                <div class="mb-4 p-3 bg-light rounded">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-building me-2 text-primary"></i>
                                                Chọn Nhà cung cấp
                                            </label>
                                            <select class="form-select" name="supplier_id" id="supplier_id">
                                                <option value="">-- Không chọn nhà cung cấp --</option>
                                                <?php if (!empty($suppliers)): ?>
                                                    <?php foreach ($suppliers as $supplier): ?>
                                                        <option value="<?= $supplier['id'] ?>">
                                                            <?= htmlspecialchars($supplier['name']) ?>
                                                            (<?= strtoupper($supplier['type'] ?? '') ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Chọn nhà cung cấp chính cho tour này (khách sạn, xe, nhà hàng...)
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Partner Services List -->
                                <div id="partners-list" class="partners-list">
                                    <!-- Partners will be added here dynamically -->
                                </div>
                                <div class="text-center text-muted py-4" id="partners-empty">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <p>Chưa có đối tác nào</p>
                                    <button type="button" class="btn btn-outline-primary" onclick="addPartner()">
                                        <i class="fas fa-plus me-2"></i>
                                        Thêm đối tác đầu tiên
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Policies -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-shield-alt text-primary me-2"></i>
                                    Chính sách áp dụng
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($policies)): ?>
                                    <div class="row g-3">
                                        <?php foreach ($policies as $policy): ?>
                                            <div class="col-md-6">
                                                <div class="form-check border rounded p-3">
                                                    <input class="form-check-input" type="checkbox" name="policies[]" value="<?= $policy['id'] ?>" id="policy_<?= $policy['id'] ?>">
                                                    <label class="form-check-label fw-medium" for="policy_<?= $policy['id'] ?>">
                                                        <?= htmlspecialchars($policy['name']) ?>
                                                    </label>
                                                    <div class="small text-muted mt-1"><?= htmlspecialchars($policy['description']) ?></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-shield-alt fa-3x mb-3"></i>
                                        <p>Chưa có chính sách nào</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Form Actions -->
                    <div class="card mb-4 sticky-top" style="top: 20px;">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thao tác</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" onclick="saveDraft()" id="save-draft-btn">
                                    <i class="fas fa-save me-2"></i>
                                    Lưu nháp
                                </button>
                                <button type="submit" form="tour-form" class="btn btn-primary">
                                    <i class="fas fa-check me-2"></i>
                                    Tạo Tour
                                </button>
                                <a href="<?= BASE_URL_ADMIN ?>&action=tours" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Hủy bỏ
                                </a>
                            </div>

                            <hr class="my-3">

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="previousStep()" id="prev-btn">
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

                    <!-- Auto-save Status -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="auto-save-indicator" id="autoSaveIndicator">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span class="ms-2">Đã lưu</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<!-- Templates -->
<template id="itinerary-template">
    <div class="itinerary-item border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h6 class="mb-0">Ngày <span class="day-number"></span></h6>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItineraryItem(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="text" class="form-control itinerary-title" placeholder=" " required>
                    <label>Tiêu đề</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="time" class="form-control itinerary-time-start" placeholder=" ">
                    <label>Bắt đầu</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="time" class="form-control itinerary-time-end" placeholder=" ">
                    <label>Kết thúc</label>
                </div>
            </div>
            <div class="col-12">
                <div class="form-floating">
                    <textarea class="form-control itinerary-description" style="height: 100px" placeholder=" "></textarea>
                    <label>Mô tả chi tiết</label>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="pricing-template">
    <div class="pricing-item border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h6 class="mb-0">Gói dịch vụ</h6>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePricingItem(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="text" class="form-control pricing-label" placeholder=" " required>
                    <label>Tên gói (VD: Người lớn, Trẻ em)</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="number" class="form-control pricing-price" min="0" step="1000" placeholder=" " required>
                    <label>Giá (VNĐ)</label>
                </div>
            </div>
            <div class="col-12">
                <div class="form-floating">
                    <textarea class="form-control pricing-description" style="height: 80px" placeholder=" "></textarea>
                    <label>Mô tả ngắn</label>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="departure-template">
    <div class="departure-item border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h6 class="mb-0">Lịch khởi hành</h6>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDepartureItem(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="date" class="form-control departure-date" required>
                    <label>Ngày khởi hành</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating">
                    <select class="form-select departure-status">
                        <option value="open">Mở</option>
                        <option value="full">Hết chỗ</option>
                        <option value="guaranteed">Đảm bảo</option>
                        <option value="closed">Đóng</option>
                    </select>
                    <label>Trạng thái</label>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="partner-template">
    <div class="partner-item border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h6 class="mb-0">Đối tác dịch vụ</h6>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePartnerItem(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-floating">
                    <select class="form-select partner-service-type" required>
                        <option value="">-- Loại dịch vụ --</option>
                        <option value="hotel">Khách sạn</option>
                        <option value="transport">Vận chuyển</option>
                        <option value="restaurant">Nhà hàng</option>
                        <option value="guide">Hướng dẫn viên</option>
                        <option value="other">Khác</option>
                    </select>
                    <label>Loại dịch vụ</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="text" class="form-control partner-name" placeholder=" " required>
                    <label>Tên đối tác</label>
                </div>
            </div>
            <div class="col-12">
                <div class="form-floating">
                    <input type="text" class="form-control partner-contact" placeholder=" " required>
                    <label>Thông tin liên hệ</label>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    // Tour Creation JavaScript
    let currentStep = 1;
    const totalSteps = 5;
    let itineraryCount = 0;
    let pricingCount = 0;
    let departureCount = 0;
    let partnerCount = 0;

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        initializeForm();
        setupImageUploads();
        setupAutoSave();
    });

    function initializeForm() {
        updateStepDisplay();
        updateNavigationButtons();
    }

    // Step Navigation
    function nextStep() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepDisplay();
                updateNavigationButtons();
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
                showToast('Vui lòng điền đầy đủ thông tin bắt buộc', 'error');
                return false;
            }
        }

        return true;
    }

    // Itinerary Management
    function addItineraryDay() {
        itineraryCount++;
        const template = document.getElementById('itinerary-template');
        const clone = template.content.cloneNode(true);

        clone.querySelector('.day-number').textContent = itineraryCount;

        const container = document.getElementById('itinerary-list');
        container.appendChild(clone);

        // Hide empty state
        document.getElementById('itinerary-empty').style.display = 'none';

        updateItineraryData();
    }

    function removeItineraryItem(button) {
        button.closest('.itinerary-item').remove();
        updateItineraryDayNumbers();
        updateItineraryData();
    }

    function updateItineraryDayNumbers() {
        const items = document.querySelectorAll('.itinerary-item');
        items.forEach((item, index) => {
            item.querySelector('.day-number').textContent = index + 1;
        });
        itineraryCount = items.length;

        if (itineraryCount === 0) {
            document.getElementById('itinerary-empty').style.display = 'block';
        }
    }

    function updateItineraryData() {
        const itineraries = [];
        document.querySelectorAll('.itinerary-item').forEach((item, index) => {
            itineraries.push({
                day_number: index + 1,
                day_label: `Ngày ${index + 1}`,
                title: item.querySelector('.itinerary-title').value,
                time_start: item.querySelector('.itinerary-time-start').value,
                time_end: item.querySelector('.itinerary-time-end').value,
                description: item.querySelector('.itinerary-description').value
            });
        });
        document.getElementById('tour_itinerary').value = JSON.stringify(itineraries);
    }

    // Pricing Management
    function addPricingOption() {
        pricingCount++;
        const template = document.getElementById('pricing-template');
        const clone = template.content.cloneNode(true);

        const container = document.getElementById('pricing-options-list');
        container.appendChild(clone);

        // Hide empty state
        document.getElementById('pricing-empty').style.display = 'none';

        updatePricingData();
    }

    function removePricingItem(button) {
        button.closest('.pricing-item').remove();
        pricingCount--;

        if (pricingCount === 0) {
            document.getElementById('pricing-empty').style.display = 'block';
        }

        updatePricingData();
    }

    function updatePricingData() {
        const pricingOptions = [];
        document.querySelectorAll('.pricing-item').forEach(item => {
            pricingOptions.push({
                label: item.querySelector('.pricing-label').value,
                price: item.querySelector('.pricing-price').value,
                description: item.querySelector('.pricing-description').value
            });
        });
        document.getElementById('tour_pricing_options').value = JSON.stringify(pricingOptions);
    }

    // Departure Management
    function addDeparture() {
        departureCount++;
        const template = document.getElementById('departure-template');
        const clone = template.content.cloneNode(true);

        const container = document.getElementById('departures-list');
        container.appendChild(clone);

        // Hide empty state
        document.getElementById('departures-empty').style.display = 'none';

        updateDepartureData();
    }

    function removeDepartureItem(button) {
        button.closest('.departure-item').remove();
        departureCount--;

        if (departureCount === 0) {
            document.getElementById('departures-empty').style.display = 'block';
        }

        updateDepartureData();
    }

    function updateDepartureData() {
        const departures = [];
        document.querySelectorAll('.departure-item').forEach(item => {
            departures.push({
                departure_date: item.querySelector('.departure-date').value,
                max_seats: item.querySelector('.departure-max-seats').value,
                price_adult: item.querySelector('.departure-price-adult').value,
                price_child: item.querySelector('.departure-price-child').value,
                status: item.querySelector('.departure-status').value
            });
        });
        document.getElementById('tour_departures').value = JSON.stringify(departures);
    }

    // Partner Management
    function addPartner() {
        partnerCount++;
        const template = document.getElementById('partner-template');
        const clone = template.content.cloneNode(true);

        const container = document.getElementById('partners-list');
        container.appendChild(clone);

        // Hide empty state
        document.getElementById('partners-empty').style.display = 'none';

        updatePartnerData();
    }

    function removePartnerItem(button) {
        button.closest('.partner-item').remove();
        partnerCount--;

        if (partnerCount === 0) {
            document.getElementById('partners-empty').style.display = 'block';
        }

        updatePartnerData();
    }

    function updatePartnerData() {
        const partners = [];
        document.querySelectorAll('.partner-item').forEach(item => {
            partners.push({
                service_type: item.querySelector('.partner-service-type').value,
                name: item.querySelector('.partner-name').value,
                contact: item.querySelector('.partner-contact').value
            });
        });
        document.getElementById('tour_partners').value = JSON.stringify(partners);
    }

    // Image Upload
    function setupImageUploads() {
        // Main image upload
        const mainImageInput = document.getElementById('main_image');
        const mainImageDropZone = document.getElementById('mainImageDropZone');

        mainImageDropZone.addEventListener('click', () => mainImageInput.click());

        mainImageDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            mainImageDropZone.classList.add('drag-over');
        });

        mainImageDropZone.addEventListener('dragleave', () => {
            mainImageDropZone.classList.remove('drag-over');
        });

        mainImageDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            mainImageDropZone.classList.remove('drag-over');
            handleMainImageUpload(e.dataTransfer.files[0]);
        });

        mainImageInput.addEventListener('change', (e) => {
            handleMainImageUpload(e.target.files[0]);
        });

        // Gallery upload
        const galleryInput = document.getElementById('gallery_images');
        const galleryDropZone = document.getElementById('galleryDropZone');

        galleryDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            galleryDropZone.classList.add('drag-over');
        });

        galleryDropZone.addEventListener('dragleave', () => {
            galleryDropZone.classList.remove('drag-over');
        });

        galleryDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            galleryDropZone.classList.remove('drag-over');
            handleGalleryUpload(e.dataTransfer.files);
        });

        galleryInput.addEventListener('change', (e) => {
            handleGalleryUpload(e.target.files);
        });
    }

    function handleMainImageUpload(file) {
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('mainImagePreview').querySelector('img').src = e.target.result;
                document.getElementById('mainImageDropZone').style.display = 'none';
                document.getElementById('mainImagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }

    function removeMainImage() {
        document.getElementById('main_image').value = '';
        document.getElementById('mainImageDropZone').style.display = 'block';
        document.getElementById('mainImagePreview').style.display = 'none';
    }

    function handleGalleryUpload(files) {
        const previewGrid = document.getElementById('galleryPreview');

        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('div');
                    preview.className = 'gallery-preview-item position-relative';
                    preview.innerHTML = `
                    <img src="${e.target.result}" alt="Gallery Preview" class="img-fluid rounded" style="width: 100px; height: 100px; object-fit: cover;">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                    previewGrid.appendChild(preview);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Auto-save
    function setupAutoSave() {
        const form = document.getElementById('tour-form');
        let saveTimeout;

        form.addEventListener('input', () => {
            clearTimeout(saveTimeout);
            const indicator = document.getElementById('autoSaveIndicator');
            indicator.innerHTML = '<i class="fas fa-spinner fa-spin text-primary"></i><span class="ms-2">Đang lưu...</span>';

            saveTimeout = setTimeout(() => {
                saveDraft();
            }, 2000);
        });
    }

    function saveDraft() {
        // Collect all form data
        const formData = new FormData(document.getElementById('tour-form'));
        const draftData = {};

        for (let [key, value] of formData.entries()) {
            draftData[key] = value;
        }

        // Save to localStorage
        localStorage.setItem('tour_draft', JSON.stringify(draftData));

        const indicator = document.getElementById('autoSaveIndicator');
        indicator.innerHTML = '<i class="fas fa-check-circle text-success"></i><span class="ms-2">Đã lưu nháp</span>';

        showToast('Đã lưu nháp thành công', 'success');
    }

    // Form submission
    document.getElementById('tour-form').addEventListener('submit', function(e) {
        // Update all data before submission
        updateItineraryData();
        updatePricingData();
        updateDepartureData();
        updatePartnerData();

        // Clear draft
        localStorage.removeItem('tour_draft');
    });

    // Toast notification
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        toast.style.zIndex = '9999';
        toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Partner Management - Add from Supplier dropdown
    function addPartnerFromSupplier() {
        const select = document.getElementById('supplier-select');
        const selectedOption = select.options[select.selectedIndex];

        if (!selectedOption.value) {
            showToast('Vui lòng chọn nhà cung cấp', 'error');
            return;
        }

        const supplierName = selectedOption.dataset.name;
        const supplierType = selectedOption.dataset.type;
        const supplierContact = selectedOption.dataset.contact;

        // Create partner item HTML
        const partnerHTML = `
            <div class="partner-item border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="mb-0">Đối tác dịch vụ</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePartnerItem(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select class="form-select partner-service-type" required>
                                <option value="">-- Loại dịch vụ --</option>
                                <option value="hotel" ${supplierType === 'hotel' ? 'selected' : ''}>Khách sạn</option>
                                <option value="transport" ${supplierType === 'transport' ? 'selected' : ''}>Vận chuyển</option>
                                <option value="restaurant" ${supplierType === 'restaurant' ? 'selected' : ''}>Nhà hàng</option>
                                <option value="guide" ${supplierType === 'guide' ? 'selected' : ''}>Hướng dẫn viên</option>
                                <option value="other" ${supplierType === 'other' ? 'selected' : ''}>Khác</option>
                            </select>
                            <label>Loại dịch vụ</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control partner-name" value="${supplierName}" placeholder=" " required>
                            <label>Tên đối tác</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating">
                            <input type="text" class="form-control partner-contact" value="${supplierContact}" placeholder=" " required>
                            <label>Thông tin liên hệ</label>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add to partners list
        const partnersList = document.getElementById('partners-list');
        partnersList.insertAdjacentHTML('beforeend', partnerHTML);

        // Hide empty message
        document.getElementById('partners-empty').style.display = 'none';

        // Reset select
        select.selectedIndex = 0;

        showToast('Đã thêm đối tác từ nhà cung cấp', 'success');
    }

    function removePartnerItem(button) {
        button.closest('.partner-item').remove();

        // Show empty message if no partners
        const partnersList = document.getElementById('partners-list');
        if (partnersList.children.length === 0) {
            document.getElementById('partners-empty').style.display = 'block';
        }
    }

    function updatePartnerData() {
        const partners = [];
        document.querySelectorAll('.partner-item').forEach(item => {
            partners.push({
                service_type: item.querySelector('.partner-service-type').value,
                partner_name: item.querySelector('.partner-name').value,
                contact: item.querySelector('.partner-contact').value
            });
        });
        document.getElementById('tour_partners').value = JSON.stringify(partners);
    }

    // Load draft if exists
    window.addEventListener('load', function() {
        const draft = localStorage.getItem('tour_draft');
        if (draft) {
            const draftData = JSON.parse(draft);
            // Populate form fields from draft
            Object.keys(draftData).forEach(key => {
                const field = document.querySelector(`[name="${key}"]`);
                if (field) {
                    field.value = draftData[key];
                }
            });
        }
    });
</script>

<style>
    /* Tour Create Page Styles - CSS moved to tours-modern.css */
</style>

<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>