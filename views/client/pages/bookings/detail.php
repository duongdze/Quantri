<?php
require_once PATH_VIEW_CLIENT . 'default/header.php';

$statusMap = [
    'pending'      => ['label' => 'Chờ thanh toán', 'class' => 'warning'],
    'paid'         => ['label' => 'Đã thanh toán',  'class' => 'success'],
    'cho_xac_nhan' => ['label' => 'Chờ xác nhận',  'class' => 'info'],
    'da_coc'       => ['label' => 'Đã cọc',         'class' => 'primary'],
    'hoan_tat'     => ['label' => 'Hoàn tất',       'class' => 'success'],
    'da_huy'       => ['label' => 'Đã hủy',         'class' => 'danger'],
    'completed'    => ['label' => 'Hoàn tất',       'class' => 'success'],
    'cancelled'    => ['label' => 'Đã hủy',         'class' => 'danger'],
];
$code = 'BK' . str_pad($booking['id'] ?? 0, 6, '0', STR_PAD_LEFT);
$statusInfo = $statusMap[$booking['status'] ?? ''] ?? ['label' => ucfirst($booking['status'] ?? ''), 'class' => 'secondary'];
?>

<div class="container my-5" style="max-width:900px">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?action=my-bookings">Đơn hàng của tôi</a></li>
            <li class="breadcrumb-item active"><?= $code ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Booking Header -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="text-muted small mb-1">Mã đặt tour</div>
                        <h4 class="fw-bold mb-0"><?= $code ?></h4>
                    </div>
                    <div>
                        <span class="badge bg-<?= $statusInfo['class'] ?> fs-6 px-3 py-2">
                            <?= $statusInfo['label'] ?>
                        </span>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small mb-1">Ngày đặt</div>
                        <div class="fw-bold"><?= date('d/m/Y H:i', strtotime($booking['booking_date'] ?? $booking['created_at'])) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tour Info -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 fw-bold p-4 pb-0">
                    <i class="fas fa-map-marked-alt text-primary me-2"></i>Thông tin tour
                </div>
                <div class="card-body p-4">
                    <h5 class="fw-bold"><?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?></h5>
                    <table class="table table-borderless small mb-0">
                        <tr>
                            <td class="text-muted w-50">Ngày khởi hành</td>
                            <td class="fw-bold"><?= !empty($booking['departure_date']) ? date('d/m/Y', strtotime($booking['departure_date'])) : '–' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tổng tiền</td>
                            <td class="fw-bold text-primary"><?= number_format($booking['total_price'] ?? $booking['final_price'] ?? 0, 0, ',', '.') ?>đ</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Trạng thái</td>
                            <td><span class="badge bg-<?= $statusInfo['class'] ?>"><?= $statusInfo['label'] ?></span></td>
                        </tr>
                        <?php if ($booking['status'] === 'da_huy' && !empty($booking['refund_amount'])): ?>
                        <tr>
                            <td class="text-muted">Số tiền hoàn lại</td>
                            <td class="fw-bold text-danger">
                                <?= number_format($booking['refund_amount'], 0, ',', '.') ?>đ 
                                <span class="fw-normal small text-muted">(<?= $booking['refund_percentage'] ?>%)</span>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 fw-bold p-4 pb-0">
                    <i class="fas fa-user text-primary me-2"></i>Người đặt
                </div>
                <div class="card-body p-4">
                    <table class="table table-borderless small mb-0">
                        <tr>
                            <td class="text-muted">Họ tên</td>
                            <td class="fw-bold"><?= htmlspecialchars($booking['customer_name'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email</td>
                            <td><?= htmlspecialchars($booking['customer_email'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Điện thoại</td>
                            <td><?= htmlspecialchars($booking['customer_phone'] ?? '') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Passengers -->
        <?php if (!empty($passengers)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 fw-bold p-4 pb-0">
                    <i class="fas fa-users text-primary me-2"></i>Danh sách hành khách
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Họ tên</th>
                                    <th>Loại</th>
                                    <th>SDT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($passengers as $i => $p): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($p['full_name']) ?></td>
                                    <td>
                                        <?= $p['passenger_type'] === 'adult' ? 
                                            '<span class="badge bg-primary">Người lớn</span>' : 
                                            '<span class="badge bg-info">Trẻ em</span>' ?>
                                    </td>
                                    <td><?= htmlspecialchars($p['phone'] ?? '–') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="col-12 d-flex gap-2">
            <a href="<?= BASE_URL ?>?action=my-bookings" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
            <?php if (in_array($booking['status'] ?? '', ['pending'])): ?>
            <a href="<?= BASE_URL ?>?action=booking-payment&code=<?= $code ?>" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-credit-card me-2"></i>Tiến hành thanh toán
            </a>
            <?php endif; ?>
            
            <?php if (in_array($booking['status'] ?? '', ['pending', 'cho_xac_nhan'])): ?>
            <a href="<?= BASE_URL ?>?action=booking-cancel&code=<?= $code ?>" 
               class="btn btn-outline-danger rounded-pill px-4"
               onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?')">
                <i class="fas fa-times-circle me-2"></i>Hủy đơn hàng
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
