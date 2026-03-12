<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

$currentUserRole = $_SESSION['user']['role'] ?? 'customer';
$currentUserId = $_SESSION['user']['user_id'] ?? null;
$isOwnProfile = ($user['user_id'] == $currentUserId);
?>

<main class="dashboard user-edit-page">
    <div class="dashboard-container">
        <!-- Modern Page Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-modern">
                        <a href="<?= BASE_URL_ADMIN ?>&action=/" class="breadcrumb-link">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <a href="<?= BASE_URL_ADMIN ?>&action=users" class="breadcrumb-link">
                            <i class="fas fa-users"></i>
                            <span>Quản lý User</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current">Chỉnh sửa User</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-user-edit title-icon"></i>
                            Chỉnh sửa User
                        </h1>
                        <p class="page-subtitle"><?= htmlspecialchars($user['full_name']) ?></p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=users" class="btn btn-modern btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        Hủy bỏ
                    </a>
                    <button type="submit" form="user-form" class="btn btn-modern btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Cập nhật
                    </button>
                </div>
            </div>
        </header>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-modern alert-danger alert-dismissible fade show" role="alert">
                <div class="alert-content">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <span><?= $_SESSION['error'] ?></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- User Form -->
        <form method="POST" action="<?= BASE_URL_ADMIN ?>&action=users/update" id="user-form">
            <input type="hidden" name="id" value="<?= $user['user_id'] ?>">

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user text-primary me-2"></i>
                                Thông tin cơ bản
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="full_name" name="full_name" required placeholder=" " value="<?= htmlspecialchars($user['full_name']) ?>">
                                        <label for="full_name">Họ và tên <span class="text-danger">*</span></label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" required placeholder=" " value="<?= htmlspecialchars($user['email']) ?>">
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="phone" name="phone" placeholder=" " value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                        <label for="phone">Số điện thoại</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="role" name="role" required disabled>
                                            <option value="customer" selected>Khách hàng</option>
                                        </select>
                                        <label for="role">Vai trò <span class="text-danger">*</span></label>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle"></i>
                                        Vai trò không thể thay đổi
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Password Update (Optional) -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-key text-warning me-2"></i>
                                Đổi mật khẩu (Tùy chọn)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                Chỉ điền nếu muốn thay đổi mật khẩu. Để trống nếu giữ nguyên mật khẩu cũ.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="password" name="password" placeholder=" " minlength="6">
                                        <label for="password">Mật khẩu mới</label>
                                    </div>
                                    <small class="text-muted d-block mt-2">Tối thiểu 6 ký tự</small>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder=" " minlength="6">
                                        <label for="password_confirm">Xác nhận mật khẩu mới</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Form Actions -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thao tác</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" form="user-form" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Cập nhật User
                                </button>
                                <a href="<?= BASE_URL_ADMIN ?>&action=users" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Quay lại
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- User Info -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Thông tin
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="info-item mb-3">
                                <label class="text-muted small">ID User</label>
                                <div class="fw-medium">#<?= $user['user_id'] ?></div>
                            </div>
                            <div class="info-item mb-3">
                                <label class="text-muted small">Ngày tạo</label>
                                <div class="fw-medium"><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></div>
                            </div>
                            <?php if (!empty($user['updated_at'])): ?>
                                <div class="info-item">
                                    <label class="text-muted small">Cập nhật lần cuối</label>
                                    <div class="fw-medium"><?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        setupPasswordValidation();
    });

    function setupPasswordValidation() {
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirm');

        passwordConfirm.addEventListener('input', function() {
            if (password.value && password.value !== passwordConfirm.value) {
                passwordConfirm.setCustomValidity('Mật khẩu không khớp');
            } else {
                passwordConfirm.setCustomValidity('');
            }
        });

        password.addEventListener('input', function() {
            if (this.value && passwordConfirm.value && this.value !== passwordConfirm.value) {
                passwordConfirm.setCustomValidity('Mật khẩu không khớp');
            } else {
                passwordConfirm.setCustomValidity('');
            }
        });
    }
</script>

<style>
    .info-item {
        padding: 10px;
        background: #f8f9fa;
        border-radius: 6px;
    }
</style>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>