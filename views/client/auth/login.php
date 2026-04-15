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
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <label style="margin-bottom: 0;"><i class="fas fa-lock"></i> Mật khẩu</label>
                    <a href="<?= BASE_URL ?>?action=forgot-password" style="font-size: 14px; text-decoration: none; color: #007bff;">Quên mật khẩu?</a>
                </div>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-control-vt"
                           placeholder="••••••••" required style="padding-right: 40px;">
                    <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6c757d;"></i>
                </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
    });
</script>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
