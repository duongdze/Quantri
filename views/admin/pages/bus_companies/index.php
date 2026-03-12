<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>

<main class="dashboard">
    <div class="dashboard-container">
        <!-- Header -->
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
                        <span class="breadcrumb-current">Quản lý Nhà Xe</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-bus title-icon"></i>
                            Quản lý Nhà Xe
                        </h1>
                        <p class="page-subtitle">Quản lý các nhà xe cung cấp dịch vụ vận chuyển</p>
                    </div>
                </div>
                <div class="header-right">
                    <button class="btn btn-modern btn-primary btn-lg" onclick="window.location.href='<?= BASE_URL_ADMIN . '&action=bus-companies/create' ?>'">
                        <i class="fas fa-plus-circle me-2"></i>
                        Thêm Nhà Xe Mới
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
                        <i class="fas fa-bus"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
                        <div class="stat-label">Tổng Nhà Xe</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['active'] ?? 0) ?></div>
                        <div class="stat-label">Đang Hoạt Động</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['total_vehicles'] ?? 0) ?></div>
                        <div class="stat-label">Tổng Số Xe</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['avg_rating'] ?? 5, 1) ?></div>
                        <div class="stat-label">Đánh Giá TB</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filters -->
        <section class="filters-section">
            <div class="filter-card">
                <div class="filter-header">
                    <h3 class="filter-title">
                        <i class="fas fa-filter"></i>
                        Bộ Lọc
                    </h3>
                </div>

                <form id="bus-company-filters" onsubmit="return false;" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="filter-label">Tìm kiếm</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" id="keyword" placeholder="Tên nhà xe, mã, SĐT, email...">
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Đánh giá tối thiểu</label>
                            <select class="form-select" id="rating_min">
                                <option value="">Tất cả</option>
                                <option value="4.5">4.5+ ⭐</option>
                                <option value="4.0">4.0+ ⭐</option>
                                <option value="3.5">3.5+ ⭐</option>
                                <option value="3.0">3.0+ ⭐</option>
                            </select>
                        </div>

                        <div class="filter-group filter-actions-group">
                            <button type="button" class="btn btn-primary" onclick="filterBusCompanies()">
                                <i class="fas fa-search me-2"></i>
                                Tìm kiếm
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                                <i class="fas fa-times me-2"></i>
                                Xóa lọc
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- Bus Companies Table -->
        <section class="tours-section">
            <div class="tours-header">
                <div class="tours-info">
                    <div class="select-all-wrapper">
                        <i class="fas fa-list"></i>
                        <label class="select-all-label">
                            Danh sách Nhà Xe
                        </label>
                    </div>
                    <div class="tours-count">
                        <span class="count-info">
                            <?= count($busCompanies) ?> nhà xe
                        </span>
                    </div>
                </div>
            </div>

            <div class="tours-container">
                <?php if (!empty($busCompanies)) : ?>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Mã NX</th>
                                    <th>Tên Nhà Xe</th>
                                    <th>Liên Hệ</th>
                                    <th>Số Xe</th>
                                    <th>Trạng Thái</th>
                                    <th>Đánh Giá</th>
                                    <th>Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($busCompanies as $index => $company) : ?>
                                    <tr class="bus-company-row">
                                        <td><strong><?= $index + 1 ?></strong></td>
                                        <td>
                                            <span class="badge bg-primary"><?= htmlspecialchars($company['company_code']) ?></span>
                                        </td>
                                        <td>
                                            <div class="customer-info">
                                                <i class="fas fa-building me-2 text-primary"></i>
                                                <strong><?= htmlspecialchars($company['company_name']) ?></strong>
                                            </div>
                                            <?php if (!empty($company['contact_person'])): ?>
                                                <small class="text-muted">Liên hệ: <?= htmlspecialchars($company['contact_person']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <?php if (!empty($company['phone'])): ?>
                                                    <div><i class="fas fa-phone me-2 text-success"></i><?= htmlspecialchars($company['phone']) ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($company['email'])): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($company['email']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= number_format($company['total_vehicles'] ?? 0) ?> xe</span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = $company['status'] == 'active' ? 'success' : 'secondary';
                                            $statusText = $company['status'] == 'active' ? 'Hoạt động' : 'Ngừng';
                                            $statusIcon = $company['status'] == 'active' ? 'check-circle' : 'pause-circle';
                                            ?>
                                            <span class="badge badge-modern badge-<?= $statusClass ?>">
                                                <i class="fas fa-<?= $statusIcon ?> me-1"></i>
                                                <?= $statusText ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="rating-display">
                                                <div class="stars">
                                                    <?php
                                                    $rating = $company['rating'] ?? 5;
                                                    $fullStars = floor($rating);
                                                    for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?= $i <= $fullStars ? 'filled' : 'empty' ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <span class="rating-value"><?= number_format($rating, 1) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?= BASE_URL_ADMIN . '&action=bus-companies/detail&id=' . $company['id'] ?>"
                                                    class="btn-action btn-view"
                                                    data-bs-toggle="tooltip"
                                                    title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= BASE_URL_ADMIN . '&action=bus-companies/edit&id=' . $company['id'] ?>"
                                                    class="btn-action btn-edit"
                                                    data-bs-toggle="tooltip"
                                                    title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button"
                                                    class="btn-action btn-delete"
                                                    onclick="deleteBusCompany(<?= $company['id'] ?>, '<?= htmlspecialchars($company['company_name']) ?>')"
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
                            <i class="fas fa-bus"></i>
                        </div>
                        <h3 class="empty-title">Chưa có nhà xe nào</h3>
                        <p class="empty-description">
                            Bắt đầu thêm nhà xe đầu tiên vào hệ thống.
                        </p>
                        <button class="btn btn-primary" onclick="window.location.href='<?= BASE_URL_ADMIN . '&action=bus-companies/create' ?>'">
                            <i class="fas fa-plus me-2"></i>
                            Thêm Nhà Xe Mới
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<!-- Form xóa nhà xe -->
<form id="deleteForm" method="POST" action="<?= BASE_URL_ADMIN ?>&action=bus-companies/delete" style="display: none;">
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

    // Client-side filtering
    function filterBusCompanies() {
        const keyword = document.getElementById('keyword').value.toLowerCase();
        const ratingMin = parseFloat(document.getElementById('rating_min').value) || 0;
        const rows = document.querySelectorAll('.bus-company-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const ratingElement = row.querySelector('.rating-value');
            const rating = ratingElement ? parseFloat(ratingElement.textContent) : 0;

            const matchesKeyword = !keyword || text.includes(keyword);
            const matchesRating = rating >= ratingMin;

            if (matchesKeyword && matchesRating) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        document.querySelector('.count-info').textContent = visibleCount + ' nhà xe';
    }

    function resetFilters() {
        document.getElementById('keyword').value = '';
        document.getElementById('rating_min').value = '';
        filterBusCompanies();
    }

    // Enter key support
    document.getElementById('keyword').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            filterBusCompanies();
        }
    });

    function deleteBusCompany(id, name) {
        if (confirm('Bạn có chắc muốn xóa nhà xe "' + name + '"?\nLưu ý: Các booking và tour assignment liên quan sẽ bị ảnh hưởng.')) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>