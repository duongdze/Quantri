<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>
<main class="dashboard">
    <div class="dashboard-container">
        <!-- Modern Header -->
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
                        <span class="breadcrumb-current">Quản lý Chính sách</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-shield-alt title-icon"></i>
                            Quản lý Chính sách
                        </h1>
                        <p class="page-subtitle">Quản lý các chính sách áp dụng cho tour</p>
                    </div>
                </div>
                <div class="header-right">
                    <button class="btn btn-modern btn-primary btn-lg" onclick="window.location.href='<?= BASE_URL_ADMIN . '&action=policies/create' ?>'">
                        <i class="fas fa-plus-circle me-2"></i>
                        Thêm Chính sách Mới
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

        <!-- Statistics Cards -->
        <section class="stats-section">
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format(count($policies)) ?></div>
                        <div class="stat-label">Tổng Chính sách</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-route"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">
                            <?php 
                            $totalTours = array_sum(array_column($policies, 'tour_count'));
                            echo number_format($totalTours);
                            ?>
                        </div>
                        <div class="stat-label">Tour Áp dụng</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Policies List Section -->
        <section class="tours-section">
            <div class="tours-header">
                <div class="tours-info">
                    <div class="select-all-wrapper">
                        <i class="fas fa-list"></i>
                        <label class="select-all-label">Danh sách Chính sách</label>
                    </div>
                </div>
            </div>

            <div class="tours-container">
                <?php if (!empty($policies)) : ?>
                    <div class="row g-4">
                        <?php foreach ($policies as $policy) : ?>
                            <div class="col-12 col-lg-6">
                                <div class="card policy-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <h5 class="card-title mb-2">
                                                    <i class="fas fa-shield-alt text-primary me-2"></i>
                                                    <?= htmlspecialchars($policy['name']) ?>
                                                </h5>
                                                <p class="card-text text-muted small mb-2">
                                                    <i class="fas fa-link me-1"></i>
                                                    <code><?= htmlspecialchars($policy['slug']) ?></code>
                                                </p>
                                                <p class="card-text">
                                                    <?= nl2br(htmlspecialchars($policy['description'] ?? '')) ?>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="policy-meta">
                                            <span class="badge bg-info">
                                                <i class="fas fa-route me-1"></i>
                                                <?= $policy['tour_count'] ?> tour
                                            </span>
                                            <span class="text-muted small">
                                                <i class="fas fa-clock me-1"></i>
                                                <?= date('d/m/Y', strtotime($policy['created_at'])) ?>
                                            </span>
                                        </div>

                                        <div class="card-actions mt-3">
                                            <a href="<?= BASE_URL_ADMIN . '&action=policies/edit&id=' . $policy['id'] ?>" 
                                               class="btn btn-sm btn-primary" 
                                               data-bs-toggle="tooltip" 
                                               title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger delete-policy" 
                                                    data-id="<?= $policy['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($policy['name']) ?>"
                                                    data-tour-count="<?= $policy['tour_count'] ?>"
                                                    data-bs-toggle="tooltip" 
                                                    title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="empty-title">Chưa có chính sách nào</h3>
                        <p class="empty-description">
                            Bắt đầu bằng cách tạo chính sách đầu tiên cho tour của bạn.
                        </p>
                        <button class="btn btn-primary" onclick="window.location.href='<?= BASE_URL_ADMIN . '&action=policies/create' ?>'">
                            <i class="fas fa-plus me-2"></i>
                            Tạo chính sách đầu tiên
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Xác nhận xóa chính sách
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Bạn có chắc chắn muốn xóa chính sách "<span id="delete-policy-name"></span>"?
                </div>
                <p class="text-muted small" id="tour-count-warning" style="display: none;">
                    <i class="fas fa-info-circle me-1"></i>
                    <span id="tour-count-text"></span>
                </p>
                <p class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Hành động này không thể hoàn tác.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>
                    Hủy
                </button>
                <button type="button" class="btn btn-danger">
                    <i class="fas fa-trash me-2"></i>
                    Xóa
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Delete policy functionality
        const deleteButtons = document.querySelectorAll('.delete-policy');
        const deleteModalEl = document.getElementById('deleteModal');
        const deleteModal = new bootstrap.Modal(deleteModalEl);
        const deletePolicyName = document.getElementById('delete-policy-name');
        const tourCountWarning = document.getElementById('tour-count-warning');
        const tourCountText = document.getElementById('tour-count-text');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const tourCount = parseInt(this.dataset.tourCount);

                deletePolicyName.textContent = name;

                if (tourCount > 0) {
                    tourCountWarning.style.display = 'block';
                    tourCountText.textContent = `Chính sách này đang được sử dụng bởi ${tourCount} tour. Bạn không thể xóa chính sách đang được sử dụng.`;
                    // Hide delete button or disable
                    const confirmBtn = deleteModalEl.querySelector('.btn-danger');
                    if (confirmBtn) confirmBtn.disabled = true;
                } else {
                    tourCountWarning.style.display = 'none';
                    const confirmBtn = deleteModalEl.querySelector('.btn-danger');
                    if (confirmBtn) {
                        confirmBtn.disabled = false;
                        confirmBtn.onclick = function() {
                            window.location.href = '<?= BASE_URL_ADMIN ?>&action=policies/delete&id=' + id;
                        };
                    }
                }

                deleteModal.show();
            });
        });
    });
</script>

<style>
.policy-card {
    transition: transform 0.2s, box-shadow 0.2s;
    height: 100%;
}

.policy-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.policy-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.card-actions {
    display: flex;
    gap: 0.5rem;
}
</style>

<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>