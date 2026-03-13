<?php include_once PATH_VIEW_CLIENT . 'default/header.php'; ?>

<div class="container my-5 text-center">
    <div class="card shadow-lg border-0 py-5 mx-auto" style="max-width: 600px;">
        <div class="card-body">
<?php
$pageTitle = 'Đặt tour thành công – VietTour';
require_once PATH_VIEW_CLIENT . 'default/header.php';
$code = $_GET['code'] ?? '';
?>

<div class="container py-5" style="max-width:700px">
    <div class="text-center mb-5">
        <!-- Checkmark animation -->
        <div class="success-circle mx-auto mb-4">
            <i class="fas fa-check"></i>
        </div>
        <h1 class="fw-bold" style="color:#111827;font-size:1.8rem">Đặt tour thành công!</h1>
        <p class="text-muted">Cảm ơn bạn đã đặt tour cùng VietTour. Chúng tôi sẽ xác nhận đơn hàng trong vòng 24 giờ.</p>
    </div>

    <!-- Booking info card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <div class="text-muted small mb-1">Mã đặt tour</div>
                    <h4 class="fw-bold text-primary mb-0"><?= htmlspecialchars($code) ?></h4>
                </div>
                <span class="badge bg-warning text-dark fs-6 px-3 py-2">Chờ xác nhận</span>
            </div>

            <?php if (!empty($booking)): ?>
            <table class="table table-borderless small mb-0">
                <tr>
                    <td class="text-muted w-50">Tour</td>
                    <td class="fw-bold"><?= htmlspecialchars($booking['tour_name'] ?? '') ?></td>
                </tr>
                <?php if (!empty($booking['departure_date'])): ?>
                <tr>
                    <td class="text-muted">Ngày khởi hành</td>
                    <td class="fw-bold"><?= date('d/m/Y', strtotime($booking['departure_date'])) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="text-muted">Tổng tiền</td>
                    <td class="fw-bold text-primary"><?= number_format($booking['final_price'] ?? 0, 0, ',', '.') ?>đ</td>
                </tr>
                <tr>
                    <td class="text-muted">Khách hàng</td>
                    <td><?= htmlspecialchars($booking['customer_name'] ?? '') ?></td>
                </tr>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Next steps -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background:linear-gradient(135deg,#f0f4ff,#fff)">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Bước tiếp theo</h6>
            <ul class="list-unstyled mb-0 small text-muted">
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Nhân viên sẽ liên hệ xác nhận đơn trong vòng 24 giờ</li>
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Giữ mã đặt tour để tra cứu thông tin</li>
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Mang theo giấy tờ tùy thân vào ngày khởi hành</li>
            </ul>
        </div>
    </div>

    <!-- Actions -->
    <div class="d-flex gap-3 justify-content-center flex-wrap">
        <?php if (!empty($_SESSION['user'])): ?>
        <a href="<?= BASE_URL ?>?action=my-bookings" class="btn btn-primary rounded-pill px-5">
            <i class="fas fa-ticket-alt me-2"></i>Xem đơn hàng của tôi
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary rounded-pill px-5">
            <i class="fas fa-home me-2"></i>Về trang chủ
        </a>
    </div>
</div>

<style>
.success-circle {
    width: 90px; height: 90px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; color: #fff;
    box-shadow: 0 8px 32px rgba(34,197,94,.3);
    animation: successPop .5s cubic-bezier(.175,.885,.32,1.275) both;
}
@keyframes successPop {
    from { transform: scale(0); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}
</style>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
