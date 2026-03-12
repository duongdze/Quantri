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
                            <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-money-bill-wave me-2"></i>Thông Tin Chuyển Khoản</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-5 text-center mb-4 mb-md-0">
                                    <div class="qr-card p-3 d-inline-block bg-white">
                                        <!-- QR Code Generation Link -->
                                        <!-- https://img.vietqr.io/image/[BANK_ID]-[ACCOUNT_NO]-[TEMPLATE].png?amount=[AMOUNT]&addInfo=[CONTENT] -->
                                        <?php 
                                            // Config Bank Info (You can move this to config later)
                                            $bankId = 'MB'; // MB Bank
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
</script>

<?php include_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
