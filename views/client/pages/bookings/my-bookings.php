<?php
require_once PATH_VIEW_CLIENT . 'default/header.php';

$statusMap = [
    'pending'       => ['label' => 'Chờ thanh toán', 'class' => 'bg-warning text-dark'],
    'paid'          => ['label' => 'Đã thanh toán',  'class' => 'bg-success'],
    'cho_xac_nhan'  => ['label' => 'Chờ xác nhận',  'class' => 'bg-info'],
    'da_coc'        => ['label' => 'Đã cọc',         'class' => 'bg-primary'],
    'hoan_tat'      => ['label' => 'Hoàn tất',       'class' => 'bg-success'],
    'da_huy'        => ['label' => 'Đã hủy',         'class' => 'bg-danger'],
    'completed'     => ['label' => 'Hoàn tất',       'class' => 'bg-success'],
    'cancelled'     => ['label' => 'Đã hủy',         'class' => 'bg-danger'],
];
?>

<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
            <li class="breadcrumb-item active">Đơn hàng của tôi</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="fas fa-ticket-alt me-2 text-primary"></i>Đơn hàng của tôi</h2>
        <a href="<?= BASE_URL ?>?action=my-account" class="btn btn-outline-secondary rounded-pill btn-sm">
            <i class="fas fa-user me-1"></i>Tài khoản
        </a>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="text-center py-5">
            <div class="mb-4 opacity-25 fs-1"><i class="fas fa-ticket-alt"></i></div>
            <h5 class="text-muted">Bạn chưa có đơn hàng nào</h5>
            <p class="text-muted">Hãy khám phá và đặt tour ngay hôm nay!</p>
            <a href="<?= BASE_URL ?>?action=tour-list" class="btn btn-primary rounded-pill px-5">
                <i class="fas fa-compass me-2"></i>Khám phá tour
            </a>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($bookings as $b):
                $statusInfo = $statusMap[$b['status']] ?? ['label' => ucfirst($b['status']), 'class' => 'bg-secondary'];
                $code = 'BK' . str_pad($b['id'], 6, '0', STR_PAD_LEFT);
            ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 hover-lift">
                    <div class="card-body p-4">
                        <div class="row align-items-center g-3">
                            <!-- Tour image -->
                            <div class="col-auto d-none d-md-block">
                                <?php if (!empty($b['tour_image'])): ?>
                                    <img src="<?= BASE_ASSETS_UPLOADS . htmlspecialchars($b['tour_image']) ?>"
                                         style="width:80px;height:70px;object-fit:cover;border-radius:12px" alt="Tour">
                                <?php else: ?>
                                    <div style="width:80px;height:70px;background:#e5e7eb;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#9ca3af;">
                                        <i class="fas fa-mountain"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!-- Main info -->
                            <div class="col">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge <?= $statusInfo['class'] ?>"><?= $statusInfo['label'] ?></span>
                                    <small class="text-muted"><?= $code ?></small>
                                </div>
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($b['tour_name'] ?? 'N/A') ?></h6>
                                <div class="d-flex flex-wrap gap-3 small text-muted">
                                    <?php if (!empty($b['departure_date'])): ?>
                                        <span><i class="far fa-calendar me-1"></i><?= date('d/m/Y', strtotime($b['departure_date'])) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($b['duration_days'])): ?>
                                        <span><i class="far fa-clock me-1"></i><?= $b['duration_days'] ?> ngày</span>
                                    <?php endif; ?>
                                    <span><i class="far fa-calendar-alt me-1"></i>Đặt: <?= date('d/m/Y', strtotime($b['booking_date'] ?? $b['created_at'])) ?></span>
                                </div>
                            </div>
                            <!-- Price & Action -->
                            <div class="col-auto text-end">
                                <div class="fw-bold text-primary fs-5 mb-2">
                                    <?= number_format($b['total_price'] ?? $b['final_price'] ?? 0, 0, ',', '.') ?>đ
                                </div>
                                <a href="<?= BASE_URL ?>?action=booking-detail&code=<?= $code ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.hover-lift { transition: transform .2s, box-shadow .2s; }
.hover-lift:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.1) !important; }
</style>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
