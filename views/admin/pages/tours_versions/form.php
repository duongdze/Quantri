<?php
$version = $version ?? null;
$isEdit = isset($version);
$formAction = $isEdit
    ? "?mode=admin&action=tours_versions/update&id={$version['id']}"
    : "?mode=admin&action=tours_versions/store";
$title = $isEdit ? 'Chỉnh sửa phiên bản' : 'Thêm phiên bản mới';

// Get old input or use version data
$formData = $_SESSION['old_input'] ?? ($version ?? []);
$formErrors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['old_input'], $_SESSION['form_errors']);
?>

<?php include_once PATH_VIEW_ADMIN . 'default/header.php'; ?>
<?php include_once PATH_VIEW_ADMIN . 'default/sidebar.php'; ?>

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
                        <a href="<?= BASE_URL_ADMIN ?>&action=tours_versions" class="breadcrumb-link">
                            <span>Phiên bản Tour</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current"><?= $isEdit ? 'Chỉnh sửa' : 'Thêm mới' ?></span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-<?= $isEdit ? 'edit' : 'plus-circle' ?> title-icon"></i>
                            <?= $title ?>
                        </h1>
                        <p class="page-subtitle">
                            <?= $isEdit ? 'Cập nhật thông tin phiên bản tour' : 'Tạo phiên bản tour mới cho hệ thống' ?>
                        </p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=tours_versions" class="btn btn-modern btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Quay lại
                    </a>
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

        <!-- Form Section -->
        <section class="form-section">
            <div class="form-container-enhanced">
                <form method="POST" action="<?= $formAction ?>" class="form-modern" id="versionForm">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= $version['id'] ?>">
                        <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <div class="form-grid">
                        <!-- Basic Information -->
                        <div class="form-section-group">
                            <div class="section-subtitle">
                                <h3 class="section-subtitle-text">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Thông tin cơ bản
                                </h3>
                                <div class="section-subtitle-description">
                                    Các thông tin chính của phiên bản tour
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group form-group-lg">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-tag me-1"></i>
                                        Tên phiên bản <span class="required">*</span>
                                    </label>
                                    <div class="form-input-wrapper">
                                        <input type="text"
                                            class="form-control form-control-modern <?= isset($formErrors['name']) ? 'is-invalid' : '' ?>"
                                            id="name"
                                            name="name"
                                            value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                                            placeholder="Ví dụ: Mùa hè 2023, Giáng sinh 2023, v.v."
                                            required>
                                        <div class="form-input-icon">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                    </div>
                                    <?php if (isset($formErrors['name'])): ?>
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            <?= $formErrors['name'] ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="form-help">
                                            <i class="fas fa-lightbulb me-1"></i>
                                            Đặt tên phiên bản dễ nhận biết, ví dụ: "Mùa hè 2023" hoặc "Giáng sinh đặc biệt"
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-toggle-on me-1"></i>
                                        Trạng thái
                                    </label>
                                    <div class="form-input-wrapper">
                                        <select class="form-select form-select-modern" id="status" name="status">
                                            <option value="active" <?= ($formData['status'] ?? '') === 'active' ? 'selected' : '' ?>>
                                                <i class="fas fa-check-circle me-1"></i>
                                                Đang hoạt động
                                            </option>
                                            <option value="inactive" <?= ($formData['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>
                                                <i class="fas fa-pause-circle me-1"></i>
                                                Tạm ẩn
                                            </option>
                                        </select>
                                        <div class="form-input-icon">
                                            <i class="fas fa-toggle-on"></i>
                                        </div>
                                    </div>
                                    <div class="form-help">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Chọn trạng thái để hiển thị hoặc ẩn phiên bản
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-section-group">
                            <div class="section-subtitle">
                                <h3 class="section-subtitle-text">
                                    <i class="fas fa-align-left me-2"></i>
                                    Mô tả chi tiết
                                </h3>
                                <div class="section-subtitle-description">
                                    Mô tả các đặc điểm và thông tin quan trọng về phiên bản
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description" class="form-label">
                                    <i class="fas fa-file-alt me-1"></i>
                                    Mô tả phiên bản
                                </label>
                                <div class="form-textarea-wrapper">
                                    <textarea class="form-control form-control-modern"
                                        id="description"
                                        name="description"
                                        rows="5"
                                        placeholder="Nhập mô tả chi tiết về phiên bản này..."><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
                                    <div class="form-textarea-counter">
                                        <span id="descCounter">0</span> / 1000 ký tự
                                    </div>
                                </div>
                                <div class="form-help">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    Mô tả các thay đổi, đặc điểm nổi bật hoặc thông tin quan trọng của phiên bản
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Section -->
                        <div class="form-section-group">
                            <div class="section-subtitle">
                                <h3 class="section-subtitle-text">
                                    <i class="fas fa-percent me-2"></i>
                                    Giá theo Phiên Bản
                                </h3>
                                <div class="section-subtitle-description">
                                    Nhập % tăng/giảm so với giá gốc của tour. Ví dụ: +20 (tăng 20%), -15 (giảm 15%)
                                </div>
                            </div>

                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Lưu ý:</strong> Giá sẽ được tính dựa trên % tăng/giảm so với giá gốc của tour.
                                Để giữ nguyên giá gốc, nhập 0%.
                            </div>

                            <div class="form-row">
                                <!-- % Người Lớn -->
                                <div class="form-group">
                                    <label for="adult_percent" class="form-label">
                                        <i class="fas fa-user me-1"></i>
                                        Người lớn (%)
                                    </label>
                                    <div class="form-input-wrapper">
                                        <input type="number"
                                            class="form-control form-control-modern"
                                            id="adult_percent"
                                            name="adult_percent"
                                            value="<?= htmlspecialchars($prices['adult_percent'] ?? 0) ?>"
                                            step="0.01"
                                            placeholder="0">
                                        <div class="form-input-icon">
                                            <i class="fas fa-percent"></i>
                                        </div>
                                    </div>
                                    <div class="form-help">
                                        <i class="fas fa-info-circle me-1"></i>
                                        % tăng/giảm so với giá gốc tour
                                    </div>
                                </div>

                                <!-- % Trẻ Em -->
                                <div class="form-group">
                                    <label for="child_percent" class="form-label">
                                        <i class="fas fa-child me-1"></i>
                                        Trẻ em (%)
                                    </label>
                                    <div class="form-input-wrapper">
                                        <input type="number"
                                            class="form-control form-control-modern"
                                            id="child_percent"
                                            name="child_percent"
                                            value="<?= htmlspecialchars($prices['child_percent'] ?? 0) ?>"
                                            step="0.01"
                                            placeholder="0">
                                        <div class="form-input-icon">
                                            <i class="fas fa-percent"></i>
                                        </div>
                                    </div>
                                    <div class="form-help">
                                        <i class="fas fa-info-circle me-1"></i>
                                        % tăng/giảm so với 75% giá người lớn
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <!-- % Em Bé -->
                                <div class="form-group">
                                    <label for="infant_percent" class="form-label">
                                        <i class="fas fa-baby me-1"></i>
                                        Em bé (%)
                                    </label>
                                    <div class="form-input-wrapper">
                                        <input type="number"
                                            class="form-control form-control-modern"
                                            id="infant_percent"
                                            name="infant_percent"
                                            value="<?= htmlspecialchars($prices['infant_percent'] ?? 0) ?>"
                                            step="0.01"
                                            placeholder="0">
                                        <div class="form-input-icon">
                                            <i class="fas fa-percent"></i>
                                        </div>
                                    </div>
                                    <div class="form-help">
                                        <i class="fas fa-info-circle me-1"></i>
                                        % tăng/giảm so với 50% giá người lớn
                                    </div>
                                </div>

                                <!-- Tỷ lệ Base Trẻ Em -->
                                <div class="form-group">
                                    <label for="child_base_percent" class="form-label">
                                        <i class="fas fa-percentage me-1"></i>
                                        Tỷ lệ giá trẻ em (%)
                                    </label>
                                    <div class="form-input-wrapper">
                                        <input type="number"
                                            class="form-control form-control-modern"
                                            id="child_base_percent"
                                            name="child_base_percent"
                                            value="<?= htmlspecialchars($prices['child_base_percent'] ?? 75) ?>"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            placeholder="75">
                                        <div class="form-input-icon">
                                            <i class="fas fa-percentage"></i>
                                        </div>
                                    </div>
                                    <div class="form-help">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Trẻ em = % giá người lớn (mặc định 75%)
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <!-- Tỷ lệ Base Em Bé -->
                                <div class="form-group">
                                    <label for="infant_base_percent" class="form-label">
                                        <i class="fas fa-percentage me-1"></i>
                                        Tỷ lệ giá em bé (%)
                                    </label>
                                    <div class="form-input-wrapper">
                                        <input type="number"
                                            class="form-control form-control-modern"
                                            id="infant_base_percent"
                                            name="infant_base_percent"
                                            value="<?= htmlspecialchars($prices['infant_base_percent'] ?? 50) ?>"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            placeholder="50">
                                        <div class="form-input-icon">
                                            <i class="fas fa-percentage"></i>
                                        </div>
                                    </div>
                                    <div class="form-help">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Em bé = % giá người lớn (mặc định 50%)
                                    </div>
                                </div>

                                <div class="form-group">
                                    <!-- Empty space for alignment -->
                                </div>
                            </div>
                        </div>
                        <!-- Preview Section -->
                        <div class="form-section-group">
                            <div class="section-subtitle">
                                <h3 class="section-subtitle-text">
                                    <i class="fas fa-eye me-2"></i>
                                    Xem trước
                                </h3>
                                <div class="section-subtitle-description">
                                    Xem trước thông tin phiên bản sẽ hiển thị
                                </div>
                            </div>

                            <div class="preview-card-modern">
                                <div class="preview-header">
                                    <div class="preview-info">
                                        <h4 class="preview-name" id="preview-name"><?= htmlspecialchars($formData['name'] ?? 'Tên phiên bản') ?></h4>
                                        <div class="preview-meta">
                                            <span class="preview-id">#<?= str_pad($version['id'] ?? '0000', 4, '0', STR_PAD_LEFT) ?></span>
                                            <span class="preview-date">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                <?= date('d/m/Y') ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="preview-status">
                                        <span class="status-badge status-<?= ($formData['status'] ?? 'inactive') ?>" id="preview-status">
                                            <i class="fas fa-<?= ($formData['status'] ?? 'inactive') === 'active' ? 'check' : 'pause' ?> me-1"></i>
                                            <?= ($formData['status'] ?? 'inactive') === 'active' ? 'Đang hoạt động' : 'Tạm ẩn' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="preview-body">
                                    <div class="preview-description">
                                        <p id="preview-description">
                                            <?= !empty($formData['description']) ? nl2br(htmlspecialchars($formData['description'])) : 'Chưa có mô tả' ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <div class="form-actions-left">
                            <a href="<?= BASE_URL_ADMIN ?>&action=tours_versions" class="btn btn-modern btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Quay lại
                            </a>
                        </div>
                        <div class="form-actions-right">
                            <a href="<?= BASE_URL_ADMIN ?>&action=tours_versions" class="btn btn-modern btn-outline-secondary me-2">
                                <i class="fas fa-times me-2"></i>
                                Hủy
                            </a>
                            <button type="submit" class="btn btn-modern btn-primary" id="submitBtn">
                                <i class="fas fa-save me-2"></i>
                                <span id="submitText"><?= $isEdit ? 'Cập nhật' : 'Tạo mới' ?></span>
                            </button>
                        </div>
                    </div>

                    <!-- Auto-save Status -->
                    <div class="auto-save-status" id="autoSaveStatus">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        <span class="auto-save-text">Đã tự động lưu</span>
                    </div>
                </form>
            </div>
        </section>
    </div>
</main>

<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form elements
        const form = document.getElementById('versionForm');
        const nameInput = document.getElementById('name');
        const statusSelect = document.getElementById('status');
        const descriptionTextarea = document.getElementById('description');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');

        // Preview elements
        const previewName = document.getElementById('preview-name');
        const previewStatus = document.getElementById('preview-status');
        const previewDescription = document.getElementById('preview-description');

        // Auto-save elements
        const autoSaveStatus = document.getElementById('autoSaveStatus');
        const autoSaveText = autoSaveStatus.querySelector('.auto-save-text');

        // Progress elements
        const formProgress = document.createElement('div');
        formProgress.className = 'form-progress';
        const progressBar = document.createElement('div');
        progressBar.className = 'form-progress-bar';
        formProgress.appendChild(progressBar);
        form.parentElement.insertBefore(formProgress, form);

        // Keyboard hint
        const keyboardHint = document.createElement('div');
        keyboardHint.className = 'keyboard-hint';
        keyboardHint.innerHTML = 'Press <kbd>Ctrl+S</kbd> to save • <kbd>Ctrl+Enter</kbd> to submit • <kbd>Esc</kbd> to cancel';
        document.body.appendChild(keyboardHint);

        // Auto-save functionality
        let autoSaveTimeout;
        let formData = {};
        const AUTO_SAVE_DELAY = 2000; // 2 seconds

        function saveFormData() {
            formData = {
                name: nameInput.value,
                status: statusSelect.value,
                description: descriptionTextarea.value
            };
            localStorage.setItem('versionFormDraft', JSON.stringify(formData));
        }

        function loadFormData() {
            const saved = localStorage.getItem('versionFormDraft');
            if (saved) {
                formData = JSON.parse(saved);
                if (!nameInput.value && formData.name) {
                    nameInput.value = formData.name;
                    updatePreview();
                }
                if (!statusSelect.value && formData.status) {
                    statusSelect.value = formData.status;
                    updatePreview();
                }
                if (!descriptionTextarea.value && formData.description) {
                    descriptionTextarea.value = formData.description;
                    updatePreview();
                    updateCounter();
                }
            }
        }

        function showAutoSaveStatus(type, message) {
            autoSaveStatus.className = `auto-save-status show ${type}`;
            autoSaveText.textContent = message;

            setTimeout(() => {
                autoSaveStatus.classList.remove('show');
            }, 3000);
        }

        function autoSave() {
            clearTimeout(autoSaveTimeout);
            autoSaveStatus.classList.add('saving');
            autoSaveText.textContent = 'Đang lưu...';

            autoSaveTimeout = setTimeout(() => {
                saveFormData();
                showAutoSaveStatus('success', 'Đã tự động lưu');
            }, AUTO_SAVE_DELAY);
        }

        // Progress calculation
        function updateProgress() {
            const fields = [nameInput, statusSelect, descriptionTextarea];
            let completed = 0;

            fields.forEach(field => {
                if (field.value.trim()) completed++;
            });

            const progress = (completed / fields.length) * 100;
            progressBar.style.width = progress + '%';
        }

        // Real-time preview
        function updatePreview() {
            // Update name
            previewName.textContent = nameInput.value || 'Tên phiên bản';

            // Update status
            const status = statusSelect.value;
            previewStatus.innerHTML = `
                <i class="fas fa-${status === 'active' ? 'check' : 'pause'} me-1"></i>
                ${status === 'active' ? 'Đang hoạt động' : 'Tạm ẩn'}
            `;
            previewStatus.className = 'status-badge status-' + status;

            // Update description
            const description = descriptionTextarea.value;
            previewDescription.innerHTML = description ?
                description.replace(/\n/g, '<br>') : 'Chưa có mô tả';

            updateProgress();
        }

        // Character counter
        const descCounter = document.getElementById('descCounter');

        function updateCounter() {
            const length = descriptionTextarea.value.length;
            descCounter.textContent = length;

            // Update counter color based on length
            descCounter.parentElement.classList.remove('warning', 'danger');
            if (length > 1000) {
                descCounter.parentElement.classList.add('danger');
            } else if (length > 800) {
                descCounter.parentElement.classList.add('warning');
            }
        }

        // Enhanced validation
        function validateField(field) {
            const wrapper = field.closest('.form-input-wrapper, .form-textarea-wrapper');
            const feedback = wrapper.parentElement.querySelector('.invalid-feedback');

            if (field.hasAttribute('required') && !field.value.trim()) {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
                return false;
            }

            if (field.id === 'name' && field.value.trim()) {
                if (field.value.trim().length < 2) {
                    field.classList.add('is-invalid');
                    field.classList.remove('is-valid');
                    if (feedback) {
                        feedback.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>Tên phiên bản phải có ít nhất 2 ký tự';
                    }
                    return false;
                }
            }

            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            return true;
        }

        function validateForm() {
            const fields = [nameInput, statusSelect, descriptionTextarea];
            let isValid = true;

            fields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });

            return isValid;
        }

        // Form submission - NO VALIDATION BLOCKING
        form.addEventListener('submit', function(event) {
            // Show loading state only
            submitBtn.classList.add('loading');
            submitText.textContent = 'Đang xử lý...';

            // Clear draft on submission
            localStorage.removeItem('versionFormDraft');

            // Let form submit normally
        });

        // Event listeners
        nameInput.addEventListener('input', () => {
            updatePreview();
            validateField(nameInput);
            autoSave();
        });

        statusSelect.addEventListener('change', () => {
            updatePreview();
            validateField(statusSelect);
            autoSave();
        });

        descriptionTextarea.addEventListener('input', () => {
            updatePreview();
            updateCounter();
            autoSave();
        });

        // Field focus/blur events
        [nameInput, statusSelect, descriptionTextarea].forEach(field => {
            field.addEventListener('blur', () => validateField(field));
            field.addEventListener('focus', () => {
                field.classList.remove('is-invalid');
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+S: Save draft
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                saveFormData();
                showAutoSaveStatus('success', 'Đã lưu nháp');
            }

            // Ctrl+Enter: Submit form
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }

            // Esc: Cancel
            if (e.key === 'Escape') {
                if (confirm('Bạn có chắc muốn hủy và quay lại?')) {
                    window.location.href = '<?= BASE_URL_ADMIN ?>&action=tours_versions';
                }
            }
        });

        // Show keyboard hint on focus
        let hintTimeout;
        [nameInput, descriptionTextarea].forEach(field => {
            field.addEventListener('focus', () => {
                keyboardHint.classList.add('show');
                clearTimeout(hintTimeout);
                hintTimeout = setTimeout(() => {
                    keyboardHint.classList.remove('show');
                }, 3000);
            });
        });

        // Character limit enforcement
        descriptionTextarea.addEventListener('input', function() {
            if (this.value.length > 1000) {
                this.value = this.value.substring(0, 1000);
                updateCounter();
            }
        });

        // Initialize
        loadFormData();
        updatePreview();
        updateCounter();
        updateProgress();

        // Show initial keyboard hint
        setTimeout(() => {
            keyboardHint.classList.add('show');
            setTimeout(() => {
                keyboardHint.classList.remove('show');
            }, 5000);
        }, 1000);

        // Warn before leaving if form has unsaved changes
        let hasChanges = false;
        form.addEventListener('change', () => {
            hasChanges = true;
        });

        window.addEventListener('beforeunload', function(e) {
            if (hasChanges && !submitBtn.classList.contains('loading')) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });

        // Clear changes flag after successful submission
        form.addEventListener('submit', () => {
            hasChanges = false;
        });
    });
</script>