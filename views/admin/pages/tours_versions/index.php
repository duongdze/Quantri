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
                        <span class="breadcrumb-current">Phiên bản Tour</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-code-branch title-icon"></i>
                            Phiên bản Tour
                        </h1>
                        <p class="page-subtitle">
                            Quản lý các phiên bản tour của hệ thống
                        </p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=tours_versions/create" class="btn btn-modern btn-primary btn-lg">
                        <i class="fas fa-plus-circle me-2"></i>
                        Thêm Phiên Bản Mới
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

        <!-- Statistics Cards -->
        <section class="stats-section">
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-code-branch"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= count($versions) ?></div>
                        <div class="stat-label">Tổng phiên bản</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= count(array_filter($versions, fn($v) => $v['status'] === 'active')) ?></div>
                        <div class="stat-label">Đang hoạt động</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= count(array_filter($versions, fn($v) => $v['status'] === 'inactive')) ?></div>
                        <div class="stat-label">Tạm dừng</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= date('d/m/Y', strtotime($versions[0]['created_at'] ?? 'now')) ?></div>
                        <div class="stat-label">Ngày tạo gần nhất</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Versions List -->
        <section class="versions-section">
            <div class="section-header">
                <div class="section-title">
                    <h2 class="section-heading">
                        <i class="fas fa-list me-2"></i>
                        Danh sách phiên bản
                    </h2>
                    <p class="section-description">
                        Tổng hợp các phiên bản của tour với trạng thái và thông tin chi tiết
                    </p>
                </div>
                <div class="section-actions">
                    <a href="<?= BASE_URL_ADMIN ?>&action=tours" class="btn btn-modern btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Quay lại Tours
                    </a>
                </div>
            </div>

            <div class="versions-container">
                <?php if (!empty($versions)): ?>
                    <div class="versions-grid">
                        <?php foreach ($versions as $version): ?>
                            <div class="version-card-modern" data-id="<?= $version['id'] ?>">
                                <div class="version-card-header">
                                    <div class="version-info">
                                        <h3 class="version-name"><?= htmlspecialchars($version['name']) ?></h3>
                                        <div class="version-meta">
                                            <span class="version-id">#<?= str_pad($version['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                            <span class="version-date">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                <?= date('d/m/Y', strtotime($version['created_at'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="version-status">
                                        <span class="status-badge status-<?= $version['status'] ?>">
                                            <i class="fas fa-<?= $version['status'] === 'active' ? 'check' : 'pause' ?> me-1"></i>
                                            <?= $version['status'] === 'active' ? 'Đang hoạt động' : 'Tạm dừng' ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="version-card-body">
                                    <div class="version-description">
                                        <p><?= htmlspecialchars($version['description'] ?? 'Chưa có mô tả') ?></p>
                                    </div>

                                    <div class="version-stats">
                                        <div class="stat-item">
                                            <span class="stat-label">Ngày tạo:</span>
                                            <span class="stat-value"><?= date('H:i d/m/Y', strtotime($version['created_at'])) ?></span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-label">Cập nhật:</span>
                                            <span class="stat-value"><?= date('H:i d/m/Y', strtotime($version['updated_at'])) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="version-card-footer">
                                    <div class="version-actions">
                                        <button class="action-btn action-edit" onclick="editVersion(<?= $version['id'] ?>)" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn action-toggle" onclick="toggleVersionStatus(<?= $version['id'] ?>, '<?= $version['status'] ?>')" title="<?= $version['status'] === 'active' ? 'Tạm dừng' : 'Kích hoạt' ?>">
                                            <i class="fas fa-<?= $version['status'] === 'active' ? 'pause' : 'play' ?>"></i>
                                        </button>
                                        <button class="action-btn action-delete" onclick="deleteVersion(<?= $version['id'] ?>, '<?= htmlspecialchars($version['name']) ?>')" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state-modern">
                        <div class="empty-state-icon">
                            <i class="fas fa-code-branch"></i>
                        </div>
                        <h3 class="empty-state-title">Chưa có phiên bản nào</h3>
                        <p class="empty-state-description">
                            Chưa có phiên bản tour nào trong hệ thống. Hãy tạo phiên bản đầu tiên để bắt đầu quản lý.
                        </p>
                        <div class="empty-state-actions">
                            <a href="<?= BASE_URL_ADMIN ?>&action=tours_versions/create" class="btn btn-modern btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>
                                Tạo Phiên Bản Đầu Tiên
                            </a>
                            <a href="<?= BASE_URL_ADMIN ?>&action=tours" class="btn btn-modern btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Quay lại Tours
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa phiên bản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="delete-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h6>Bạn có chắc chắn muốn xóa phiên bản này?</h6>
                    <p class="mb-0">
                        <strong id="deleteVersionName"></strong>
                    </p>
                    <p class="text-muted small">Hành động này không thể hoàn tác.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">Xóa phiên bản</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Versions Styles - Sync with tours and tours_categories */
    .versions-section {
        margin-bottom: 32px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        background: var(--tours-bg-primary, #ffffff);
        border: 1px solid var(--tours-border-light, #e9ecef);
        border-radius: var(--tours-radius-lg, 12px);
        padding: 24px 32px;
        margin-bottom: 24px;
        box-shadow: var(--tours-shadow-sm, 0 2px 8px rgba(0, 0, 0, 0.04));
    }

    .section-title {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .section-heading {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--tours-text-primary, #212529);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-description {
        color: var(--tours-text-secondary, #6c757d);
        font-size: 0.95rem;
        margin: 0;
        line-height: 1.5;
    }

    .section-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .versions-container {
        margin-top: 24px;
    }

    .versions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .version-card-modern {
        background: var(--tours-bg-primary, #ffffff);
        border-radius: var(--tours-radius-lg, 12px);
        box-shadow: var(--tours-shadow, 0 4px 12px rgba(0, 0, 0, 0.08));
        border: 1px solid var(--tours-border-light, #e9ecef);
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .version-card-modern:hover {
        transform: translateY(-2px);
        box-shadow: var(--tours-shadow-lg, 0 8px 24px rgba(0, 0, 0, 0.12));
    }

    .version-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--tours-border-light, #e9ecef);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
    }

    .version-info {
        flex: 1;
    }

    .version-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--tours-text-primary, #212529);
        margin: 0 0 8px 0;
        line-height: 1.3;
    }

    .version-meta {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .version-id {
        font-family: 'Courier New', monospace;
        background: var(--tours-bg-secondary, #f8f9fa);
        color: var(--tours-text-secondary, #6c757d);
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
        width: fit-content;
    }

    .version-date {
        font-size: 0.85rem;
        color: var(--tours-text-secondary, #6c757d);
        display: flex;
        align-items: center;
    }

    .version-status {
        flex-shrink: 0;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-active {
        background: rgba(25, 135, 84, 0.1);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.2);
    }

    .status-inactive {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.2);
    }

    .version-card-body {
        padding: 20px 24px;
    }

    .version-description {
        margin-bottom: 16px;
    }

    .version-description p {
        color: var(--tours-text-secondary, #6c757d);
        line-height: 1.6;
        margin: 0;
    }

    .version-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background: var(--tours-bg-secondary, #f8f9fa);
        border-radius: 6px;
    }

    .stat-label {
        font-size: 0.8rem;
        color: var(--tours-text-secondary, #6c757d);
        font-weight: 500;
    }

    .stat-value {
        font-size: 0.8rem;
        color: var(--tours-text-primary, #212529);
        font-weight: 600;
    }

    .version-card-footer {
        padding: 16px 24px;
        background: var(--tours-bg-secondary, #f8f9fa);
        border-top: 1px solid var(--tours-border-light, #e9ecef);
        display: flex;
        justify-content: flex-end;
    }

    .version-actions {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--tours-border, #dee2e6);
        background: var(--tours-bg-primary, #ffffff);
        color: var(--tours-text-secondary, #6c757d);
        cursor: pointer;
        transition: var(--tours-transition, all 0.3s ease);
    }

    .action-btn:hover {
        transform: scale(1.1);
    }

    .action-edit:hover {
        background: var(--tours-primary, #0d6efd);
        color: white;
        border-color: var(--tours-primary, #0d6efd);
    }

    .action-toggle:hover {
        background: var(--tours-warning, #ffc107);
        color: white;
        border-color: var(--tours-warning, #ffc107);
    }

    .action-delete:hover {
        background: var(--tours-danger, #dc3545);
        color: white;
        border-color: var(--tours-danger, #dc3545);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .versions-grid {
            grid-template-columns: 1fr;
        }

        .section-header {
            flex-direction: column;
            gap: 16px;
            align-items: stretch;
        }

        .version-card-header {
            flex-direction: column;
            gap: 12px;
        }

        .version-stats {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Delete version functionality
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        var deleteForm = document.getElementById('deleteForm');
        var deleteVersionName = document.getElementById('deleteVersionName');

        window.deleteVersion = function(id, name) {
            deleteVersionName.textContent = name;
            deleteForm.action = '<?= BASE_URL_ADMIN ?>&action=tours_versions/delete&id=' + id;
            deleteModal.show();
        };

        // Edit version
        window.editVersion = function(id) {
            window.location.href = '<?= BASE_URL_ADMIN ?>&action=tours_versions/edit&id=' + id;
        };

        // Toggle version status
        window.toggleVersionStatus = function(id, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const actionText = newStatus === 'active' ? 'Kích hoạt' : 'Tạm dừng';

            if (confirm(`Bạn có chắc muốn ${actionText.toLowerCase()} phiên bản này?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= BASE_URL_ADMIN ?>&action=tours_versions/toggle-status';

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PATCH';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;

                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = newStatus;

                form.appendChild(methodInput);
                form.appendChild(idInput);
                form.appendChild(statusInput);
                document.body.appendChild(form);
                form.submit();
            }
        };
    });
</script>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>