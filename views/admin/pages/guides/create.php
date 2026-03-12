<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>

<main class="dashboard guide-create-page">
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
                        <a href="<?= BASE_URL_ADMIN ?>&action=guides" class="breadcrumb-link">
                            <i class="fas fa-user-tie"></i>
                            <span>Quản lý HDV</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current">Thêm HDV Mới</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-plus-circle title-icon"></i>
                            Thêm Hướng Dẫn Viên Mới
                        </h1>
                        <p class="page-subtitle">Tạo hồ sơ cho hướng dẫn viên mới</p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=guides" class="btn btn-modern btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        Hủy bỏ
                    </a>
                    <button type="submit" form="guide-form" class="btn btn-modern btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Lưu HDV
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
                    <div class="step-label">Chuyên môn</div>
                </div>
            </div>
        </div>

        <!-- Guide Form -->
        <form method="POST" action="<?= BASE_URL_ADMIN ?>&action=guides/store" enctype="multipart/form-data" id="guide-form">
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
                                            <input type="tel" class="form-control" id="phone" name="phone" required placeholder=" ">
                                            <label for="phone">Số điện thoại <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Avatar Upload -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-image text-success me-2"></i>
                                    Ảnh đại diện
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="main-image-upload">
                                    <div class="upload-area" id="avatarDropZone" onclick="document.getElementById('avatar').click()">
                                        <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-primary"></i>
                                        <p class="mb-1">Kéo thả hoặc click để chọn ảnh</p>
                                        <span class="text-muted small">JPG, PNG, GIF. Tối đa 5MB</span>
                                        <input type="file" name="avatar" id="avatar" accept="image/*" style="display: none;">
                                    </div>
                                    <div class="main-image-preview" id="avatarPreview" style="display: none;">
                                        <img src="" alt="Avatar Preview" class="img-fluid rounded">
                                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeAvatar()">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Professional Information -->
                    <div class="form-step" id="step-2">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-briefcase text-warning me-2"></i>
                                    Thông tin chuyên môn
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select" id="guide_type" name="guide_type">
                                                <option value="domestic">Nội địa</option>
                                                <option value="international">Quốc tế</option>
                                                <option value="specialized">Chuyên môn</option>
                                            </select>
                                            <label for="guide_type">Loại hướng dẫn viên</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="specialization" name="specialization" placeholder=" ">
                                            <label for="specialization">Chuyên môn</label>
                                        </div>
                                        <small class="text-muted d-block mt-2">VD: Chuyên tuyến miền Bắc, Chuyên khách đoàn...</small>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="languages" name="languages" placeholder=" ">
                                            <label for="languages">Ngôn ngữ sử dụng</label>
                                        </div>
                                        <small class="text-muted d-block mt-2">Phân cách bằng dấu phẩy</small>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" id="experience_years" name="experience_years" min="0" value="0" placeholder=" ">
                                            <label for="experience_years">Số năm kinh nghiệm</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select" id="health_status" name="health_status">
                                                <option value="">-- Chọn --</option>
                                                <option value="Tốt">Tốt</option>
                                                <option value="Khá">Khá</option>
                                                <option value="Trung bình">Trung bình</option>
                                                <option value="Cần theo dõi">Cần theo dõi</option>
                                            </select>
                                            <label for="health_status">Tình trạng sức khỏe</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="notes" name="notes" style="height: 120px" placeholder=" "></textarea>
                                            <label for="notes">Ghi chú</label>
                                        </div>
                                        <small class="text-muted d-block mt-2">Chứng chỉ, chuyên môn, kinh nghiệm đặc biệt...</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Info -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle text-info me-2"></i>
                                    Thông tin hệ thống
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-key me-2"></i>
                                    Mật khẩu mặc định: <strong>123456</strong>
                                    <br>
                                    <small>HDV có thể đổi mật khẩu sau khi đăng nhập lần đầu</small>
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
                                <button type="submit" form="guide-form" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Tạo hướng dẫn viên
                                </button>
                                <a href="<?= BASE_URL_ADMIN ?>&action=guides" class="btn btn-secondary">
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
        setupAvatarUpload();
    });

    function initializeForm() {
        updateStepDisplay();
        updateNavigationButtons();
    }

    function setupAvatarUpload() {
        document.getElementById('avatar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarDropZone').style.display = 'none';
                    const preview = document.getElementById('avatarPreview');
                    preview.querySelector('img').src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    function removeAvatar() {
        document.getElementById('avatar').value = '';
        document.getElementById('avatarDropZone').style.display = 'flex';
        document.getElementById('avatarPreview').style.display = 'none';
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