<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

$currentUserRole = $_SESSION['user']['role'] ?? 'customer';
?>

<main class="dashboard user-create-page">
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
                        <span class="breadcrumb-current">Thêm User Mới</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-user-plus title-icon"></i>
                            Thêm User Mới
                        </h1>
                        <p class="page-subtitle">Tạo tài khoản người dùng mới</p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=users" class="btn btn-modern btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        Hủy bỏ
                    </a>
                    <button type="submit" form="user-form" class="btn btn-modern btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Lưu User
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

        <!-- Progress Steps -->
        <div class="progress-steps-wrapper mb-4">
            <div class="progress-steps">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Thông tin cơ bản</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Mật khẩu</div>
                </div>
            </div>
        </div>

        <!-- User Form -->
        <form method="POST" action="<?= BASE_URL_ADMIN ?>&action=users/store" id="user-form">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Step 1: Basic Information -->
                    <div class="form-step active" id="step-1">
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
                                            <input type="text" class="form-control" id="full_name" name="full_name" required placeholder=" ">
                                            <label for="full_name">Họ và tên <span class="text-danger">*</span></label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" id="email" name="email" required placeholder=" ">
                                            <label for="email">Email <span class="text-danger">*</span></label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control" id="phone" name="phone" placeholder=" ">
                                            <label for="phone">Số điện thoại</label>
                                        </div>
                                    </div>

                                    <!-- Hidden role field - automatically set to customer -->
                                    <input type="hidden" name="role" value="customer">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Password -->
                    <div class="form-step" id="step-2">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-key text-warning me-2"></i>
                                    Mật khẩu
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="password" class="form-control" id="password" name="password" required placeholder=" " minlength="6">
                                            <label for="password">Mật khẩu <span class="text-danger">*</span></label>
                                        </div>
                                        <small class="text-muted d-block mt-2">Tối thiểu 6 ký tự</small>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required placeholder=" " minlength="6">
                                            <label for="password_confirm">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Lưu ý:</strong> Người dùng có thể đổi mật khẩu sau khi đăng nhập lần đầu
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
                                    Tạo User
                                </button>
                                <a href="<?= BASE_URL_ADMIN ?>&action=users" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Hủy
                                </a>
                            </div>

                            <hr class="my-3">

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="previousStep()" id="prev-btn" style="display: none;">
                                    <i class="fas fa-chevron-left me-1"></i>
                                    Quay lại
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" onclick="nextStep()" id="next-btn">
                                    Tiếp theo
                                    <i class="fas fa-chevron-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Hướng dẫn
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Điền đầy đủ thông tin bắt buộc
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Email phải là duy nhất
                                </li>
                                <li class="mb-0">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Mật khẩu tối thiểu 6 ký tự
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    let currentStep = 1;
    const totalSteps = 2;

    document.addEventListener('DOMContentLoaded', function() {
        initializeForm();
        setupPasswordValidation();
    });

    function initializeForm() {
        updateStepDisplay();
        updateNavigationButtons();
    }

    function setupPasswordValidation() {
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirm');

        passwordConfirm.addEventListener('input', function() {
            if (password.value !== passwordConfirm.value) {
                passwordConfirm.setCustomValidity('Mật khẩu không khớp');
            } else {
                passwordConfirm.setCustomValidity('');
            }
        });
    }

    function nextStep() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepDisplay();
                updateNavigationButtons();
            }
        }
    }

    function previousStep() {
        if (currentStep > 1) {
            currentStep--;
            updateStepDisplay();
            updateNavigationButtons();
        }
    }

    function updateStepDisplay() {
        document.querySelectorAll('.step').forEach(step => {
            step.classList.remove('active', 'completed');
            const stepNum = parseInt(step.dataset.step);
            if (stepNum === currentStep) {
                step.classList.add('active');
            } else if (stepNum < currentStep) {
                step.classList.add('completed');
            }
        });

        document.querySelectorAll('.form-step').forEach(step => {
            step.classList.remove('active');
        });
        document.getElementById(`step-${currentStep}`).classList.add('active');
    }

    function updateNavigationButtons() {
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        prevBtn.style.display = currentStep === 1 ? 'none' : 'block';
        nextBtn.style.display = currentStep === totalSteps ? 'none' : 'block';
    }

    function validateCurrentStep() {
        const currentStepElement = document.getElementById(`step-${currentStep}`);
        const requiredFields = currentStepElement.querySelectorAll('[required]');

        for (let field of requiredFields) {
            if (!field.value.trim()) {
                field.focus();
                alert('Vui lòng điền đầy đủ thông tin bắt buộc');
                return false;
            }
        }
        return true;
    }
</script>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>