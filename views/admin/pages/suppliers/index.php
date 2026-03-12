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
                        <span class="breadcrumb-current">Quản lý Nhà Cung Cấp</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-handshake title-icon"></i>
                            Quản lý Nhà Cung Cấp
                        </h1>
                        <p class="page-subtitle">Quản lý toàn bộ đối tác và nhà cung cấp dịch vụ</p>
                    </div>
                </div>
                <div class="header-right">
                    <button class="btn btn-modern btn-primary btn-lg" onclick="window.location.href='<?= BASE_URL_ADMIN . '&action=suppliers/create' ?>'">
                        <i class="fas fa-plus-circle me-2"></i>
                        Thêm Nhà Cung Cấp Mới
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
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
                        <div class="stat-label">Tổng Nhà Cung Cấp</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+5%</span>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['active'] ?? 0) ?></div>
                        <div class="stat-label">Đánh Giá Tốt</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+8%</span>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['high_rated'] ?? 0) ?></div>
                        <div class="stat-label">Đối Tác Ưu Tiên</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+3%</span>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['avg_rating'] ?? 0, 1) ?></div>
                        <div class="stat-label">Đánh Giá TB</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+0.2</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Advanced Filters -->
        <section class="filters-section">
            <div class="filter-card">
                <div class="filter-header">
                    <h3 class="filter-title">
                        <i class="fas fa-filter"></i>
                        Bộ Lọc
                    </h3>
                </div>

                <form id="supplier-filters" method="GET" action="<?= BASE_URL_ADMIN . '&action=suppliers' ?>" class="filter-form" onsubmit="return false;">
                    <input type="hidden" name="action" value="suppliers">

                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="filter-label">Tìm kiếm</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" name="keyword"
                                    value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>"
                                    placeholder="Tên, liên hệ, SĐT, email...">
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Loại</label>
                            <select class="form-select" name="type">
                                <option value="">Tất cả</option>
                                <?php foreach ($types ?? [] as $t): ?>
                                    <option value="<?= htmlspecialchars($t) ?>"
                                        <?= (($_GET['type'] ?? '') == $t) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucfirst($t)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Đánh giá tối thiểu</label>
                            <select class="form-select" name="rating_min">
                                <option value="">Tất cả</option>
                                <option value="1" <?= (($_GET['rating_min'] ?? '') == '1') ? 'selected' : '' ?>>≥ 1 sao</option>
                                <option value="2" <?= (($_GET['rating_min'] ?? '') == '2') ? 'selected' : '' ?>>≥ 2 sao</option>
                                <option value="3" <?= (($_GET['rating_min'] ?? '') == '3') ? 'selected' : '' ?>>≥ 3 sao</option>
                                <option value="4" <?= (($_GET['rating_min'] ?? '') == '4') ? 'selected' : '' ?>>≥ 4 sao</option>
                                <option value="5" <?= (($_GET['rating_min'] ?? '') == '5') ? 'selected' : '' ?>>5 sao</option>
                            </select>
                        </div>

                        <div class="filter-group filter-actions-group">
                            <button type="button" onclick="filterSuppliers()" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>
                                Tìm kiếm
                            </button>
                            <button type="button" onclick="resetFilters()" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Xóa lọc
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- Suppliers Table Section -->
        <section class="tours-section">
            <div class="tours-header">
                <div class="tours-info">
                    <div class="select-all-wrapper">
                        <i class="fas fa-list"></i>
                        <label class="select-all-label">
                            Danh sách Nhà Cung Cấp
                        </label>
                    </div>
                    <div class="tours-count">
                        <span class="count-info">
                            <?= count($suppliers) ?> nhà cung cấp
                        </span>
                    </div>
                </div>
            </div>

            <div class="tours-container">
                <?php if (!empty($suppliers)) : ?>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tên Nhà Cung Cấp</th>
                                    <th>Loại</th>
                                    <th>Liên Hệ</th>
                                    <th>Thông Tin</th>
                                    <th>Đánh Giá</th>
                                    <th>Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($suppliers as $index => $supplier) : ?>
                                    <tr>
                                        <td><strong><?= $index + 1 ?></strong></td>
                                        <td>
                                            <div class="supplier-info">
                                                <i class="fas fa-building me-2 text-primary"></i>
                                                <strong><?= htmlspecialchars($supplier['name']) ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-modern badge-info">
                                                <i class="fas fa-tag me-1"></i>
                                                <?= htmlspecialchars(ucfirst($supplier['type'] ?? '-')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <?php if (!empty($supplier['contact_person'])): ?>
                                                    <div><i class="fas fa-user me-2 text-secondary"></i><?= htmlspecialchars($supplier['contact_person']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="info-details">
                                                <?php if (!empty($supplier['phone'])): ?>
                                                    <div><i class="fas fa-phone me-2 text-success"></i><?= htmlspecialchars($supplier['phone']) ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($supplier['email'])): ?>
                                                    <div><i class="fas fa-envelope me-2 text-info"></i><small><?= htmlspecialchars($supplier['email']) ?></small></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($supplier['rating'])): ?>
                                                <div class="rating-display">
                                                    <div class="stars">
                                                        <?php
                                                        $rating = $supplier['rating'];
                                                        $fullStars = floor($rating);
                                                        for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star <?= $i <= $fullStars ? 'filled' : 'empty' ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <span class="rating-value"><?= number_format($rating, 1) ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?= BASE_URL_ADMIN . '&action=suppliers/detail&id=' . $supplier['id'] ?>"
                                                    class="btn-action btn-view"
                                                    data-bs-toggle="tooltip"
                                                    title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= BASE_URL_ADMIN . '&action=suppliers/edit&id=' . $supplier['id'] ?>"
                                                    class="btn-action btn-edit"
                                                    data-bs-toggle="tooltip"
                                                    title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button"
                                                    class="btn-action btn-delete"
                                                    onclick="deleteSupplier(<?= $supplier['id'] ?>, '<?= htmlspecialchars($supplier['name']) ?>')"
                                                    data-bs-toggle="tooltip"
                                                    title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3 class="empty-title">Chưa có nhà cung cấp nào</h3>
                        <p class="empty-description">
                            Bắt đầu thêm nhà cung cấp đầu tiên vào hệ thống.
                        </p>
                        <button class="btn btn-primary" onclick="window.location.href='<?= BASE_URL_ADMIN . '&action=suppliers/create' ?>'">
                            <i class="fas fa-plus me-2"></i>
                            Thêm Nhà Cung Cấp Mới
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<!-- Form xóa nhà cung cấp -->
<form id="deleteForm" method="POST" action="<?= BASE_URL_ADMIN ?>&action=suppliers/delete" style="display: none;">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    function filterSuppliers() {
        const keyword = document.querySelector('[name="keyword"]').value.toLowerCase();
        const type = document.querySelector('[name="type"]').value.toLowerCase();
        const ratingMin = parseFloat(document.querySelector('[name="rating_min"]').value) || 0;

        const tbody = document.querySelector('.table-modern tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));

        // Filter rows
        let visibleCount = 0;
        rows.forEach(row => {
            // Get row data
            const supplierName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const supplierType = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const contactInfo = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
            const phoneEmail = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
            
            // Get rating
            const ratingElement = row.querySelector('.rating-value');
            const supplierRating = ratingElement ? parseFloat(ratingElement.textContent) : 0;

            // Check filters
            let show = true;

            // Filter by keyword (search in name, contact, phone, email)
            if (keyword && !supplierName.includes(keyword) && 
                !contactInfo.includes(keyword) && !phoneEmail.includes(keyword)) {
                show = false;
            }

            // Filter by type
            if (type && !supplierType.includes(type)) {
                show = false;
            }

            // Filter by rating
            if (ratingMin > 0 && supplierRating < ratingMin) {
                show = false;
            }

            // Show/hide row
            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        // Update count
        const countElement = document.querySelector('.count-info');
        if (countElement) {
            countElement.textContent = visibleCount + ' nhà cung cấp';
        }
    }

    function resetFilters() {
        document.getElementById('supplier-filters').reset();
        filterSuppliers();
    }

    function deleteSupplier(id, name) {
        if (confirm('Bạn có chắc muốn xóa nhà cung cấp "' + name + '"?\nLưu ý: Các dữ liệu liên quan có thể bị ảnh hưởng.')) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>