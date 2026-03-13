<?php
$pageTitle = $pageTitle ?? 'Quên mật khẩu – VietTour';
require_once PATH_VIEW_CLIENT . 'default/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon"><i class="fas fa-key"></i></div>
            <h2>Quên mật khẩu</h2>
            <p class="auth-subtitle">Nhập email để nhận link đặt lại mật khẩu</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-vt alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert-vt alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>?action=forgot-password" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" class="form-control-vt" placeholder="your@email.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i>&nbsp;Gửi link đặt lại
            </button>
        </form>

        <div class="auth-divider mt-3">
            <a href="<?= BASE_URL ?>?action=login"><i class="fas fa-arrow-left me-1"></i>Quay lại đăng nhập</a>
        </div>
    </div>
</div>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
