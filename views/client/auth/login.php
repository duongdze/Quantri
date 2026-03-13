<?php
$pageTitle = 'Đăng nhập – VietTour';
require_once PATH_VIEW_CLIENT . 'default/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Logo -->
        <div class="auth-logo">
            <div class="logo-icon"><i class="fas fa-globe-asia"></i></div>
            <h2>Đăng nhập</h2>
            <p class="auth-subtitle">Chào mừng trở lại VietTour!</p>
        </div>

        <!-- Alert -->
        <?php if (!empty($error)): ?>
            <div class="alert-vt alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert-vt alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="<?= BASE_URL ?>?action=login-post" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" class="form-control-vt"
                       placeholder="your@email.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Mật khẩu</label>
                <input type="password" name="password" class="form-control-vt"
                       placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-sign-in-alt"></i> &nbsp;Đăng nhập
            </button>
        </form>

        <div class="auth-divider mt-3">
            Chưa có tài khoản? <a href="<?= BASE_URL ?>?action=register">Đăng ký ngay</a>
        </div>
    </div>
</div>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
