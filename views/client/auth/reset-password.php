<?php
$pageTitle = 'Đặt lại mật khẩu – VietTour';
require_once PATH_VIEW_CLIENT . 'default/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon"><i class="fas fa-lock"></i></div>
            <h2>Đặt lại mật khẩu</h2>
            <p class="auth-subtitle">Nhập mật khẩu mới cho tài khoản của bạn</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-vt alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($valid)): ?>
        <form action="<?= BASE_URL ?>?action=reset-password" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Mật khẩu mới</label>
                <input type="password" name="new_password" class="form-control-vt" placeholder="••••••••" required minlength="6">
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Xác nhận mật khẩu</label>
                <input type="password" name="confirm_password" class="form-control-vt" placeholder="••••••••" required minlength="6">
            </div>
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i>&nbsp;Đặt lại mật khẩu
            </button>
        </form>
        <?php else: ?>
            <div class="auth-divider mt-3 text-center">
                <a href="<?= BASE_URL ?>?action=forgot-password" class="btn-submit d-block text-center">Yêu cầu link mới</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
