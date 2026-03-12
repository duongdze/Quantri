<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>
<main class="dashboard">
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
                        <a href="<?= BASE_URL_ADMIN ?>&action=tours_categories" class="breadcrumb-link">
                            <i class="fas fa-folder"></i>
                            <span>Quản lý Danh mục Tour</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current"><?= isset($category) ? 'Chỉnh sửa Danh mục' : 'Thêm Danh mục Mới' ?></span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-<?= isset($category) ? 'edit' : 'plus-circle' ?> title-icon"></i>
                            <?= isset($category) ? 'Chỉnh sửa Danh mục' : 'Thêm Danh mục Mới' ?>
                        </h1>
                        <p class="page-subtitle">
                            <?= isset($category) ? 'Cập nhật thông tin danh mục tour' : 'Điền thông tin để tạo danh mục tour mới' ?>
                        </p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=tours_categories" class="btn btn-modern btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        Hủy bỏ
                    </a>
                    <button type="submit" form="category-form" class="btn btn-modern btn-primary">
                        <i class="fas fa-save me-2"></i>
                        <?= isset($category) ? 'Cập nhật' : 'Lưu Danh mục' ?>
                    </button>
                </div>
            </div>
        </header>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-modern alert-success alert-dismissible fade show" role="alert">
                <div class="alert-content">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <span><?= $_SESSION['success'] ?></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

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

        <!-- Category Form -->
        <section class="form-section">
            <div class="form-container">
                <form method="POST" action="<?= BASE_URL_ADMIN ?>&action=tours_categories/<?= isset($category) ? 'update' : 'store' ?>" id="category-form">
                    <?php if (isset($category)): ?>
                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                    <?php endif; ?>

                    <!-- Basic Information -->
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3 class="form-card-title">
                                <i class="fas fa-info-circle"></i>
                                Thông tin cơ bản
                            </h3>
                            <p class="form-card-description">
                                Các thông tin bắt buộc cho danh mục tour
                            </p>
                        </div>
                        <div class="form-card-body">
                            <!-- Name -->
                            <div class="form-group mb-4">
                                <label for="name" class="form-label">
                                    <i class="fas fa-tag me-2"></i>
                                    Tên danh mục <span class="text-danger">*</span>
                                </label>
                                <div class="input-group-modern">
                                    <input type="text" class="form-control form-control-modern" id="name" name="name"
                                        value="<?= htmlspecialchars(isset($category) ? ($category['name'] ?? '') : ($_SESSION['old_input']['name'] ?? '')) ?>"
                                        placeholder="Nhập tên danh mục tour" required>
                                    <div class="input-group-icon">
                                        <i class="fas fa-folder"></i>
                                    </div>
                                </div>
                                <?php if (isset($_SESSION['form_errors']['name'])): ?>
                                    <div class="form-error">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <?= $_SESSION['form_errors']['name'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Slug -->
                            <div class="form-group mb-4">
                                <label for="slug" class="form-label">
                                    <i class="fas fa-link me-2"></i>
                                    Slug (URL thân thiện)
                                </label>
                                <div class="input-group-modern">
                                    <input type="text" class="form-control form-control-modern" id="slug" name="slug"
                                        value="<?= htmlspecialchars(isset($category) ? ($category['slug'] ?? '') : ($_SESSION['old_input']['slug'] ?? '')) ?>"
                                        placeholder="tour-trong-nuoc">
                                    <div class="input-group-icon">
                                        <i class="fas fa-globe"></i>
                                    </div>
                                </div>
                                <div class="form-help">
                                    <i class="fas fa-info-circle"></i>
                                    Slug sẽ được dùng trong URL. Nếu để trống, hệ thống sẽ tự động tạo từ tên danh mục.
                                </div>
                                <?php if (isset($_SESSION['form_errors']['slug'])): ?>
                                    <div class="form-error">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <?= $_SESSION['form_errors']['slug'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Icon -->
                            <div class="form-group mb-4">
                                <label for="icon" class="form-label">
                                    <i class="fas fa-icons me-2"></i>
                                    Icon (FontAwesome)
                                </label>
                                <div class="input-group-modern">
                                    <input type="text" class="form-control form-control-modern" id="icon" name="icon"
                                        value="<?= htmlspecialchars(isset($category) ? ($category['icon'] ?? '') : ($_SESSION['old_input']['icon'] ?? '')) ?>"
                                        placeholder="fas fa-folder">
                                    <div class="input-group-icon">
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                                <div class="form-help">
                                    <i class="fas fa-info-circle"></i>
                                    Nhập class FontAwesome (ví dụ: fas fa-folder, fas fa-globe, fas fa-map-marked-alt)
                                </div>
                                <div class="icon-preview mt-3">
                                    <label class="form-label">Preview:</label>
                                    <div class="preview-box">
                                        <i id="icon-preview" class="<?= !empty($category['icon'] ?? '') ? htmlspecialchars($category['icon']) : 'fas fa-folder' ?>"></i>
                                        <span id="icon-text"><?= !empty($category['icon'] ?? '') ? htmlspecialchars($category['icon']) : 'fas fa-folder' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3 class="form-card-title">
                                <i class="fas fa-align-left"></i>
                                Thông tin bổ sung
                            </h3>
                            <p class="form-card-description">
                                Mô tả chi tiết về danh mục tour
                            </p>
                        </div>
                        <div class="form-card-body">
                            <!-- Description -->
                            <div class="form-group">
                                <label for="description" class="form-label">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Mô tả danh mục
                                </label>
                                <div class="textarea-modern">
                                    <textarea class="form-control form-control-modern" id="description" name="description" rows="5"
                                        placeholder="Nhập mô tả chi tiết cho danh mục tour..."><?= htmlspecialchars(isset($category) ? ($category['description'] ?? '') : ($_SESSION['old_input']['description'] ?? '')) ?></textarea>
                                    <div class="textarea-counter">
                                        <span id="char-count">0</span> / 500 ký tự
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <div class="actions-left">
                            <a href="<?= BASE_URL_ADMIN ?>&action=tours_categories" class="btn btn-modern btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Quay lại danh sách
                            </a>
                        </div>
                        <div class="actions-right">
                            <button type="button" class="btn btn-modern btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-redo me-2"></i>
                                Reset form
                            </button>
                            <button type="submit" class="btn btn-modern btn-primary">
                                <i class="fas fa-save me-2"></i>
                                <?= isset($category) ? 'Cập nhật danh mục' : 'Tạo danh mục mới' ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
</main>

<style>
    /* Form Styles - Sync with tours */
    .form-section {
        margin-bottom: 32px;
    }

    .form-container {
        max-width: auto;
        margin: 0 auto;
    }

    .form-card {
        background: var(--tours-bg-primary, #ffffff);
        border-radius: var(--tours-radius-lg, 12px);
        box-shadow: var(--tours-shadow, 0 4px 12px rgba(0, 0, 0, 0.08));
        border: 1px solid var(--tours-border-light, #e9ecef);
        margin-bottom: 24px;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .form-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--tours-shadow-lg, 0 8px 24px rgba(0, 0, 0, 0.12));
    }

    .form-card-header {
        background: linear-gradient(135deg, var(--tours-bg-secondary, #f8f9fa), #e9ecef);
        padding: 24px 32px;
        border-bottom: 1px solid var(--tours-border-light, #e9ecef);
    }

    .form-card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--tours-text-primary, #212529);
        margin: 0 0 8px 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .form-card-title i {
        color: var(--tours-primary, #0d6efd);
    }

    .form-card-description {
        color: var(--tours-text-secondary, #6c757d);
        margin: 0;
        font-size: 0.9rem;
    }

    .form-card-body {
        padding: 32px;
    }

    .form-group {
        position: relative;
    }

    .form-label {
        font-weight: 600;
        color: var(--tours-text-primary, #212529);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
    }

    .form-label i {
        color: var(--tours-primary, #0d6efd);
        font-size: 0.9rem;
    }

    .input-group-modern {
        position: relative;
    }

    .form-control-modern {
        border: 2px solid var(--tours-border, #dee2e6);
        border-radius: var(--tours-radius, 8px);
        padding: 12px 16px;
        font-size: 1rem;
        transition: var(--tours-transition, all 0.3s ease);
        background: var(--tours-bg-primary, #ffffff);
    }

    .form-control-modern:focus {
        border-color: var(--tours-primary, #0d6efd);
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        outline: none;
    }

    .input-group-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--tours-text-muted, #adb5bd);
        pointer-events: none;
        transition: color 0.3s ease;
    }

    .form-control-modern:focus+.input-group-icon {
        color: var(--tours-primary, #0d6efd);
    }

    .form-help {
        margin-top: 8px;
        font-size: 0.85rem;
        color: var(--tours-text-secondary, #6c757d);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-help i {
        color: var(--tours-info, #0dcaf0);
    }

    .form-error {
        margin-top: 8px;
        padding: 8px 12px;
        background: rgba(220, 53, 69, 0.1);
        border-left: 4px solid var(--tours-danger, #dc3545);
        border-radius: 4px;
        color: var(--tours-danger, #dc3545);
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .icon-preview {
        margin-top: 16px;
    }

    .preview-box {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: var(--tours-bg-secondary, #f8f9fa);
        border: 1px solid var(--tours-border, #dee2e6);
        border-radius: var(--tours-radius, 8px);
    }

    .preview-box i {
        font-size: 1.5rem;
        color: var(--tours-primary, #0d6efd);
    }

    .preview-box span {
        font-family: 'Courier New', monospace;
        color: var(--tours-text-secondary, #6c757d);
        font-size: 0.9rem;
    }

    .textarea-modern {
        position: relative;
    }

    .textarea-counter {
        position: absolute;
        bottom: 12px;
        right: 16px;
        font-size: 0.8rem;
        color: var(--tours-text-muted, #adb5bd);
        background: var(--tours-bg-primary, #ffffff);
        padding: 2px 6px;
        border-radius: 4px;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 24px 32px;
        background: var(--tours-bg-primary, #ffffff);
        border-top: 1px solid var(--tours-border-light, #e9ecef);
        border-radius: 0 0 var(--tours-radius-lg, 12px) var(--tours-radius-lg, 12px);
    }

    .actions-left,
    .actions-right {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    /* Button Styles */
    .btn-modern {
        border-radius: var(--tours-radius, 8px);
        padding: 12px 24px;
        font-weight: 600;
        border: none;
        transition: var(--tours-transition, all 0.3s ease);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: var(--tours-shadow, 0 4px 12px rgba(0, 0, 0, 0.15));
    }

    .btn-modern.btn-primary {
        background: linear-gradient(135deg, var(--tours-primary, #0d6efd), #0b5ed7);
        color: white;
    }

    .btn-modern.btn-secondary {
        background: var(--tours-bg-secondary, #f8f9fa);
        color: var(--tours-text-secondary, #6c757d);
        border: 1px solid var(--tours-border, #dee2e6);
    }

    .btn-modern.btn-outline-secondary {
        background: transparent;
        color: var(--tours-text-secondary, #6c757d);
        border: 2px solid var(--tours-border, #dee2e6);
    }

    .btn-modern.btn-outline-secondary:hover {
        background: var(--tours-bg-secondary, #f8f9fa);
        border-color: var(--tours-text-secondary, #6c757d);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-container {
            margin: 0 16px;
        }

        .form-card-body {
            padding: 24px 20px;
        }

        .form-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .actions-left,
        .actions-right {
            justify-content: center;
        }

        .btn-modern {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Icon preview
        const iconInput = document.getElementById('icon');
        const iconPreview = document.getElementById('icon-preview');
        const iconText = document.getElementById('icon-text');

        if (iconInput && iconPreview) {
            iconInput.addEventListener('input', function() {
                const iconClass = this.value.trim();
                if (iconClass) {
                    iconPreview.className = iconClass;
                    iconText.textContent = iconClass;
                } else {
                    iconPreview.className = 'fas fa-folder';
                    iconText.textContent = 'fas fa-folder';
                }
            });
        }

        // Character counter
        const descriptionTextarea = document.getElementById('description');
        const charCount = document.getElementById('char-count');

        if (descriptionTextarea && charCount) {
            function updateCharCount() {
                const length = descriptionTextarea.value.length;
                charCount.textContent = length;

                if (length > 500) {
                    charCount.style.color = 'var(--tours-danger, #dc3545)';
                } else if (length > 400) {
                    charCount.style.color = 'var(--tours-warning, #ffc107)';
                } else {
                    charCount.style.color = 'var(--tours-text-muted, #adb5bd)';
                }
            }

            descriptionTextarea.addEventListener('input', updateCharCount);
            updateCharCount(); // Initial count
        }

        // Auto-generate slug from name
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function() {
                if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
                    const slug = this.value.toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .replace(/^-|-$/g, '');

                    slugInput.value = slug;
                    slugInput.dataset.autoGenerated = 'true';
                }
            });

            slugInput.addEventListener('input', function() {
                this.dataset.autoGenerated = 'false';
            });
        }
    });

    function resetForm() {
        if (confirm('Bạn có chắc muốn reset toàn bộ form?')) {
            document.getElementById('category-form').reset();

            // Reset icon preview
            const iconPreview = document.getElementById('icon-preview');
            const iconText = document.getElementById('icon-text');
            if (iconPreview && iconText) {
                iconPreview.className = 'fas fa-folder';
                iconText.textContent = 'fas fa-folder';
            }

            // Reset character counter
            const charCount = document.getElementById('char-count');
            if (charCount) {
                charCount.textContent = '0';
                charCount.style.color = 'var(--tours-text-muted, #adb5bd)';
            }
        }
    }
</script>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
unset($_SESSION['form_errors']);
unset($_SESSION['old_input']);
?>