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
                        <span class="breadcrumb-current">Quản lý Tour</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-route title-icon"></i>
                            Quản lý Tour
                        </h1>
                        <p class="page-subtitle">Quản lý toàn bộ các tour và thông tin liên quan</p>
                    </div>
                </div>
                <div class="header-right">
                    <button class="btn btn-modern btn-primary btn-lg" onclick="window.location.href='<?= BASE_URL_ADMIN . '&action=tours/create' ?>'">
                        <i class="fas fa-plus-circle me-2"></i>
                        Thêm Tour Mới
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
                        <i class="fas fa-route"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
                        <div class="stat-label">Tổng Tour</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+12%</span>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['active'] ?? 0) ?></div>
                        <div class="stat-label">Đang Hoạt Động</div>
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
                        <div class="stat-value"><?= number_format($stats['featured'] ?? 0) ?></div>
                        <div class="stat-label">Tour Nổi Bật</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+5%</span>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['ongoing'] ?? 0) ?></div>
                        <div class="stat-label">Đang Diễn Ra</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+15%</span>
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
                        Bộ Lọc Nâng Cao
                    </h3>
                    <div class="filter-actions">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                            <i class="fas fa-redo"></i>
                            Reset
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAdvancedFilters()">
                            <i class="fas fa-cog"></i>
                            Nâng Cao
                        </button>
                    </div>
                </div>

                <form id="tour-filters" method="GET" class="filter-form">
                    <input type="hidden" name="action" value="tours">

                    <!-- Basic Filters -->
                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="filter-label">Tìm kiếm</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" name="keyword"
                                    value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>"
                                    placeholder="Tên tour, mô tả...">
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Loại Tour</label>
                            <select class="form-select" name="category_id">
                                <option value="">Tất cả</option>
                                <?php foreach ($categories ?? [] as $category): ?>
                                    <option value="<?= $category['id'] ?>"
                                        <?= (($_GET['category_id'] ?? '') == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Trạng thái</label>
                            <select class="form-select" name="status">
                                <option value="">Tất cả</option>
                                <option value="active" <?= (($_GET['status'] ?? '') == 'active') ? 'selected' : '' ?>>
                                    Đang hoạt động
                                </option>
                                <option value="inactive" <?= (($_GET['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>
                                    Tạm ẩn
                                </option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Đánh giá</label>
                            <select class="form-select" name="rating_min">
                                <option value="">Tất cả</option>
                                <option value="1" <?= (($_GET['rating_min'] ?? '') == '1') ? 'selected' : '' ?>>≥ 1 sao</option>
                                <option value="2" <?= (($_GET['rating_min'] ?? '') == '2') ? 'selected' : '' ?>>≥ 2 sao</option>
                                <option value="3" <?= (($_GET['rating_min'] ?? '') == '3') ? 'selected' : '' ?>>≥ 3 sao</option>
                                <option value="4" <?= (($_GET['rating_min'] ?? '') == '4') ? 'selected' : '' ?>>≥ 4 sao</option>
                                <option value="5" <?= (($_GET['rating_min'] ?? '') == '5') ? 'selected' : '' ?>>≥ 5 sao</option>
                            </select>
                        </div>
                    </div>

                    <!-- Advanced Filters (Hidden by default) -->
                    <div class="filter-row advanced-filters" style="display: none;">
                        <div class="filter-group">
                            <label class="filter-label">Giá từ (VNĐ)</label>
                            <input type="number" class="form-control" name="price_min"
                                value="<?= htmlspecialchars($_GET['price_min'] ?? '') ?>"
                                placeholder="0">
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Giá đến (VNĐ)</label>
                            <input type="number" class="form-control" name="price_max"
                                value="<?= htmlspecialchars($_GET['price_max'] ?? '') ?>"
                                placeholder="Không giới hạn">
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Từ ngày</label>
                            <input type="date" class="form-control" name="date_from"
                                value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>" />
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Đến ngày</label>
                            <input type="date" class="form-control" name="date_to"
                                value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>" />
                        </div>
                    </div>

                    <!-- Sort and Display Options -->
                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="filter-label">Sắp xếp</label>
                            <div class="sort-controls">
                                <select class="form-select" name="sort_by">
                                    <option value="">Mặc định</option>
                                    <option value="name" <?= (($_GET['sort_by'] ?? '') == 'name') ? 'selected' : '' ?>>
                                        Tên tour
                                    </option>
                                    <option value="price" <?= (($_GET['sort_by'] ?? '') == 'price') ? 'selected' : '' ?>>
                                        Giá
                                    </option>
                                    <option value="rating" <?= (($_GET['sort_by'] ?? '') == 'rating') ? 'selected' : '' ?>>
                                        Đánh giá
                                    </option>
                                    <option value="created_at" <?= (($_GET['sort_by'] ?? '') == 'created_at') ? 'selected' : '' ?>>
                                        Ngày tạo
                                    </option>
                                </select>
                                <select class="form-select" name="sort_dir">
                                    <option value="DESC" <?= (($_GET['sort_dir'] ?? '') == 'DESC') ? 'selected' : '' ?>>
                                        Giảm dần
                                    </option>
                                    <option value="ASC" <?= (($_GET['sort_dir'] ?? '') == 'ASC') ? 'selected' : '' ?>>
                                        Tăng dần
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Hiển thị</label>
                            <select class="form-select" name="per_page">
                                <option value="12" <?= (($_GET['per_page'] ?? '') == '12') ? 'selected' : '' ?>>12</option>
                                <option value="24" <?= (($_GET['per_page'] ?? '') == '24') ? 'selected' : '' ?>>24</option>
                                <option value="48" <?= (($_GET['per_page'] ?? '') == '48') ? 'selected' : '' ?>>48</option>
                            </select>
                        </div>

                        <div class="filter-group filter-actions-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>
                                Tìm kiếm
                            </button>
                            <a href="<?= BASE_URL_ADMIN . '&action=tours' ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Xóa lọc
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- Bulk Actions Bar -->
        <section class="bulk-actions-bar" id="bulk-actions" style="display: none;">
            <div class="bulk-actions-content">
                <div class="bulk-info">
                    <span class="bulk-count">
                        <i class="fas fa-check-square"></i>
                        Đã chọn <strong id="selected-count">0</strong> tour
                    </span>
                </div>
                <div class="bulk-buttons">
                    <button type="button" class="btn btn-success" id="bulk-activate">
                        <i class="fas fa-check me-2"></i>
                        Kích hoạt
                    </button>
                    <button type="button" class="btn btn-warning" id="bulk-deactivate">
                        <i class="fas fa-pause me-2"></i>
                        Tạm ẩn
                    </button>
                    <button type="button" class="btn btn-info" id="bulk-featured">
                        <i class="fas fa-star me-2"></i>
                        Nổi bật
                    </button>
                </div>
            </div>
        </section>

        <!-- Tours Grid Section -->
        <section class="tours-section">
            <div class="tours-header">
                <div class="tours-info">
                    <div class="select-all-wrapper">
                        <input type="checkbox" class="form-check-input" id="select-all-tours">
                        <label for="select-all-tours" class="select-all-label">
                            <i class="fas fa-list"></i>
                            Danh sách Tour
                        </label>
                    </div>
                    <div class="tours-count">
                        <span class="count-info">
                            <?= $pagination['total'] ?? 0 ?> tour •
                            Trang <?= $pagination['page'] ?? 1 ?>/<?= max(1, $pagination['total_pages'] ?? 1) ?>
                        </span>
                    </div>
                </div>
                <div class="view-options">
                    <button class="view-btn active" data-view="grid" title="Grid view">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-btn" data-view="list" title="List view">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>

            <div class="tours-container" id="tour-list-container">
                <?php if (!empty($tours)) : ?>
                    <div class="tours-grid">
                        <?php foreach ($tours as $tour) : ?>
                            <?php
                            // Prepare images - ưu tiên main_image trước
                            $mainImage = $tour['main_image'] ?? null;
                            $galleryImages = [];
                            if (!empty($tour['gallery_images'])) {
                                $galleryImages = array_values(array_filter(array_map('trim', explode(',', $tour['gallery_images']))));
                            }
                            // Nếu không có gallery nhưng có main_image, thêm vào gallery
                            if (empty($galleryImages) && !empty($mainImage)) {
                                $galleryImages = [$mainImage];
                            }
                            // Nếu có gallery nhưng không có main_image trong gallery, thêm main_image vào đầu
                            if (!empty($mainImage) && !empty($galleryImages) && !in_array($mainImage, $galleryImages)) {
                                array_unshift($galleryImages, $mainImage);
                            }
                            // Nếu không có main_image, dùng gallery đầu tiên
                            if (empty($mainImage) && !empty($galleryImages)) {
                                $mainImage = $galleryImages[0];
                            }
                            $thumbs = array_slice($galleryImages, 1);
                            $totalImages = count($galleryImages);
                            $maxThumbs = 3;
                            $thumbsToShow = array_slice($thumbs, 0, $maxThumbs);
                            $remaining = max(0, $totalImages - 1 - count($thumbsToShow));

                            // Build full gallery URLs for JS
                            $galleryUrls = [];
                            if (!empty($galleryImages)) {
                                foreach ($galleryImages as $g) {
                                    if (!empty($g)) {
                                        $galleryUrls[] = BASE_ASSETS_UPLOADS . $g;
                                    }
                                }
                            }
                            if (empty($galleryUrls) && !empty($tour['main_image'])) {
                                $galleryUrls = [BASE_ASSETS_UPLOADS . $tour['main_image']];
                            }
                            ?>

                            <div class="tour-card-modern" data-id="<?= $tour['id'] ?>"
                                data-gallery='<?= htmlspecialchars(json_encode($galleryUrls), ENT_QUOTES) ?>'>

                                <!-- Card Header -->
                                <div class="tour-card-header">
                                    <div class="tour-checkbox">
                                        <input type="checkbox" class="form-check-input tour-select" value="<?= $tour['id'] ?>">
                                    </div>
                                    <div class="tour-badges">
                                        <?php if ($tour['featured'] ?? 0): ?>
                                            <span class="badge badge-featured">
                                                <i class="fas fa-star"></i>
                                                Nổi bật
                                            </span>
                                        <?php endif; ?>
                                        <span class="badge badge-status badge-<?= ($tour['status'] ?? 'active') === 'active' ? 'active' : 'inactive' ?>">
                                            <?= ($tour['status'] ?? 'active') === 'active' ? 'Đang hoạt động' : 'Tạm ẩn' ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Gallery Section -->
                                <div class="tour-gallery">
                                    <div class="tour-main-image">
                                        <?php if ($mainImage) :
                                            $mainUrl = BASE_ASSETS_UPLOADS . $mainImage;
                                        else:
                                            $mainUrl = BASE_URL . 'assets/admin/image/no-image.png';
                                        endif; ?>
                                        <img src="<?= $mainUrl ?>" alt="<?= htmlspecialchars($tour['name']) ?>"
                                            data-index="0" class="main-img">

                                        <!-- Overlay Info -->
                                        <div class="image-overlay">
                                            <div class="overlay-top">
                                                <span class="category-badge">
                                                    <?= htmlspecialchars($tour['category_name'] ?? '') ?>
                                                </span>
                                                <?php
                                                $price = $tour['base_price'] ?? 0;
                                                if ($price >= 1000000000) {
                                                    $priceShort = round($price / 1000000000, ($price / 1000000000) >= 10 ? 0 : 1) . ' tỷ';
                                                } elseif ($price >= 1000000) {
                                                    $priceShort = round($price / 1000000, 1) . ' tr';
                                                } else {
                                                    $priceShort = number_format($price, 0, ',', '.') . 'đ';
                                                }
                                                ?>
                                                <span class="price-badge"><?= $priceShort ?></span>
                                            </div>
                                            <div class="gallery-counter">
                                                <i class="fas fa-images"></i>
                                                <span><?= $totalImages ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!empty($thumbsToShow)) : ?>
                                        <div class="tour-thumbnails">
                                            <?php foreach ($thumbsToShow as $i => $timg):
                                                $turl = !empty($timg) ? BASE_ASSETS_UPLOADS . $timg : BASE_URL . 'assets/admin/image/no-image.png';
                                            ?>
                                                <div class="thumbnail-item">
                                                    <img src="<?= $turl ?>" alt="thumb-<?= $i ?>" data-index="<?= $i + 1 ?>">
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if ($remaining > 0) : ?>
                                                <div class="thumbnail-item more-photos">
                                                    <span>+<?= $remaining ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Tour Info -->
                                <div class="tour-info">
                                    <div class="tour-title-section">
                                        <h5 class="tour-title"><?= htmlspecialchars($tour['name']) ?></h5>
                                        <div class="tour-meta">
                                            <div class="rating-display">
                                                <div class="stars">
                                                    <?php
                                                    $rating = $tour['avg_rating'] ?? 0;
                                                    $fullStars = floor($rating);
                                                    $hasHalfStar = $rating - $fullStars >= 0.5;
                                                    for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?= $i <= $fullStars ? 'filled' : ($i == $fullStars + 1 && $hasHalfStar ? 'half' : 'empty') ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <span class="rating-value"><?= number_format($rating, 1) ?></span>
                                                <span class="booking-count">(<?= number_format($tour['booking_count'] ?? 0) ?> đặt)</span>
                                            </div>
                                            <div class="price-display">
                                                <span class="price-value"><?= number_format($tour['base_price'] ?? 0, 0, ',', '.') ?> VNĐ</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Quick Actions -->
                                    <div class="quick-actions">
                                        <div class="toggles">
                                            <div class="toggle-group">
                                                <label class="toggle-label">
                                                    <input type="checkbox" class="toggle-input toggle-status"
                                                        data-id="<?= $tour['id'] ?>"
                                                        <?= ($tour['status'] ?? 'active') === 'active' ? 'checked' : '' ?>>
                                                    <span class="toggle-slider"></span>
                                                    <span class="toggle-text">
                                                        <?= ($tour['status'] ?? 'active') === 'active' ? 'Hoạt động' : 'Tạm ẩn' ?>
                                                    </span>
                                                </label>
                                            </div>
                                            <div class="toggle-group">
                                                <label class="toggle-label featured-toggle">
                                                    <input type="checkbox" class="toggle-input toggle-featured"
                                                        data-id="<?= $tour['id'] ?>"
                                                        <?= ($tour['featured'] ?? 0) ? 'checked' : '' ?>>
                                                    <span class="toggle-slider"></span>
                                                    <span class="toggle-text">
                                                        <i class="fas fa-star"></i>
                                                        Nổi bật
                                                    </span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="action-buttons">
                                            <a href="<?= BASE_URL_ADMIN . '&action=tours/detail&id=' . $tour['id'] ?>"
                                                class="btn-action btn-view"
                                                data-bs-toggle="tooltip"
                                                title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL_ADMIN . '&action=tours/edit&id=' . $tour['id'] ?>"
                                                class="btn-action btn-edit"
                                                data-bs-toggle="tooltip"
                                                title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button"
                                                class="btn-action btn-qr btn-info"
                                                data-id="<?= $tour['id'] ?>"
                                                data-name="<?= htmlspecialchars($tour['name']) ?>"
                                                data-bs-toggle="tooltip"
                                                title="Lấy Link & QR">
                                                <i class="fas fa-qrcode"></i>
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
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="empty-title">Không tìm thấy tour phù hợp</h3>
                        <p class="empty-description">
                            Thử điều chỉnh bộ lọc hoặc tìm kiếm với từ khóa khác.
                        </p>
                        <button class="btn btn-primary" onclick="resetFilters()">
                            <i class="fas fa-redo me-2"></i>
                            Reset bộ lọc
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if (($pagination['total_pages'] ?? 1) > 1) : ?>
                <?php
                $filterParams = array_filter([
                    'keyword' => $_GET['keyword'] ?? '',
                    'category_id' => $_GET['category_id'] ?? '',
                    'status' => $_GET['status'] ?? '',
                    'date_from' => $_GET['date_from'] ?? '',
                    'date_to' => $_GET['date_to'] ?? '',
                    'price_min' => $_GET['price_min'] ?? '',
                    'price_max' => $_GET['price_max'] ?? '',
                    'rating_min' => $_GET['rating_min'] ?? '',
                    'per_page' => $pagination['per_page'] ?? null,
                    'sort_by' => $_GET['sort_by'] ?? null,
                    'sort_dir' => $_GET['sort_dir'] ?? null,
                ], function ($value) {
                    return $value !== null && $value !== '';
                });

                $queryStringBase = '';
                if (!empty($filterParams)) {
                    $queryStringBase = '&' . http_build_query($filterParams);
                }
                ?>

                <nav class="pagination-nav">
                    <ul class="pagination-modern">
                        <?php
                        $currentPage = $pagination['page'];
                        $totalPages = $pagination['total_pages'];
                        $prevPage = max(1, $currentPage - 1);
                        $nextPage = min($totalPages, $currentPage + 1);
                        ?>

                        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL_ADMIN . '&action=tours&page=' . $prevPage . $queryStringBase ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);

                        if ($startPage > 1) : ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= BASE_URL_ADMIN . '&action=tours&page=1' . $queryStringBase ?>">1</a>
                            </li>
                            <?php if ($startPage > 2) : ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($page = $startPage; $page <= $endPage; $page++) : ?>
                            <li class="page-item <?= $page === $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL_ADMIN . '&action=tours&page=' . $page . $queryStringBase ?>"><?= $page ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($endPage < $totalPages) : ?>
                            <?php if ($endPage < $totalPages - 1) : ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= BASE_URL_ADMIN . '&action=tours&page=' . $totalPages . $queryStringBase ?>"><?= $totalPages ?></a>
                            </li>
                        <?php endif; ?>

                        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL_ADMIN . '&action=tours&page=' . $nextPage . $queryStringBase ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </section>
    </div>
</main>

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrModalLabel">
                    <i class="fas fa-qrcode me-2"></i>
                    Mã QR & Link Đặt Tour
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <h6 id="qr-tour-name" class="mb-3 fw-bold text-primary"></h6>

                <div class="qr-code-wrapper mb-4 p-3 border rounded bg-light d-inline-block">
                    <div id="qrcode"></div>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fas fa-link"></i></span>
                    <input type="text" class="form-control" id="tour-link" readonly>
                    <button class="btn btn-primary" type="button" id="copy-link-btn">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>

                <div class="alert alert-success d-none" id="copy-success-alert">
                    <small><i class="fas fa-check-circle me-1"></i> Đã sao chép liên kết vào bộ nhớ tạm!</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Xác nhận xóa tour
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Bạn có chắc chắn muốn xóa tour "<span id="delete-tour-name"></span>"?
                </div>
                <p class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Hành động này không thể hoàn tác và sẽ xóa tất cả dữ liệu liên quan.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>
                    Hủy
                </button>
                <form id="delete-form" method="POST" style="display: inline;">
                    <input type="hidden" name="id" id="delete-tour-id">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>
                        Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Confirmation Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Xác nhận xóa nhiều tour
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Bạn có chắc chắn muốn xóa <span id="bulk-delete-count">0</span> tour đã chọn?
                </div>
                <p class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Hành động này không thể hoàn tác và sẽ xóa tất cả dữ liệu liên quan.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>
                    Hủy
                </button>
                <form id="bulk-delete-form" method="POST" style="display: inline;">
                    <input type="hidden" name="tour_ids" id="bulk-tour-ids">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>
                        Xóa
                    </button>
                </form>
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

        // Tour selection functionality
        const selectAllCheckbox = document.getElementById('select-all-tours');
        const tourCheckboxes = document.querySelectorAll('.tour-select');
        const bulkActions = document.getElementById('bulk-actions');
        const selectedCount = document.getElementById('selected-count');

        function updateBulkActions() {
            const checkedBoxes = document.querySelectorAll('.tour-select:checked');
            const count = checkedBoxes.length;

            selectedCount.textContent = count;
            bulkActions.style.display = count > 0 ? 'flex' : 'none';

            // Update select all checkbox state
            if (tourCheckboxes.length === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (count === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (count === tourCheckboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }

        // Select all functionality
        selectAllCheckbox.addEventListener('change', function() {
            tourCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });

        // Individual checkbox change
        tourCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActions);
        });

        // Toggle status functionality
        document.querySelectorAll('.toggle-status').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const tourId = this.dataset.id;
                const newStatus = this.checked ? 'active' : 'inactive';

                fetch('<?= BASE_URL_ADMIN ?>&action=tours/toggle-status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `_method=PATCH&id=${tourId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the status badge
                            const tourCard = this.closest('.tour-card-modern');
                            const statusBadge = tourCard.querySelector('.badge-status');
                            statusBadge.className = `badge badge-status badge-${newStatus === 'active' ? 'active' : 'inactive'}`;
                            statusBadge.textContent = newStatus === 'active' ? 'Đang hoạt động' : 'Tạm ẩn';

                            // Update the label
                            const toggleText = this.closest('.toggle-label').querySelector('.toggle-text');
                            toggleText.textContent = newStatus === 'active' ? 'Hoạt động' : 'Tạm ẩn';
                        } else {
                            this.checked = !this.checked; // Revert on error
                            showToast('Có lỗi xảy ra khi cập nhật trạng thái', 'error');
                        }
                    })
                    .catch(error => {
                        this.checked = !this.checked; // Revert on error
                        console.error('Error:', error);
                        showToast('Có lỗi xảy ra khi cập nhật trạng thái', 'error');
                    });
            });
        });

        // Toggle featured functionality
        document.querySelectorAll('.toggle-featured').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const tourId = this.dataset.id;

                fetch('<?= BASE_URL_ADMIN ?>&action=tours/toggle-featured', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `_method=PATCH&id=${tourId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            this.checked = !this.checked; // Revert on error
                            showToast('Có lỗi xảy ra khi cập nhật trạng thái nổi bật', 'error');
                        }
                    })
                    .catch(error => {
                        this.checked = !this.checked; // Revert on error
                        console.error('Error:', error);
                        showToast('Có lỗi xảy ra khi cập nhật trạng thái nổi bật', 'error');
                    });
            });
        });

        // Delete functionality
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const deleteForm = document.getElementById('delete-form');

        document.querySelectorAll('.delete-tour').forEach(button => {
            button.addEventListener('click', function() {
                const tourId = this.dataset.id;
                const tourName = this.dataset.name;

                document.getElementById('delete-tour-id').value = tourId;
                document.getElementById('delete-tour-name').textContent = tourName;
                deleteForm.action = '<?= BASE_URL_ADMIN ?>&action=tours/delete&id=' + tourId;

                deleteModal.show();
            });
        });

        // Bulk actions
        document.getElementById('bulk-activate').addEventListener('click', function() {
            performBulkAction('active');
        });

        document.getElementById('bulk-deactivate').addEventListener('click', function() {
            performBulkAction('inactive');
        });

        document.getElementById('bulk-featured').addEventListener('click', function() {
            performBulkAction('featured');
        });

        function performBulkAction(action) {
            const selectedIds = Array.from(document.querySelectorAll('.tour-select:checked')).map(cb => cb.value);
            if (selectedIds.length === 0) return;

            const form = document.createElement('form');
            form.method = 'POST';

            if (action === 'featured') {
                form.action = '<?= BASE_URL_ADMIN ?>&action=tours/bulk-update-featured';
            } else {
                form.action = '<?= BASE_URL_ADMIN ?>&action=tours/bulk-update-status';
            }

            selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'tour_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            if (action !== 'featured') {
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = action;
                form.appendChild(statusInput);
            }

            document.body.appendChild(form);
            form.submit();
        }

        // Bulk delete
        const bulkDeleteModal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
        const bulkDeleteForm = document.getElementById('bulk-delete-form');

        document.getElementById('bulk-delete').addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.tour-select:checked')).map(cb => cb.value);
            if (selectedIds.length === 0) return;

            document.getElementById('bulk-delete-count').textContent = selectedIds.length;
            document.getElementById('bulk-tour-ids').value = JSON.stringify(selectedIds);

            bulkDeleteModal.show();
        });

        // Handle bulk delete form submission
        bulkDeleteForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const tourIds = JSON.parse(document.getElementById('bulk-tour-ids').value);

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= BASE_URL_ADMIN ?>&action=tours/bulk-delete';

            tourIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'tour_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        });

        // Gallery functionality
        document.querySelectorAll('.tour-card-modern').forEach(card => {
            const gallery = JSON.parse(card.dataset.gallery || '[]');
            const mainImg = card.querySelector('.main-img');
            const thumbs = card.querySelectorAll('.thumbnail-item img');

            if (thumbs.length > 0) {
                thumbs.forEach((thumb, index) => {
                    thumb.addEventListener('click', function() {
                        if (gallery[index + 1]) {
                            mainImg.src = gallery[index + 1];
                            mainImg.dataset.index = index + 1;

                            // Update active thumbnail
                            document.querySelectorAll('.thumbnail-item').forEach(item => {
                                item.classList.remove('active');
                            });
                            this.closest('.thumbnail-item').classList.add('active');
                        }
                    });
                });
            }

            if (mainImg && gallery.length > 0) {
                mainImg.addEventListener('click', function() {
                    // Simple lightbox implementation
                    const currentIndex = parseInt(this.dataset.index) || 0;
                    // You can integrate with a proper lightbox library here
                    console.log('Open lightbox at index:', currentIndex, gallery);
                });
            }
        });

        // View toggle functionality
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const view = this.dataset.view;
                const container = document.querySelector('.tours-grid');

                if (view === 'list') {
                    container.classList.add('list-view');
                } else {
                    container.classList.remove('list-view');
                }
            });
        });
    });


    // Helper functions
    function toggleAdvancedFilters() {
        const advancedFilters = document.querySelector('.advanced-filters');
        const toggleBtn = document.querySelector('[onclick="toggleAdvancedFilters()"]');

        if (advancedFilters.style.display === 'none') {
            advancedFilters.style.display = 'flex';
            toggleBtn.innerHTML = '<i class="fas fa-chevron-up"></i> Thu gọn';
        } else {
            advancedFilters.style.display = 'none';
            toggleBtn.innerHTML = '<i class="fas fa-cog"></i> Nâng cao';
        }
    }

    function resetFilters() {
        window.location.href = '<?= BASE_URL_ADMIN ?>&action=tours';
    }

    function showToast(message, type = 'success') {
        // Create toast element
        const toastHtml = `
        <div class="toast toast-${type} show" role="alert">
            <div class="toast-body">
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle me-2"></i>
                ${message}
            </div>
        </div>
    `;

        // Create container if not exists
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
            document.body.appendChild(container);
        }

        // Add toast to container
        const toastElement = document.createElement('div');
        toastElement.innerHTML = toastHtml;
        container.appendChild(toastElement.firstElementChild);

        // Remove after 3 seconds
        setTimeout(() => {
            toastElement.firstElementChild.remove();
        }, 3000);
    }


    // Client-side Filter Functions
    function resetFilters() {
        const form = document.getElementById('tour-filters');
        form.reset();
        filterTours();
    }

    function toggleAdvancedFilters() {
        const advancedFilters = document.querySelector('.advanced-filters');
        if (advancedFilters) {
            if (advancedFilters.style.display === 'none' || !advancedFilters.style.display) {
                advancedFilters.style.display = 'flex';
            } else {
                advancedFilters.style.display = 'none';
            }
        }
    }

    // Main filter function
    function filterTours() {
        const keyword = document.querySelector('input[name="keyword"]').value.toLowerCase();
        const categoryId = document.querySelector('select[name="category_id"]').value;
        const status = document.querySelector('select[name="status"]').value;
        const ratingMin = document.querySelector('select[name="rating_min"]').value;
        const priceMin = document.querySelector('input[name="price_min"]').value;
        const priceMax = document.querySelector('input[name="price_max"]').value;
        const sortBy = document.querySelector('select[name="sort_by"]').value;
        const sortDir = document.querySelector('select[name="sort_dir"]').value;

        const tourCards = Array.from(document.querySelectorAll('.tour-card-modern'));
        let visibleCount = 0;

        // Filter tours
        tourCards.forEach(card => {
            let show = true;

            // Keyword search
            if (keyword) {
                const tourName = card.querySelector('.tour-title').textContent.toLowerCase();
                if (!tourName.includes(keyword)) {
                    show = false;
                }
            }

            // Category filter
            if (categoryId && show) {
                const categoryBadge = card.querySelector('.category-badge');
                const cardCategoryName = categoryBadge ? categoryBadge.textContent.trim() : '';
                const selectedCategory = document.querySelector(`select[name="category_id"] option[value="${categoryId}"]`);
                const selectedCategoryName = selectedCategory ? selectedCategory.textContent.trim() : '';
                if (cardCategoryName !== selectedCategoryName) {
                    show = false;
                }
            }

            // Status filter
            if (status && show) {
                const statusBadge = card.querySelector('.badge-status');
                const isActive = statusBadge && statusBadge.classList.contains('badge-active');
                if ((status === 'active' && !isActive) || (status === 'inactive' && isActive)) {
                    show = false;
                }
            }

            // Rating filter
            if (ratingMin && show) {
                const ratingValue = card.querySelector('.rating-value');
                const rating = ratingValue ? parseFloat(ratingValue.textContent) : 0;
                if (rating < parseFloat(ratingMin)) {
                    show = false;
                }
            }

            // Price filter
            if ((priceMin || priceMax) && show) {
                const priceText = card.querySelector('.price-value').textContent.replace(/[^\d]/g, '');
                const price = parseInt(priceText) || 0;
                if (priceMin && price < parseInt(priceMin)) {
                    show = false;
                }
                if (priceMax && price > parseInt(priceMax)) {
                    show = false;
                }
            }

            // Show/hide card
            if (show) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Sort tours
        if (sortBy) {
            sortTours(tourCards, sortBy, sortDir);
        }

        // Update count
        updateTourCount(visibleCount);

        // Show empty state if no results
        const emptyState = document.querySelector('.empty-state');
        const toursGrid = document.querySelector('.tours-grid');
        if (visibleCount === 0) {
            if (toursGrid) toursGrid.style.display = 'none';
            if (emptyState) emptyState.style.display = 'flex';
        } else {
            if (toursGrid) toursGrid.style.display = 'grid';
            if (emptyState) emptyState.style.display = 'none';
        }
    }

    // Sort function
    function sortTours(tourCards, sortBy, sortDir) {
        const toursGrid = document.querySelector('.tours-grid');
        if (!toursGrid) return;

        const visibleCards = tourCards.filter(card => card.style.display !== 'none');

        visibleCards.sort((a, b) => {
            let aValue, bValue;

            switch (sortBy) {
                case 'name':
                    aValue = a.querySelector('.tour-title').textContent.toLowerCase();
                    bValue = b.querySelector('.tour-title').textContent.toLowerCase();
                    break;
                case 'price':
                    aValue = parseInt(a.querySelector('.price-value').textContent.replace(/[^\d]/g, '')) || 0;
                    bValue = parseInt(b.querySelector('.price-value').textContent.replace(/[^\d]/g, '')) || 0;
                    break;
                case 'rating':
                    aValue = parseFloat(a.querySelector('.rating-value').textContent) || 0;
                    bValue = parseFloat(b.querySelector('.rating-value').textContent) || 0;
                    break;
                default:
                    return 0;
            }

            if (aValue < bValue) return sortDir === 'ASC' ? -1 : 1;
            if (aValue > bValue) return sortDir === 'ASC' ? 1 : -1;
            return 0;
        });

        // Re-append sorted cards
        visibleCards.forEach(card => {
            toursGrid.appendChild(card);
        });
    }

    // Update tour count
    function updateTourCount(count) {
        const countInfo = document.querySelector('.count-info');
        if (countInfo) {
            const currentPage = <?= $pagination['page'] ?? 1 ?>;
            const totalPages = <?= max(1, $pagination['total_pages'] ?? 1) ?>;
            countInfo.textContent = `${count} tour • Trang ${currentPage}/${totalPages}`;
        }
    }

    // Setup event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('tour-filters');
        if (!filterForm) return;

        // Prevent form submission and filter when search button is clicked
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            filterTours();
        });

        // Also filter when Enter is pressed in keyword input
        const keywordInput = document.querySelector('input[name="keyword"]');
        if (keywordInput) {
            keywordInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    filterTours();
                }
            });
        }
    });
</script>
<!-- QR Code Functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const qrModal = document.getElementById('qrModal');
        const qrCodeContainer = document.getElementById('qrcode');
        const tourLinkInput = document.getElementById('tour-link');
        const qrTourName = document.getElementById('qr-tour-name');
        const copyBtn = document.getElementById('copy-link-btn');
        const copyAlert = document.getElementById('copy-success-alert');
        let qrcodeObj = null;

        document.querySelectorAll('.btn-qr').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const tourId = this.dataset.id;
                const tourName = this.dataset.name;
                // Construct public URL
                const publicUrl = `<?= BASE_URL ?>?action=tour-detail&id=${tourId}`;

                // Update Modal Content
                qrTourName.textContent = tourName;
                tourLinkInput.value = publicUrl;
                copyAlert.classList.add('d-none');

                // Generate QR
                qrCodeContainer.innerHTML = '';
                qrcodeObj = new QRCode(qrCodeContainer, {
                    text: publicUrl,
                    width: 180,
                    height: 180,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });

                // Show Modal
                const modal = new bootstrap.Modal(qrModal);
                modal.show();
            });
        });

        copyBtn.addEventListener('click', function() {
            tourLinkInput.select();
            tourLinkInput.setSelectionRange(0, 99999); /* For mobile devices */
            navigator.clipboard.writeText(tourLinkInput.value).then(function() {
                copyAlert.classList.remove('d-none');
                setTimeout(() => {
                    copyAlert.classList.add('d-none');
                }, 2000);
            });
        });
    });
</script>
<!-- QRCode.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>