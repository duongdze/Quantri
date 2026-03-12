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
                        <span class="breadcrumb-current">Quản lý HDV</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-user-tie title-icon"></i>
                            Quản lý Hướng Dẫn Viên
                        </h1>
                        <p class="page-subtitle">Quản lý toàn bộ hướng dẫn viên du lịch trong hệ thống</p>
                    </div>
                </div>
                <div class="header-right">
                    <button class="btn btn-modern btn-primary btn-lg" onclick="window.location.href='<?= BASE_URL_ADMIN . '&action=guides/create' ?>'">
                        <i class="fas fa-plus-circle me-2"></i>
                        Thêm HDV Mới
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
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format(count($guides)) ?></div>
                        <div class="stat-label">Tổng HDV</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+6%</span>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">
                            <?php
                            $activeCount = 0;
                            foreach ($guides as $g) {
                                if (($g['status'] ?? 'active') === 'active') $activeCount++;
                            }
                            echo number_format($activeCount);
                            ?>
                        </div>
                        <div class="stat-label">Đang Hoạt Động</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+8%</span>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">
                            <?php
                            $totalExp = 0;
                            foreach ($guides as $g) {
                                $totalExp += ($g['experience_years'] ?? 0);
                            }
                            $avgExp = count($guides) > 0 ? $totalExp / count($guides) : 0;
                            echo number_format($avgExp, 1);
                            ?>
                        </div>
                        <div class="stat-label">Kinh Nghiệm TB (năm)</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+0.5</span>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">
                            <?php
                            $totalRating = 0;
                            foreach ($guides as $g) {
                                $totalRating += ($g['rating'] ?? 5);
                            }
                            $avgRating = count($guides) > 0 ? $totalRating / count($guides) : 5;
                            echo number_format($avgRating, 1);
                            ?>
                        </div>
                        <div class="stat-label">Đánh Giá TB</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+0.3</span>
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

                <form id="guide-filters" method="GET" action="<?= BASE_URL_ADMIN . '&action=guides' ?>" class="filter-form" onsubmit="return false;">
                    <input type="hidden" name="action" value="guides">

                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="filter-label">Tìm kiếm</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" name="keyword"
                                    value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>"
                                    placeholder="Tên, email, SĐT...">
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Đánh giá tối thiểu</label>
                            <select class="form-select" name="rating_min">
                                <option value="">Tất cả</option>
                                <option value="4" <?= (($_GET['rating_min'] ?? '') == '4') ? 'selected' : '' ?>>≥ 4 sao</option>
                                <option value="3" <?= (($_GET['rating_min'] ?? '') == '3') ? 'selected' : '' ?>>≥ 3 sao</option>
                            </select>
                        </div>

                        <div class="filter-group filter-actions-group">
                            <button type="button" onclick="filterGuides()" class="btn btn-primary">
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

        <!-- Guides Table Section -->
        <section class="tours-section">
            <div class="tours-header">
                <div class="tours-info">
                    <div class="select-all-wrapper">
                        <i class="fas fa-list"></i>
                        <label class="select-all-label">
                            Danh sách Hướng Dẫn Viên
                        </label>
                    </div>
                    <div class="tours-count">
                        <span class="count-info">
                            <?= count($guides) ?> HDV
                        </span>
                    </div>
                </div>
            </div>

            <div class="tours-container">
                <?php if (!empty($guides)) : ?>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Ảnh</th>
                                    <th>Họ và Tên</th>
                                    <th>Liên Hệ</th>
                                    <th>Ngôn Ngữ</th>
                                    <th>Kinh Nghiệm</th>
                                    <th>Đánh Giá</th>
                                    <th>Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($guides as $guide) : ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($guide['avatar'])): ?>
                                                <img src="<?= htmlspecialchars($guide['avatar']) ?>"
                                                    alt="Avatar"
                                                    class="rounded-circle"
                                                    style="width: 45px; height: 45px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                                                    style="width: 45px; height: 45px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="guide-name">
                                                <strong><?= htmlspecialchars($guide['full_name'] ?? 'N/A') ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <div><i class="fas fa-envelope me-2 text-primary"></i><?= htmlspecialchars($guide['email'] ?? 'N/A') ?></div>
                                                <div><i class="fas fa-phone me-2 text-success"></i><?= htmlspecialchars($guide['phone'] ?? 'N/A') ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-modern badge-info">
                                                <i class="fas fa-language me-1"></i>
                                                <?= htmlspecialchars($guide['languages'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($guide['experience_years'] ?? 0) ?></strong> năm
                                        </td>
                                        <td>
                                            <div class="rating-display">
                                                <div class="stars">
                                                    <?php
                                                    $rating = $guide['rating'] ?? 0;
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
                                                <a href="<?= BASE_URL_ADMIN . '&action=guides/detail&id=' . $guide['id'] ?>"
                                                    class="btn-action btn-view"
                                                    data-bs-toggle="tooltip"
                                                    title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= BASE_URL_ADMIN . '&action=guides/edit&id=' . $guide['id'] ?>"
                                                    class="btn-action btn-edit"
                                                    data-bs-toggle="tooltip"
                                                    title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= BASE_URL_ADMIN . '&action=guides/delete&id=' . $guide['id'] ?>"
                                                    class="btn-action btn-delete"
                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa hướng dẫn viên này?')"
                                                    data-bs-toggle="tooltip"
                                                    title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3 class="empty-title">Chưa có hướng dẫn viên nào</h3>
                        <p class="empty-description">
                            Bắt đầu thêm hướng dẫn viên đầu tiên vào hệ thống.
                        </p>
                        <button class="btn btn-primary" onclick="window.location.href='<?= BASE_URL_ADMIN . '&action=guides/create' ?>'">
                            <i class="fas fa-plus me-2"></i>
                            Thêm HDV Mới
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Add Enter key handler to keyword input
        const keywordInput = document.querySelector('#guide-filters [name="keyword"]');
        if (keywordInput) {
            keywordInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filterGuides();
                }
            });
        }
    });

    function filterGuides() {
        const keyword = document.querySelector('[name="keyword"]').value.toLowerCase();
        const ratingMin = parseFloat(document.querySelector('[name="rating_min"]').value) || 0;

        const tbody = document.querySelector('.table-modern tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));

        // Filter rows
        let visibleCount = 0;
        rows.forEach(row => {
            // Get row data
            const guideName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const contactInfo = row.querySelector('td:nth-child(3)').textContent.toLowerCase();

            // Get rating
            const ratingElement = row.querySelector('.rating-value');
            const guideRating = ratingElement ? parseFloat(ratingElement.textContent) : 0;

            // Check filters
            let show = true;

            // Filter by keyword (search in name, email, phone)
            if (keyword && !guideName.includes(keyword) && !contactInfo.includes(keyword)) {
                show = false;
            }

            // Filter by rating
            if (ratingMin > 0 && guideRating < ratingMin) {
                show = false;
            }

            // Show/hide row
            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        // Update count
        const countElement = document.querySelector('.count-info');
        if (countElement) {
            countElement.textContent = visibleCount + ' HDV';
        }
    }

    function resetFilters() {
        document.getElementById('guide-filters').reset();
        filterGuides();
    }
</script>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>