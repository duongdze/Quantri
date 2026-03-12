<?php include_once PATH_VIEW_CLIENT . 'default/header.php'; ?>

<!-- Custom CSS for Premium Look -->
<style>
    .booking-stepper {
        position: relative;
        counter-reset: step;
        z-index: 1;
    }
    .booking-stepper::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        width: 100%;
        height: 2px;
        background: #e9ecef;
        z-index: -1;
    }
    .booking-step {
        width: 40px;
        height: 40px;
        background: #fff;
        border: 2px solid #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #adb5bd;
        margin: 0 auto 10px;
        transition: all 0.3s ease;
    }
    .booking-step.active {
        border-color: #0d6efd;
        background: #0d6efd;
        color: #fff;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
    }
    .booking-step.completed {
        border-color: #198754;
        background: #198754;
        color: #fff;
    }
    .step-label {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .step-label.active {
        color: #0d6efd;
    }
    
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
        color: #0d6efd;
        font-weight: 500;
    }
    
    .summary-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
    }
    .summary-header {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: white;
        padding: 20px;
        position: relative;
    }
    .summary-header::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        right: 0;
        height: 20px;
        background: #fff;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
    }
</style>

<div class="container my-5">
    <!-- Stepper -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="d-flex justify-content-between text-center booking-stepper">
                <div class="position-relative">
                    <div class="booking-step completed"><i class="fas fa-check"></i></div>
                    <div class="step-label">Chọn Tour</div>
                </div>
                <div class="position-relative">
                    <div class="booking-step active">2</div>
                    <div class="step-label active">Nhập Thông Tin</div>
                </div>
                <div class="position-relative">
                    <div class="booking-step">3</div>
                    <div class="step-label">Thanh Toán</div>
                </div>
                <div class="position-relative">
                    <div class="booking-step">4</div>
                    <div class="step-label">Hoàn Tất</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Booking Form -->
        <div class="col-lg-8 Order-lg-1">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 p-lg-5">
                    <h3 class="card-title fw-bold mb-4 text-primary">
                        <i class="fas fa-user-edit me-2"></i>Thông Tin Đặt Tour
                    </h3>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger border-0 bg-danger-subtle text-danger mb-4 rounded-3 d-flex align-items-center">
                            <i class="fas fa-exclamation-circle me-2 fa-lg"></i>
                            <div><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>?action=booking-store" method="POST" id="bookingForm">
                        <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                        <input type="hidden" name="departure_id" value="<?= $departure['id'] ?>">

                        <!-- Contact Info Section -->
                        <div class="mb-5">
                            <h5 class="fw-bold text-dark mb-3 border-start border-4 border-primary ps-3">Thông Tin Liên Hệ</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control bg-light border-0" id="full_name" name="full_name" placeholder="Họ và tên" required value="<?= (isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'customer') ? ($_SESSION['user']['full_name'] ?? '') : '' ?>">
                                        <label for="full_name">Họ và tên <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control bg-light border-0" id="phone" name="phone" placeholder="Số điện thoại" required value="<?= (isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'customer') ? ($_SESSION['user']['phone'] ?? '') : '' ?>">
                                        <label for="phone">Số điện thoại <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating">
                                        <input type="email" class="form-control bg-light border-0" id="email" name="email" placeholder="Email" required value="<?= (isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'customer') ? ($_SESSION['user']['email'] ?? '') : '' ?>">
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control bg-light border-0" id="address" name="address" placeholder="Địa chỉ" value="<?= (isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'customer') ? ($_SESSION['user']['address'] ?? '') : '' ?>">
                                        <label for="address">Địa chỉ</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control bg-light border-0" placeholder="Ghi chú" id="note" name="note" style="height: 100px"></textarea>
                                        <label for="note">Ghi chú thêm (Ăn kiêng, dị ứng...)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Passengers Section -->
                        <div class="mb-5">
                            <h5 class="fw-bold text-dark mb-3 border-start border-4 border-primary ps-3">Số Lượng Khách</h5>
                            <div class="bg-light p-4 rounded-4">
                                <div class="row g-4 align-items-center">
                                    <!-- Adult Input -->
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <label class="fw-bold d-block">Người lớn</label>
                                                <small class="text-muted">Trên 12 tuổi</small>
                                            </div>
                                            <div class="text-primary fw-bold">
                                                <?= number_format($departure['price_adult'] ?: $tour['base_price'], 0, ',', '.') ?>đ
                                            </div>
                                        </div>
                                        <div class="input-group">
                                            <button type="button" class="btn btn-outline-secondary" onclick="updateQty('adults', -1)"><i class="fas fa-minus"></i></button>
                                            <input type="number" class="form-control text-center fw-bold" name="adults" id="adults" min="1" value="1" readonly>
                                            <button type="button" class="btn btn-outline-secondary" onclick="updateQty('adults', 1)"><i class="fas fa-plus"></i></button>
                                        </div>
                                    </div>

                                    <!-- Children Input -->
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <label class="fw-bold d-block">Trẻ em</label>
                                                <small class="text-muted">5 - 11 tuổi</small>
                                            </div>
                                            <div class="text-primary fw-bold">
                                                <?= number_format($departure['price_child'] ?: ($departure['price_adult'] ?: $tour['base_price']), 0, ',', '.') ?>đ
                                            </div>
                                        </div>
                                        <div class="input-group">
                                            <button type="button" class="btn btn-outline-secondary" onclick="updateQty('children', -1)"><i class="fas fa-minus"></i></button>
                                            <input type="number" class="form-control text-center fw-bold" name="children" id="children" min="0" value="0" readonly>
                                            <button type="button" class="btn btn-outline-secondary" onclick="updateQty('children', 1)"><i class="fas fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="<?= BASE_URL ?>?action=tour-detail&id=<?= $tour['id'] ?>" class="text-decoration-none text-muted fw-bold">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-lg move-right-hover">
                                Tiếp tục thanh toán <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sticky Sidebar Summary -->
        <div class="col-lg-4 Order-lg-2">
            <div class="card summary-card shadow-lg sticky-top" style="top: 100px; z-index: 10;">
                <div class="summary-header">
                    <h5 class="mb-0 fw-bold text-white"><i class="fas fa-receipt me-2"></i>Tóm Tắt Chuyến Đi</h5>
                </div>
                <div class="card-body p-4 pt-4">
                    <!-- Tour Info Mini -->
                    <div class="d-flex mb-4">
                        <img src="<?= BASE_ASSETS_UPLOADS . $this->tourModel->getRelatedData('tour_gallery_images', $tour['id'])[0]['image_url'] ?? 'https://via.placeholder.com/100' ?>" 
                             class="rounded-3 object-fit-cover shadow-sm" style="width: 80px; height: 80px;" alt="Tour Thumb">
                        <div class="ms-3">
                            <h6 class="fw-bold line-clamp-2 mb-1"><?= htmlspecialchars($tour['name']) ?></h6>
                            <span class="badge bg-light text-secondary border"><i class="far fa-clock me-1"></i><?= $tour['duration_days'] ?> Ngày</span>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded-3 mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Khởi hành:</span>
                            <span class="fw-bold text-dark"><?= date('d/m/Y', strtotime($departure['departure_date'])) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Mã Tour:</span>
                            <span class="fw-bold text-dark">TOUR-<?= $tour['id'] ?></span>
                        </div>
                    </div>

                    <hr class="border-secondary opacity-10">

                    <!-- Pricing Detail -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary">Người lớn x<span id="summary-adults">1</span></span>
                            <span class="fw-bold" id="total-adults">...</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary">Trẻ em x<span id="summary-children">0</span></span>
                            <span class="fw-bold" id="total-children">0đ</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top border-2">
                        <span class="h6 fw-bold mb-0 text-muted">Tổng cộng</span>
                        <span class="h4 fw-bold text-primary mb-0" id="grand-total">...</span>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex align-items-center small text-success bg-success-subtle p-2 rounded justify-content-center">
                            <i class="fas fa-check-circle me-2"></i>Đã bao gồm thuế & phí
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const priceAdult = <?= $departure['price_adult'] ?: $tour['base_price'] ?>;
    const priceChild = <?= $departure['price_child'] ?: ($departure['price_adult'] ?: $tour['base_price']) ?>;

    function updateQty(id, change) {
        const input = document.getElementById(id);
        let val = parseInt(input.value) || 0;
        val += change;
        
        const min = parseInt(input.getAttribute('min'));
        if (val < min) val = min;
        
        input.value = val;
        calculateTotal();
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }

    function calculateTotal() {
        const adults = parseInt(document.getElementById('adults').value) || 0;
        const children = parseInt(document.getElementById('children').value) || 0;
        
        // Update counts
        document.getElementById('summary-adults').textContent = adults;
        document.getElementById('summary-children').textContent = children;
        
        // Update line totals
        const totalAdults = adults * priceAdult;
        const totalChildren = children * priceChild;
        
        document.getElementById('total-adults').textContent = formatCurrency(totalAdults);
        document.getElementById('total-children').textContent = formatCurrency(totalChildren);
        
        // Grand total
        const grandTotal = totalAdults + totalChildren;
        document.getElementById('grand-total').textContent = formatCurrency(grandTotal);
    }
    
    // Init call
    document.addEventListener('DOMContentLoaded', calculateTotal);
</script>

<?php include_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
