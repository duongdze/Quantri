<?php include_once PATH_VIEW_CLIENT . 'default/header.php'; ?>

<div class="container my-5 text-center">
    <div class="card shadow-lg border-0 py-5 mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <div class="mb-4 text-success">
                <i class="fas fa-check-circle fa-5x"></i>
            </div>
            <h2 class="mb-3 fw-bold text-success">Đặt Tour Thành Công!</h2>
            <p class="lead text-muted mb-4">Cảm ơn bạn đã tin tưởng dịch vụ của chúng tôi.</p>
            
            <div class="bg-light p-4 rounded mb-4 text-start">
                <p><strong>Mã đơn hàng:</strong> <span class="text-primary fw-bold"><?= htmlspecialchars($code) ?></span></p>
                <p>Chúng tôi đã nhận được yêu cầu đặt tour của bạn. Nhân viên tư vấn sẽ liên hệ lại với bạn trong thời gian sớm nhất để xác nhận thông tin.</p>
            </div>
            
            <a href="<?= BASE_URL ?>" class="btn btn-primary px-5 py-2">
                <i class="fas fa-home me-2"></i>Trở về trang chủ
            </a>
        </div>
    </div>
</div>

<?php include_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
