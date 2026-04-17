<?php include_once PATH_VIEW_CLIENT . 'default/header.php'; ?>

<!-- Custom CSS for Payment Page -->
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
    
    .qr-card {
        border: 2px solid #e9ecef;
        border-radius: 16px;
        transition: all 0.3s ease;
    }
    .qr-card:hover {
        border-color: #0d6efd;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .bank-info-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px dashed #e9ecef;
    }
    .bank-info-row:last-child {
        border-bottom: none;
    }
    .copy-btn {
        cursor: pointer;
        color: #0d6efd;
        font-size: 0.9rem;
    }
    .copy-btn:hover {
        text-decoration: underline;
    }
    
    /* Hiệu ứng cho ô chọn thanh toán */
    .payment-option-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #e9ecef !important;
        position: relative;
    }
    .payment-option-card:hover {
        border-color: #0d6efd !important;
        background-color: #f8f9ff !important;
    }
    .payment-option-card.active {
        border-color: #198754 !important;
        background-color: #f0fff4 !important;
    }
    .payment-option-card .check-icon {
        position: absolute;
        top: 10px;
        right: 10px;
        color: #198754;
        display: none;
    }
    .payment-option-card.active .check-icon {
        display: block;
    }
</style>

<div class="container my-5">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?action=tour-list">Tour</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?action=tour-detail&id=<?= $tour['id'] ?>"><?= htmlspecialchars($tour['name']) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Thanh toán</li>
        </ol>
    </nav>

    <!-- Stepper -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="d-flex justify-content-between text-center booking-stepper">
                <div class="position-relative">
                    <div class="booking-step completed"><i class="fas fa-check"></i></div>
                    <div class="step-label">Chọn Tour</div>
                </div>
                <div class="position-relative">
                    <div class="booking-step completed"><i class="fas fa-check"></i></div>
                    <div class="step-label">Nhập Thông Tin</div>
                </div>
                <div class="position-relative">
                    <div class="booking-step active">3</div>
                    <div class="step-label active">Thanh Toán</div>
                </div>
                <div class="position-relative">
                    <div class="booking-step">4</div>
                    <div class="step-label">Hoàn Tất</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-3">Thanh Toán Đặt Tour</h2>
                <p class="text-muted lead">Vui lòng quét mã QR hoặc chuyển khoản theo thông tin dưới đây để hoàn tất việc đặt tour.</p>
                <div class="badge bg-warning text-dark px-3 py-2 fs-6 rounded-pill">
                    <i class="fas fa-clock me-2"></i>Chờ thanh toán
                </div>
            </div>

            <div class="row g-4 d-flex align-items-stretch">
                <!-- Payment Info -->
                <div class="col-lg-7">
                    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-bottom p-4">
                            <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-money-bill-wave me-2"></i>Chọn Phương Thức Thanh Toán</h5>
                        </div>
                        <div class="card-body p-4">
                            <!-- Payment Method Tabs -->
                            <ul class="nav nav-pills mb-4 gap-2" id="paymentTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active rounded-pill px-4" id="bank-tab" data-bs-toggle="pill" data-bs-target="#bankTransfer" type="button" role="tab">
                                        <i class="fas fa-university me-2"></i>Chuyển khoản
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-pill px-4" id="cash-tab" data-bs-toggle="pill" data-bs-target="#cashPayment" type="button" role="tab">
                                        <i class="fas fa-money-bill-alt me-2"></i>Tiền mặt
                                    </button>
                                </li>
                                <?php if (defined('VNPAY_ENABLED') && VNPAY_ENABLED): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-pill px-4" id="vnpay-tab" data-bs-toggle="pill" data-bs-target="#vnpayPayment" type="button" role="tab">
                                        <i class="fas fa-credit-card me-2"></i>VNPay
                                    </button>
                                </li>
                                <?php endif; ?>
                            </ul>

                            <div class="tab-content" id="paymentTabContent">
                                <!-- Tab: Bank Transfer -->
                                <div class="tab-pane fade show active" id="bankTransfer" role="tabpanel">
                                    <div class="row align-items-center">
                                        <div class="col-md-5 text-center mb-4 mb-md-0">
                                            <div class="qr-card p-3 d-inline-block bg-white">
                                                <?php 
                                                    $bankId = 'MB';
                                                    $accountNo = '0986951086'; 
                                                    $accountName = 'Kim Van Kien';
                                                    $amount = $booking['total_price'];
                                                    $content = $code . ' THANH TOAN';
                                                    $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-compact2.jpg?amount={$amount}&addInfo=" . urlencode($content) . "&accountName=" . urlencode($accountName);
                                                ?>
                                                <img src="<?= $qrUrl ?>" alt="QR Payment" class="img-fluid rounded" style="max-width: 200px;">
                                                <p class="small text-muted mt-2 mb-0">Quét mã để thanh toán nhanh</p>
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <div class="d-flex flex-column gap-3">
                                                <div class="d-flex p-3 bg-light rounded-3 align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="rounded-circle bg-white p-2 text-primary shadow-sm" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-university fa-lg"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <small class="text-muted d-block">Ngân hàng</small>
                                                        <span class="fw-bold">MB Bank (Quân Đội)</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex p-3 bg-light rounded-3 align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="rounded-circle bg-white p-2 text-primary shadow-sm" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-credit-card fa-lg"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <small class="text-muted d-block">Số tài khoản</small>
                                                        <span class="fw-bold fs-5 text-dark" id="accNum"><?= $accountNo ?></span>
                                                    </div>
                                                    <button class="btn btn-link btn-sm copy-btn" onclick="copyToClipboard('accNum')">
                                                        <i class="far fa-copy"></i>
                                                    </button>
                                                </div>

                                                <div class="d-flex p-3 bg-light rounded-3 align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="rounded-circle bg-white p-2 text-primary shadow-sm" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-user fa-lg"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <small class="text-muted d-block">Chủ tài khoản</small>
                                                        <span class="fw-bold"><?= $accountName ?></span>
                                                    </div>
                                                </div>

                                                <div class="d-flex p-3 bg-light rounded-3 align-items-center border border-warning bg-warning-subtle">
                                                    <div class="flex-shrink-0">
                                                        <div class="rounded-circle bg-white p-2 text-warning shadow-sm" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-comment-alt fa-lg"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <small class="text-muted d-block">Nội dung chuyển khoản</small>
                                                        <span class="fw-bold text-danger fs-5" id="transContent"><?= $code ?> THANH TOAN</span>
                                                    </div>
                                                    <button class="btn btn-link btn-sm copy-btn text-warning" onclick="copyToClipboard('transContent')">
                                                        <i class="far fa-copy"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab: Cash Payment -->
                                <div class="tab-pane fade" id="cashPayment" role="tabpanel">
                                    <div class="text-center py-4">
                                        <div class="mb-4">
                                            <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle rounded-circle" style="width: 80px; height: 80px;">
                                                <i class="fas fa-hand-holding-usd fa-2x text-success"></i>
                                            </div>
                                        </div>
                                        <h5 class="fw-bold mb-3">Thanh toán bằng Tiền mặt</h5>
                                        <p class="text-muted mb-4">Quý khách vui lòng chọn một trong hai hình thức thanh toán tiền mặt dưới đây:</p>
                                        
                                        <div class="row g-3 justify-content-center">
                                            <div class="col-md-5">
                                                <div class="p-3 border rounded-4 bg-light h-100 payment-option-card active" onclick="selectCashOption(this)">
                                                    <div class="check-icon"><i class="fas fa-check-circle"></i></div>
                                                    <i class="fas fa-building text-primary mb-2 fa-lg"></i>
                                                    <h6 class="fw-bold">Tại văn phòng</h6>
                                                    <small class="text-muted">Tòa nhà VietTour, Số 123 Cầu Giấy, Hà Nội</small>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="p-3 border rounded-4 bg-light h-100 payment-option-card" onclick="selectCashOption(this)">
                                                    <div class="check-icon"><i class="fas fa-check-circle"></i></div>
                                                    <i class="fas fa-street-view text-primary mb-2 fa-lg"></i>
                                                    <h6 class="fw-bold">Tại địa chỉ của bạn</h6>
                                                    <small class="text-muted">Nhân viên sẽ đến thu tiền tận nơi (Có phụ phí 20k)</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="alert alert-warning mt-4 mb-0 border-0 bg-warning-subtle small mx-auto" style="max-width: 500px;">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Vui lòng giữ lại biên lai thu tiền để xác nhận việc đặt tour của quý khách.
                                        </div>
                                    </div>
                                </div>

                                <?php if (defined('VNPAY_ENABLED') && VNPAY_ENABLED): ?>
                                <!-- Tab: VNPay -->
                                <div class="tab-pane fade" id="vnpayPayment" role="tabpanel">
                                    <div class="text-center py-4">
                                        <div class="mb-4">
                                            <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle rounded-circle" style="width: 80px; height: 80px;">
                                                <i class="fas fa-shield-alt fa-2x text-primary"></i>
                                            </div>
                                        </div>
                                        <h5 class="fw-bold mb-2">Thanh toán qua VNPay</h5>
                                        <p class="text-muted mb-4">Thanh toán an toàn bằng thẻ ATM/Visa/MasterCard hoặc QR Pay qua VNPay</p>
                                        
                                        <div class="row justify-content-center mb-4">
                                            <div class="col-md-8">
                                                <div class="d-flex justify-content-center gap-3 flex-wrap">
                                                    <span class="badge bg-light text-dark border px-3 py-2"><i class="fas fa-credit-card me-1"></i> ATM nội địa</span>
                                                    <span class="badge bg-light text-dark border px-3 py-2"><i class="fab fa-cc-visa me-1"></i> Visa</span>
                                                    <span class="badge bg-light text-dark border px-3 py-2"><i class="fab fa-cc-mastercard me-1"></i> MasterCard</span>
                                                    <span class="badge bg-light text-dark border px-3 py-2"><i class="fas fa-qrcode me-1"></i> QR Pay</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-light rounded-4 p-4 mb-4 mx-auto" style="max-width: 400px;">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted">Mã đơn hàng:</span>
                                                <span class="fw-bold"><?= $code ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Số tiền:</span>
                                                <span class="fw-bold text-primary fs-5"><?= number_format($booking['total_price'], 0, ',', '.') ?>đ</span>
                                            </div>
                                        </div>

                                        <a href="<?= BASE_URL ?>?action=vnpay-process&code=<?= $code ?>" 
                                           class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-sm fw-bold">
                                            <i class="fas fa-lock me-2"></i>Thanh toán VNPay ngay
                                        </a>

                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Bạn sẽ được chuyển sang trang VNPay để hoàn tất thanh toán
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Summary -->
                <div class="col-lg-5">
                    <div class="card h-100 shadow-sm border-0 rounded-4">
                        <div class="card-header bg-white border-bottom p-4">
                            <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-receipt me-2"></i>Chi Tiết Đơn Hàng</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center mb-4 pb-4 border-bottom">
                                <span class="d-block text-muted mb-2">Tổng thanh toán</span>
                                <h2 class="text-primary fw-bold display-6"><?= number_format($booking['total_price'], 0, ',', '.') ?>đ</h2>
                            </div>
                            
                            <dl class="row mb-0">
                                <dt class="col-sm-5 text-muted fw-normal mb-3">Mã đơn hàng</dt>
                                <dd class="col-sm-7 fw-bold text-end mb-3"><?= $code ?></dd>

                                <dt class="col-sm-5 text-muted fw-normal mb-3">Ngày đặt</dt>
                                <dd class="col-sm-7 fw-bold text-end mb-3"><?= date('d/m/Y H:i', strtotime($booking['booking_date'])) ?></dd>
                                
                                <dt class="col-sm-5 text-muted fw-normal mb-3">Trạng thái</dt>
                                <dd class="col-sm-7 text-end mb-3"><span class="badge bg-warning text-dark">Chờ thanh toán</span></dd>
                            </dl>
                            
                            <div class="mt-4 pt-3 border-top">
                                <a href="<?= BASE_URL ?>?action=booking-success&code=<?= $code ?>" class="btn btn-success w-100 py-3 fw-bold rounded-pill mb-3 shadow-sm">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Tôi đã thanh toán
                                </a>
                                <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary w-100 py-2 rounded-pill border-0">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Quay về trang chủ
                                </a>
                            </div>
                            
                            <div class="alert alert-info mt-3 mb-0 small border-0 bg-info-subtle">
                                <i class="fas fa-info-circle me-1"></i>
                                Đơn hàng sẽ được xử lý trong vòng 24h sau khi nhận được thanh toán.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    var copyText = document.getElementById(elementId).innerText;
    navigator.clipboard.writeText(copyText).then(function() {
        // Show simplified feedback
        const btn = document.querySelector(`[onclick="copyToClipboard('${elementId}')"]`);
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => {
            btn.innerHTML = originalHtml;
        }, 2000);
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}
function selectCashOption(element) {
    // Xóa class active ở tất cả các ô
    document.querySelectorAll('.payment-option-card').forEach(card => {
        card.classList.remove('active');
    });
    // Thêm class active vào ô vừa chọn
    element.classList.add('active');
}
</script>

<?php include_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
