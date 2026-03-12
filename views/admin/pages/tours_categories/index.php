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
                        <a href="<?= BASE_URL_ADMIN ?>&action=/" class="breadcrumb-link">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current">Quản lý Danh mục Tour</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-folder title-icon"></i>
                            Quản lý Danh mục Tour
                        </h1>
                        <p class="page-subtitle">Phân loại và quản lý các danh mục tour</p>
                    </div>
                </div>
                <div class="header-right">
                    <button class="btn btn-modern btn-primary btn-lg" onclick="window.location.href='<?= BASE_URL_ADMIN . '&action=tours_categories/create' ?>'">
                        <i class="fas fa-plus-circle me-2"></i>
                        Thêm Danh Mục Mới
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
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format(count($categories ?? [])) ?></div>
                        <div class="stat-label">Tổng Danh Mục</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+5%</span>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-route"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format(array_sum(array_column($categories ?? [], 'tour_count'))) ?></div>
                        <div class="stat-label">Tổng Tour</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+12%</span>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format(count(array_filter($categories ?? [], fn($c) => $c['tour_count'] > 0))) ?></div>
                        <div class="stat-label">Có Tour</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+8%</span>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format(count(array_filter($categories ?? [], fn($c) => $c['tour_count'] == 0))) ?></div>
                        <div class="stat-label">Trống</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-down"></i>
                        <span>-3%</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Grid -->
        <section class="categories-section">
            <?php if (!empty($categories)): ?>
                <div class="categories-grid">
                    <?php foreach ($categories as $index => $category): ?>
                        <div class="category-card-modern" data-id="<?= $category['id'] ?>">
                            <!-- Card Header -->
                            <div class="category-card-header">
                                <div class="category-icon">
                                    <?php if (!empty($category['icon'])): ?>
                                        <i class="<?= htmlspecialchars($category['icon']) ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-folder"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="category-badges">
                                    <span class="badge badge-tours">
                                        <?= $category['tour_count'] ?? 0 ?> tour
                                    </span>
                                    <?php if (($category['tour_count'] ?? 0) > 0): ?>
                                        <span class="badge badge-active">
                                            <i class="fas fa-check-circle"></i>
                                            Có tour
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-empty">
                                            <i class="fas fa-inbox"></i>
                                            Trống
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="category-card-body">
                                <h3 class="category-title">
                                    <?= htmlspecialchars($category['name']) ?>
                                </h3>

                                <?php if (!empty($category['description'])): ?>
                                    <p class="category-description">
                                        <?= htmlspecialchars(substr($category['description'], 0, 120)) ?>
                                        <?php if (strlen($category['description']) > 120): ?>
                                            <span class="text-muted">...</span>
                                        <?php endif; ?>
                                    </p>
                                <?php else: ?>
                                    <p class="category-description text-muted">
                                        <em>Chưa có mô tả cho danh mục này</em>
                                    </p>
                                <?php endif; ?>

                                <!-- Category Statistics -->
                                <div class="category-stats">
                                    <div class="stat-item">
                                        <div class="stat-label">Tổng tour</div>
                                        <div class="stat-value"><?= number_format($category['tour_count'] ?? 0) ?></div>
                                    </div>
                                    <?php if (($category['tour_count'] ?? 0) > 0): ?>
                                        <div class="stat-item">
                                            <div class="stat-label">Đang hoạt động</div>
                                            <div class="stat-value"><?= number_format($category['active_tours'] ?? $category['tour_count']) ?></div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-label">Giá trung bình</div>
                                            <div class="stat-value">
                                                <?php
                                                $avgPrice = $category['avg_price'] ?? 0;
                                                if ($avgPrice >= 1000000) {
                                                    echo round($avgPrice / 1000000, 1) . ' tr';
                                                } else {
                                                    echo number_format($avgPrice, 0, ',', '.') . 'đ';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="category-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?= date('d/m/Y', strtotime($category['created_at'])) ?></span>
                                    </div>
                                    <?php if (!empty($category['slug'])): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-link"></i>
                                            <span class="slug-text"><?= htmlspecialchars($category['slug']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($category['updated_at']) && $category['updated_at'] != $category['created_at']): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-edit"></i>
                                            <span>Cập nhật: <?= date('d/m/Y', strtotime($category['updated_at'])) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="category-card-footer">
                                <div class="category-actions">
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary action-btn edit-category"
                                        data-id="<?= $category['id'] ?>"
                                        data-name="<?= htmlspecialchars($category['name']) ?>"
                                        data-bs-toggle="tooltip"
                                        title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger action-btn delete-category"
                                        data-id="<?= $category['id'] ?>"
                                        data-name="<?= htmlspecialchars($category['name']) ?>"
                                        data-tour-count="<?= $category['tour_count'] ?? 0 ?>"
                                        data-bs-toggle="tooltip"
                                        title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <?php if (($category['tour_count'] ?? 0) > 0): ?>
                                    <a href="<?= BASE_URL_ADMIN ?>&action=tours&category_id=<?= $category['id'] ?>"
                                        class="btn btn-sm btn-outline-info view-tours-btn">
                                        <i class="fas fa-eye me-1"></i>
                                        Xem Tour
                                    </a>
                                <?php else: ?>
                                    <a href="<?= BASE_URL_ADMIN ?>&action=tours/create&category_id=<?= $category['id'] ?>"
                                        class="btn btn-sm btn-outline-success add-tour-btn">
                                        <i class="fas fa-plus me-1"></i>
                                        Thêm Tour
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state-modern">
                    <div class="empty-state-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3 class="empty-state-title">Chưa có danh mục nào</h3>
                    <p class="empty-state-description">
                        Bạn chưa tạo danh mục tour nào. Hãy bắt đầu bằng cách tạo danh mục đầu tiên để tổ chức tours tốt hơn.
                    </p>
                    <button class="btn btn-modern btn-primary btn-lg" onclick="window.location.href='<?= BASE_URL_ADMIN . '&action=tours_categories/create' ?>'">
                        <i class="fas fa-plus-circle me-2"></i>
                        Thêm Danh Mục Mới
                    </button>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="delete-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Bạn có chắc chắn muốn xóa danh mục <strong id="categoryName"></strong>?</p>
                    <p id="tourCountWarning" class="text-warning" style="display: none;">
                        Danh mục này đang có <span id="tourCount"></span> tour. Bạn cần chuyển các tour này sang danh mục khác trước khi xóa.
                    </p>
                    <p class="text-danger">Hành động này không thể hoàn tác!</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Dashboard Styles - Sync with tours-modern.css */
    .dashboard {
        flex: 1;
        min-height: calc(100vh - 60px);
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 80px 0 20px 0;
        margin-left: 250px;
        transition: margin-left 0.3s ease;
    }

    .dashboard-container {
        width: 100%;
        padding: 0 15px;
    }

    /* Header Styles - Sync with tours */
    .dashboard-header {
        background: var(--tours-bg-primary, #ffffff);
        border-radius: var(--tours-radius-lg, 12px);
        padding: 32px;
        margin: 0 0 32px 0;
        box-shadow: var(--tours-shadow, 0 4px 6px rgba(0, 0, 0, 0.1));
        border: 1px solid var(--tours-border-light, #e5e7eb);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 32px;
    }

    .breadcrumb-modern {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }

    .breadcrumb-link {
        color: var(--tours-text-secondary, #6c757d);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: var(--tours-transition, all 0.3s ease);
        font-size: 14px;
    }

    .breadcrumb-link:hover {
        color: var(--tours-primary, #0d6efd);
    }

    .breadcrumb-separator {
        color: var(--tours-text-muted, #adb5bd);
        font-size: 12px;
    }

    .breadcrumb-current {
        color: var(--tours-text-primary, #212529);
        font-weight: 600;
        font-size: 14px;
    }

    .page-title-section {
        margin-top: 8px;
    }

    .page-title {
        font-size: 32px;
        font-weight: 700;
        color: var(--tours-text-primary, #212529);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 8px;
    }

    .title-icon {
        color: var(--tours-primary, #0d6efd);
    }

    .page-subtitle {
        color: var(--tours-text-secondary, #6c757d);
        margin: 0;
        font-size: 16px;
    }

    /* Statistics Cards - Sync with tours */
    .stats-section {
        margin-bottom: 32px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
    }

    .stat-card {
        background: var(--tours-bg-primary, #ffffff);
        border-radius: var(--tours-radius, 8px);
        padding: 16px;
        box-shadow: var(--tours-shadow-sm, 0 1px 3px rgba(0, 0, 0, 0.1));
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: var(--tours-radius, 8px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .stat-primary .stat-icon-wrapper {
        background: linear-gradient(135deg, var(--tours-primary, #0d6efd), #0b5ed7);
        color: white;
    }

    .stat-success .stat-icon-wrapper {
        background: linear-gradient(135deg, var(--tours-success, #198754), #157347);
        color: white;
    }

    .stat-warning .stat-icon-wrapper {
        background: linear-gradient(135deg, var(--tours-warning, #ffc107), #e0a800);
        color: white;
    }

    .stat-info .stat-icon-wrapper {
        background: linear-gradient(135deg, var(--tours-info, #0dcaf0), #0b9fb3);
        color: white;
    }

    .stat-content {
        flex: 1;
        min-width: 0;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--tours-text-primary, #212529);
        margin-bottom: 2px;
        line-height: 1;
    }

    .stat-label {
        color: var(--tours-text-secondary, #6c757d);
        font-size: 10px;
        font-weight: 500;
    }

    .stat-trend {
        display: flex;
        align-items: center;
        gap: 4px;
        color: var(--tours-success, #198754);
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
        margin-top: 4px;
    }

    /* Categories Grid - Keep existing but adjust container */
    .categories-section {
        background: var(--tours-bg-primary, #ffffff);
        border-radius: var(--tours-radius-lg, 12px);
        padding: 32px;
        box-shadow: var(--tours-shadow, 0 4px 6px rgba(0, 0, 0, 0.1));
        border: 1px solid var(--tours-border-light, #e5e7eb);
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
    }

    .category-card-modern {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .category-card-modern:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .category-card-header {
        background: linear-gradient(135deg, var(--tours-primary, #0d6efd), #0b5ed7);
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .category-icon {
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .category-badges {
        display: flex;
        gap: 8px;
    }

    .badge-tours {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-active {
        background: var(--tours-success, #198754);
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .badge-empty {
        background: var(--tours-text-muted, #adb5bd);
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .category-card-body {
        padding: 25px;
    }

    .category-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--tours-text-primary, #212529);
        margin: 0 0 15px 0;
    }

    .category-description {
        color: var(--tours-text-secondary, #6c757d);
        line-height: 1.6;
        margin: 0 0 20px 0;
    }

    /* Category Statistics */
    .category-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 15px;
        margin: 20px 0;
        padding: 15px;
        background: var(--tours-bg-secondary, #f8f9fa);
        border-radius: var(--tours-radius, 8px);
    }

    .stat-item {
        text-align: center;
    }

    .stat-item .stat-label {
        font-size: 0.75rem;
        color: var(--tours-text-secondary, #6c757d);
        margin-bottom: 4px;
        font-weight: 500;
    }

    .stat-item .stat-value {
        font-size: 1.1rem;
        color: var(--tours-text-primary, #212529);
        font-weight: 700;
    }

    .category-meta {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--tours-text-secondary, #6c757d);
        font-size: 0.9rem;
    }

    .meta-item i {
        width: 16px;
    }

    .slug-text {
        font-family: 'Courier New', monospace;
        background: var(--tours-bg-secondary, #f8f9fa);
        padding: 2px 6px;
        border-radius: 4px;
    }

    .category-card-footer {
        padding: 20px 25px;
        background: var(--tours-bg-secondary, #f8f9fa);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .category-actions {
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
        transition: var(--tours-transition, all 0.3s ease);
    }

    .action-btn:hover {
        transform: scale(1.1);
    }

    .view-tours-btn {
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid var(--tours-info, #0dcaf0);
        background: var(--tours-bg-primary, #ffffff);
        color: var(--tours-info, #0dcaf0);
        text-decoration: none;
        font-size: 0.9rem;
        transition: var(--tours-transition, all 0.3s ease);
    }

    .view-tours-btn:hover {
        background: var(--tours-info, #0dcaf0);
        color: white;
    }

    .add-tour-btn {
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid var(--tours-success, #198754);
        background: var(--tours-bg-primary, #ffffff);
        color: var(--tours-success, #198754);
        text-decoration: none;
        font-size: 0.9rem;
        transition: var(--tours-transition, all 0.3s ease);
    }

    .add-tour-btn:hover {
        background: var(--tours-success, #198754);
        color: white;
    }

    /* Empty State */
    .empty-state-modern {
        text-align: center;
        padding: 80px 20px;
    }

    .empty-state-icon {
        font-size: 80px;
        color: var(--tours-text-muted, #adb5bd);
        margin-bottom: 24px;
    }

    .empty-state-title {
        font-size: 24px;
        font-weight: 600;
        color: var(--tours-text-primary, #212529);
        margin-bottom: 12px;
    }

    .empty-state-description {
        color: var(--tours-text-secondary, #6c757d);
        font-size: 16px;
        margin-bottom: 24px;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Alert Styles */
    .alert-modern {
        border-radius: 12px;
        border: none;
        margin-bottom: 20px;
    }

    .alert-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-icon {
        font-size: 1.2rem;
    }

    /* Modal Styles */
    .delete-warning {
        text-align: center;
    }

    .delete-warning i {
        font-size: 3rem;
        color: #ffc107;
        margin-bottom: 20px;
    }

    /* Responsive - Sync with tours */
    @media (max-width: 1200px) {
        .dashboard {
            margin-left: 200px;
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }

        .stat-card {
            padding: 14px;
        }
    }

    @media (max-width: 992px) {
        .dashboard {
            margin-left: 70px;
            padding: 60px 15px 20px 15px;
        }

        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .stat-card {
            padding: 12px;
        }

        .stat-icon-wrapper {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .header-content {
            flex-direction: column;
            gap: 20px;
            text-align: center;
        }

        .categories-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .dashboard {
            margin-left: 0;
            padding: 60px 10px 20px 10px;
        }

        .dashboard-container {
            padding: 0;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .categories-grid {
            grid-template-columns: 1fr;
        }

        .page-title {
            font-size: 2rem;
        }

        .dashboard-header {
            padding: 20px;
        }

        .categories-section {
            padding: 20px;
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

        // Delete category functionality
        var deleteButtons = document.querySelectorAll('.delete-category');
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        var categoryName = document.getElementById('categoryName');
        var tourCount = document.getElementById('tourCount');
        var tourCountWarning = document.getElementById('tourCountWarning');
        var deleteForm = document.getElementById('deleteForm');
        var confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var categoryId = this.getAttribute('data-id');
                var name = this.getAttribute('data-name');
                var tourCountValue = this.getAttribute('data-tour-count');

                categoryName.textContent = name;
                tourCount.textContent = tourCountValue;

                if (tourCountValue > 0) {
                    tourCountWarning.style.display = 'block';
                    confirmDeleteBtn.disabled = true;
                    confirmDeleteBtn.classList.add('disabled');
                } else {
                    tourCountWarning.style.display = 'none';
                    confirmDeleteBtn.disabled = false;
                    confirmDeleteBtn.classList.remove('disabled');
                }

                deleteForm.action = '<?= BASE_URL_ADMIN ?>&action=tours_categories/delete&id=' + categoryId;
                deleteModal.show();
            });
        });

        // Edit category functionality
        var editButtons = document.querySelectorAll('.edit-category');
        editButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var categoryId = this.getAttribute('data-id');
                window.location.href = '<?= BASE_URL_ADMIN ?>&action=tours_categories/edit&id=' + categoryId;
            });
        });
    });
</script>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>