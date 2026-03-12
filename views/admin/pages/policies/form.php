<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

// Check if editing or creating
$isEdit = isset($policy) && !empty($policy);
$pageTitle = $isEdit ? 'Chỉnh sửa Chính sách' : 'Thêm Chính sách Mới';
$submitAction = $isEdit ? 'policies/update' : 'policies/store';

// Get old input in case of validation errors
$old = $_SESSION['old_input'] ?? [];
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['old_input'], $_SESSION['form_errors']);

// Merge policy data with old input (old input takes precedence)
if ($isEdit) {
    $formData = array_merge($policy, $old);
} else {
    $formData = $old;
}
?>

<main class="dashboard tour-create-page">
    <div class="dashboard-container">
        <!-- Modern Page Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-modern">
                        <a href="<?= BASE_URL_ADMIN ?>& action=/" class="breadcrumb-link">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <a href="<?= BASE_URL_ADMIN ?>& action=policies" class="breadcrumb-link">
                            <i class="fas fa-shield-alt"></i>
                            <span>Quản lý Chính sách</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current"><?= $pageTitle ?></span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas <?= $isEdit ? 'fa-edit' : 'fa-plus-circle' ?> title-icon"></i>
                            <?= $pageTitle ?>
                        </h1>
                        <p class="page-subtitle">
                            <?= $isEdit ? 'Cập nhật thông tin chính sách' : 'Điền thông tin để tạo chính sách mới' ?>
                        </p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>& action=policies" class="btn btn-modern btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        Hủy bỏ
                    </a>
                    <button type="submit" form="policy-form" class="btn btn-modern btn-primary">
                        <i class="fas fa-save me-2"></i>
                        <?= $isEdit ? 'Cập nhật' : 'Tạo mới' ?>
                    </button>
                </div>
            </div>
        </header>

        <!-- Alert Messages -->
        <?php if (!empty($errors)): ?>
            <div class="alert-modern alert-danger alert-dismissible fade show" role="alert">
                <div class="alert-content">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <span>Vui lòng kiểm tra lại thông tin đã nhập</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <div class="row">
            <div class="mx-auto">
                <form method="POST" action="<?= BASE_URL_ADMIN ?>&action=<?= $submitAction ?>" id="policy-form">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= $policy['id'] ?>">
                    <?php endif; ?>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Thông tin chính sách
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Policy Name -->
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" 
                                               name="name" 
                                               id="policy-name" 
                                               class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                               value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                                               required 
                                               placeholder=" ">
                                        <label for="policy-name">
                                            Tên chính sách <span class="text-danger">*</span>
                                        </label>
                                        <?php if (isset($errors['name'])): ?>
                                            <div class="invalid-feedback">
                                                <?= $errors['name'] ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Ví dụ: Chính sách hủy tour, Chính sách hoàn tiền, Điều khoản sử dụng
                                        </div>
                                    </div>
                                </div>

                                <!-- Slug (optional) -->
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" 
                                               name="slug" 
                                               id="policy-slug" 
                                               class="form-control <?= isset($errors['slug']) ? 'is-invalid' : '' ?>"
                                               value="<?= htmlspecialchars($formData['slug'] ?? '') ?>"
                                               placeholder=" ">
                                        <label for="policy-slug">
                                            Slug (URL thân thiện)
                                        </label>
                                        <?php if (isset($errors['slug'])): ?>
                                            <div class="invalid-feedback">
                                                <?= $errors['slug'] ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Để trống để tự động tạo từ tên chính sách. Chỉ dùng chữ thường, số và dấu gạch ngang
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea name="description" 
                                                  id="description" 
                                                  class="form-control" 
                                                  style="height: 200px" 
                                                  placeholder=" "><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
                                        <label for="description">Mô tả chi tiết</label>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Nội dung chi tiết của chính sách sẽ hiển thị cho khách hàng
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="<?= BASE_URL_ADMIN ?>& action=policies" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Hủy bỏ
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    <?= $isEdit ? 'Cập nhật' : 'Tạo mới' ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate slug from name
    const nameInput = document.getElementById('policy-name');
    const slugInput = document.getElementById('policy-slug');
    
    // Only auto-generate if slug is empty (for new policies)
    <?php if (!$isEdit): ?>
    nameInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
            const slug = generateSlug(this.value);
            slugInput.value = slug;
            slugInput.dataset.autoGenerated = 'true';
        }
    });
    
    slugInput.addEventListener('input', function() {
        // User manually edited slug
        if (this.value) {
            this.dataset.autoGenerated = 'false';
        }
    });
    <?php endif; ?>
    
    function generateSlug(text) {
        // Vietnamese to ASCII mapping
        const vietnameseMap = {
            'à': 'a', 'á': 'a', 'ạ': 'a', 'ả': 'a', 'ã': 'a',
            'â': 'a', 'ầ': 'a', 'ấ': 'a', 'ậ': 'a', 'ẩ': 'a', 'ẫ': 'a',
            'ă': 'a', 'ằ': 'a', 'ắ': 'a', 'ặ': 'a', 'ẳ': 'a', 'ẵ': 'a',
            'è': 'e', 'é': 'e', 'ẹ': 'e', 'ẻ': 'e', 'ẽ': 'e',
            'ê': 'e', 'ề': 'e', 'ế': 'e', 'ệ': 'e', 'ể': 'e', 'ễ': 'e',
            'ì': 'i', 'í': 'i', 'ị': 'i', 'ỉ': 'i', 'ĩ': 'i',
            'ò': 'o', 'ó': 'o', 'ọ': 'o', 'ỏ': 'o', 'õ': 'o',
            'ô': 'o', 'ồ': 'o', 'ố': 'o', 'ộ': 'o', 'ổ': 'o', 'ỗ': 'o',
            'ơ': 'o', 'ờ': 'o', 'ớ': 'o', 'ợ': 'o', 'ở': 'o', 'ỡ': 'o',
            'ù': 'u', 'ú': 'u', 'ụ': 'u', 'ủ': 'u', 'ũ': 'u',
            'ư': 'u', 'ừ': 'u', 'ứ': 'u', 'ự': 'u', 'ử': 'u', 'ữ': 'u',
            'ỳ': 'y', 'ý': 'y', 'ỵ': 'y', 'ỷ': 'y', 'ỹ': 'y',
            'đ': 'd',
            'À': 'A', 'Á': 'A', 'Ạ': 'A', 'Ả': 'A', 'Ã': 'A',
            'Â': 'A', 'Ầ': 'A', 'Ấ': 'A', 'Ậ': 'A', 'Ẩ': 'A', 'Ẫ': 'A',
            'Ă': 'A', 'Ằ': 'A', 'Ắ': 'A', 'Ặ': 'A', 'Ẳ': 'A', 'Ẵ': 'A',
            'È': 'E', 'É': 'E', 'Ẹ': 'E', 'Ẻ': 'E', 'Ẽ': 'E',
            'Ê': 'E', 'Ề': 'E', 'Ế': 'E', 'Ệ': 'E', 'Ể': 'E', 'Ễ': 'E',
            'Ì': 'I', 'Í': 'I', 'Ị': 'I', 'Ỉ': 'I', 'Ĩ': 'I',
            'Ò': 'O', 'Ó': 'O', 'Ọ': 'O', 'Ỏ': 'O', 'Õ': 'O',
            'Ô': 'O', 'Ồ': 'O', 'Ố': 'O', 'Ộ': 'O', 'Ổ': 'O', 'Ỗ': 'O',
            'Ơ': 'O', 'Ờ': 'O', 'Ớ': 'O', 'Ợ': 'O', 'Ở': 'O', 'Ỡ': 'O',
            'Ù': 'U', 'Ú': 'U', 'Ụ': 'U', 'Ủ': 'U', 'Ũ': 'U',
            'Ư': 'U', 'Ừ': 'U', 'Ứ': 'U', 'Ự': 'U', 'Ử': 'U', 'Ữ': 'U',
            'Ỳ': 'Y', 'Ý': 'Y', 'Ỵ': 'Y', 'Ỷ': 'Y', 'Ỹ': 'Y',
            'Đ': 'D'
        };
        
        let slug = text.toLowerCase();
        
        // Replace Vietnamese characters
        for (let char in vietnameseMap) {
            slug = slug.replace(new RegExp(char, 'g'), vietnameseMap[char].toLowerCase());
        }
        
        // Replace non-alphanumeric with hyphens
        slug = slug.replace(/[^a-z0-9]+/g, '-');
        
        // Remove leading/trailing hyphens
        slug = slug.replace(/^-+|-+$/g, '');
        
        return slug;
    }
});
</script>

<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>