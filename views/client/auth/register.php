<?php
$pageTitle = 'Đăng ký – VietTour';
require_once PATH_VIEW_CLIENT . 'default/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card" style="max-width:480px">
        <!-- Logo -->
        <div class="auth-logo">
            <div class="logo-icon"><i class="fas fa-globe-asia"></i></div>
            <h2>Tạo tài khoản</h2>
            <p class="auth-subtitle">Đăng ký để trải nghiệm VietTour ngay hôm nay</p>
        </div>

        <!-- Alert -->
        <?php if (!empty($error)): ?>
            <div class="alert-vt alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="<?= BASE_URL ?>?action=register-post" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label><i class="fas fa-user"></i> Họ và tên</label>
                <input type="text" name="full_name" class="form-control-vt"
                       placeholder="Nguyễn Văn A" required
                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" class="form-control-vt"
                       placeholder="your@email.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label><i class="fas fa-phone"></i> Số điện thoại <span style="color:var(--gray-400)">(tuỳ chọn)</span></label>
                <input type="tel" name="phone" class="form-control-vt"
                       placeholder="0901 234 567"
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>

            <div class="row g-3">
                <div class="col-6">
                    <div class="form-group mb-0">
                        <label><i class="fas fa-lock"></i> Mật khẩu</label>
                        <input type="password" name="password" class="form-control-vt"
                               placeholder="••••••••" required minlength="6">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-0">
                        <label><i class="fas fa-lock"></i> Xác nhận</label>
                        <input type="password" name="password_confirm" class="form-control-vt"
                               placeholder="••••••••" required minlength="6">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit mt-3">
                <i class="fas fa-user-plus"></i> &nbsp;Tạo tài khoản
            </button>
        </form>

        <div class="auth-divider mt-3">
            Đã có tài khoản? <a href="<?= BASE_URL ?>?action=login">Đăng nhập</a>
        </div>
    </div>
</div>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>