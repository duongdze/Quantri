<?php
$isEdit    = isset($post) && !empty($post['id']);
$formTitle = $isEdit ? 'Chỉnh sửa bài viết' : 'Tạo bài viết mới';
$action    = $isEdit ? BASE_URL_ADMIN . '&action=posts/update' : BASE_URL_ADMIN . '&action=posts/store';

include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>
<main class="dashboard">
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-modern">
                        <a href="<?= BASE_URL_ADMIN ?>&action=/" class="breadcrumb-link"><i class="fas fa-home"></i><span>Dashboard</span></a>
                        <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
                        <a href="<?= BASE_URL_ADMIN ?>&action=posts" class="breadcrumb-link"><span>Bài Viết</span></a>
                        <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
                        <span class="breadcrumb-current"><?= $formTitle ?></span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-<?= $isEdit ? 'edit' : 'plus-circle' ?> title-icon"></i>
                            <?= $formTitle ?>
                        </h1>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=posts" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
            </div>
        </header>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?php unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= $action ?>" enctype="multipart/form-data">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $post['id'] ?>">
            <?php endif; ?>

            <div class="row g-4">
                <!-- Main content -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0"><i class="fas fa-pen me-2"></i>Nội dung bài viết</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tiêu đề <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" name="title"
                                       value="<?= htmlspecialchars($post['title'] ?? '') ?>"
                                       placeholder="Nhập tiêu đề bài viết…" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mô tả ngắn (Excerpt)</label>
                                <textarea class="form-control" name="excerpt" rows="3"
                                          placeholder="Tóm tắt nội dung bài viết, hiển thị ở trang danh sách…"><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nội dung <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="content" id="post-content" rows="18"
                                          placeholder="Nhập nội dung bài viết…" required><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
                                <div class="form-text">Hỗ trợ HTML cơ bản.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar settings -->
                <div class="col-lg-4">
                    <!-- Publish settings -->
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0"><i class="fas fa-cog me-2"></i>Cài đặt</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Trạng thái</label>
                                <select class="form-select" name="status">
                                    <option value="draft"     <?= ($post['status'] ?? 'draft') === 'draft'     ? 'selected' : '' ?>>📝 Nháp</option>
                                    <option value="published" <?= ($post['status'] ?? '')      === 'published' ? 'selected' : '' ?>>✅ Đăng ngay</option>
                                    <option value="archived"  <?= ($post['status'] ?? '')      === 'archived'  ? 'selected' : '' ?>>📦 Lưu trữ</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="featured" id="featured"
                                           value="1" <?= !empty($post['featured']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="featured">
                                        <i class="fas fa-star text-warning me-1"></i>Bài viết nổi bật
                                    </label>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>
                                    <?= $isEdit ? 'Cập nhật bài viết' : 'Đăng bài viết' ?>
                                </button>
                                <a href="<?= BASE_URL_ADMIN ?>&action=posts" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Hủy
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Thumbnail -->
                    <div class="card">
                        <div class="card-header"><h5 class="mb-0"><i class="fas fa-image me-2"></i>Ảnh đại diện</h5></div>
                        <div class="card-body">
                            <?php if (!empty($post['thumbnail'])): ?>
                                <div class="mb-2">
                                    <img src="<?= BASE_ASSETS_UPLOADS . $post['thumbnail'] ?>" alt="Thumbnail hiện tại"
                                         class="img-fluid rounded mb-2" style="max-height:200px;object-fit:cover;width:100%">
                                    <p class="text-muted small mb-0">Ảnh hiện tại – tải ảnh mới để thay thế</p>
                                </div>
                            <?php endif; ?>
                            <div id="thumb-preview-wrapper" class="mb-2" style="display:none">
                                <img id="thumb-preview" src="" alt="Preview" class="img-fluid rounded" style="max-height:200px;object-fit:cover;width:100%">
                            </div>
                            <input type="file" class="form-control" name="thumbnail" accept="image/*" id="thumbnailInput">
                            <div class="form-text">JPG, PNG, WebP – tối đa 5MB</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
document.getElementById('thumbnailInput')?.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('thumb-preview').src = e.target.result;
        document.getElementById('thumb-preview-wrapper').style.display = 'block';
    };
    reader.readAsDataURL(file);
});
</script>

<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>
