<?php require_once PATH_VIEW_CLIENT . 'default/header.php'; ?>

<section style="background: linear-gradient(135deg,#0a1628,#1e6fff); padding: 80px 0 60px; color: #fff; text-align: center;">
    <div class="container">
        <h1 style="font-size:2.2rem;font-weight:800;margin-bottom:.5rem">Hướng dẫn đặt tour</h1>
        <p style="color:rgba(255,255,255,.8)">Chỉ vài bước đơn giản để có một chuyến đi tuyệt vời</p>
    </div>
</section>

<div class="container py-5" style="max-width:800px">
    <?php
    $steps = [
        ['icon' => 'fas fa-search', 'color' => '#1e6fff', 'title' => 'Bước 1: Tìm kiếm tour', 'content' => 'Sử dụng thanh tìm kiếm hoặc bộ lọc trên trang danh sách tour để tìm chuyến đi phù hợp với bạn. Bạn có thể lọc theo địa điểm, giá, thời gian hoặc đánh giá.'],
        ['icon' => 'fas fa-calendar-check', 'color' => '#27ae60', 'title' => 'Bước 2: Chọn ngày khởi hành', 'content' => 'Trong trang chi tiết tour, xem lịch khởi hành và chọn ngày phù hợp. Số chỗ còn trống được hiển thị rõ ràng.'],
        ['icon' => 'fas fa-pen', 'color' => '#f39c12', 'title' => 'Bước 3: Điền thông tin đặt tour', 'content' => 'Nhập họ tên, số điện thoại, email và số lượng người tham gia. Hệ thống sẽ tự động tính tổng tiền.'],
        ['icon' => 'fas fa-qrcode', 'color' => '#8e44ad', 'title' => 'Bước 4: Thanh toán', 'content' => 'Quét QR code hoặc chuyển khoản theo thông tin hiển thị. Đặt tour được giữ chỗ trong vòng 24 giờ.'],
        ['icon' => 'fas fa-check-circle', 'color' => '#e74c3c', 'title' => 'Bước 5: Xác nhận', 'content' => 'Sau khi thanh toán, nhân viên VietTour sẽ xác nhận và gửi thông tin chi tiết chuyến đi cho bạn qua email.'],
    ];
    foreach ($steps as $i => $s): ?>
    <div class="d-flex gap-4 mb-4">
        <div style="width:56px;height:56px;border-radius:50%;background:<?= $s['color'] ?>22;color:<?= $s['color'] ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.3rem;">
            <i class="<?= $s['icon'] ?>"></i>
        </div>
        <div class="card border-0 shadow-sm rounded-4 w-100">
            <div class="card-body">
                <h5 class="fw-bold mb-1"><?= $s['title'] ?></h5>
                <p class="text-muted mb-0 small"><?= $s['content'] ?></p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="text-center mt-4">
        <a href="<?= BASE_URL ?>?action=tour-list" class="btn btn-primary rounded-pill px-5">
            <i class="fas fa-compass me-2"></i>Khám phá tour ngay
        </a>
    </div>
</div>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
