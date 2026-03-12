<?php
$pageTitle = 'VietTour – Khám phá tour du lịch Việt Nam';
require_once PATH_VIEW_CLIENT . 'default/header.php';

// Helper format tiền
function formatPrice($price) {
    return number_format((float)$price, 0, ',', '.') . 'đ';
}

// Build current query string (for pagination links)
function buildQuery($extra = []) {
    $params = array_merge($_GET, $extra);
    unset($params['mode']);
    return '?' . http_build_query($params);
}
?>

<!-- HERO -->
<section class="vt-hero">
    <div class="container">
        <h1><i class="fas fa-compass" style="margin-right:10px"></i>Khám phá tour<br>du lịch tuyệt vời</h1>
        <p>Hàng trăm tour chất lượng cao, giá tốt nhất — đặt ngay hôm nay!</p>

        <!-- Search Form -->
        <form action="<?= BASE_URL ?>" method="GET" class="vt-search-bar">
            <input type="hidden" name="action" value="tour-list">
            <input type="text" name="keyword" placeholder="🔍  Tìm tên tour, địa điểm..."
                   value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
            <select name="category_id">
                <option value="">🗂  Tất cả danh mục</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= (($_GET['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="vt-search-btn"><i class="fas fa-search"></i> Tìm kiếm</button>
        </form>
    </div>
</section>

<!-- MAIN CONTENT -->
<section class="py-5">
    <div class="container">

        <!-- Filter Bar -->
        <form method="GET" action="<?= BASE_URL ?>" class="filter-bar mb-4">
            <input type="hidden" name="action" value="tour-list">
            <?php if (!empty($_GET['keyword'])): ?>
                <input type="hidden" name="keyword" value="<?= htmlspecialchars($_GET['keyword']) ?>">
            <?php endif; ?>
            <?php if (!empty($_GET['category_id'])): ?>
                <input type="hidden" name="category_id" value="<?= (int)$_GET['category_id'] ?>">
            <?php endif; ?>

            <label><i class="fas fa-sort-amount-down"></i> Sắp xếp:</label>
            <select name="sort_by" onchange="this.form.submit()">
                <option value="created_at" <?= (($_GET['sort_by'] ?? '') === 'created_at') ? 'selected' : '' ?>>Mới nhất</option>
                <option value="price"      <?= (($_GET['sort_by'] ?? '') === 'price') ? 'selected' : '' ?>>Theo giá</option>
                <option value="name"       <?= (($_GET['sort_by'] ?? '') === 'name') ? 'selected' : '' ?>>Tên A–Z</option>
                <option value="rating"     <?= (($_GET['sort_by'] ?? '') === 'rating') ? 'selected' : '' ?>>Đánh giá cao</option>
            </select>
            <select name="sort_dir" onchange="this.form.submit()">
                <option value="DESC" <?= (($_GET['sort_dir'] ?? 'DESC') === 'DESC') ? 'selected' : '' ?>>Giảm dần</option>
                <option value="ASC"  <?= (($_GET['sort_dir'] ?? '') === 'ASC') ? 'selected' : '' ?>>Tăng dần</option>
            </select>

            <label style="margin-left:auto">Giá từ:</label>
            <input type="number" name="price_min" placeholder="0" min="0" step="100000"
                   value="<?= $_GET['price_min'] ?? '' ?>" style="width:120px">
            <label>đến:</label>
            <input type="number" name="price_max" placeholder="Không giới hạn" min="0" step="100000"
                   value="<?= $_GET['price_max'] ?? '' ?>" style="width:150px">
            <button type="submit" class="btn-detail">Lọc</button>
            <?php if (!empty($_GET['price_min']) || !empty($_GET['price_max']) || !empty($_GET['sort_by'])): ?>
                <a href="<?= BASE_URL ?>?action=tour-list" style="font-size:.85rem;color:var(--gray-500)">×  Xóa lọc</a>
            <?php endif; ?>
        </form>

        <!-- Result Count -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <span class="section-title" style="font-size:1.2rem">
                    <?php if (!empty($_GET['keyword'])): ?>
                        Kết quả cho "<strong><?= htmlspecialchars($_GET['keyword']) ?></strong>"
                    <?php else: ?>
                        Danh sách tour
                    <?php endif; ?>
                </span>
                <span class="ms-2" style="font-size:.88rem;color:var(--gray-500)">(<?= $total ?> tour)</span>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert-vt alert-success mb-4"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Tour Grid -->
        <?php if (empty($tours)): ?>
            <div class="vt-empty">
                <div class="empty-icon"><i class="fas fa-map-signs"></i></div>
                <h4>Không tìm thấy tour nào</h4>
                <p>Thử thay đổi từ khóa hoặc xóa bộ lọc để xem thêm tour.</p>
                <a href="<?= BASE_URL ?>?action=tour-list" class="btn-register" style="display:inline-block;margin-top:16px">Xem tất cả tours</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($tours as $tour): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="tour-card">
                            <!-- Image -->
                            <div class="position-relative">
                                <?php if (!empty($tour['main_image'])): ?>
                                    <img src="<?= BASE_ASSETS_UPLOADS ?><?= htmlspecialchars($tour['main_image']) ?>"
                                         alt="<?= htmlspecialchars($tour['name']) ?>"
                                         class="tour-card-img">
                                <?php else: ?>
                                    <div class="tour-card-img-placeholder">
                                        <i class="fas fa-mountain"></i>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($tour['featured'])): ?>
                                    <span class="tour-card-badge featured">⭐ Nổi bật</span>
                                <?php endif; ?>
                            </div>

                            <!-- Body -->
                            <div class="tour-card-body">
                                <?php if (!empty($tour['category_name'])): ?>
                                    <span class="tour-category-tag">
                                        <i class="fas fa-tag" style="font-size:.7rem"></i>
                                        <?= htmlspecialchars($tour['category_name']) ?>
                                    </span>
                                <?php endif; ?>

                                <div class="tour-card-title"><?= htmlspecialchars($tour['name']) ?></div>

                                <div class="tour-card-meta">
                                    <?php if (!empty($tour['duration_days'])): ?>
                                        <span><i class="far fa-clock"></i> <?= $tour['duration_days'] ?> ngày</span>
                                    <?php endif; ?>
                                    <?php if (!empty($tour['start_location'])): ?>
                                        <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($tour['start_location']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <!-- Rating -->
                                <?php $rating = round((float)($tour['avg_rating'] ?? 0), 1); ?>
                                <?php if ($rating > 0): ?>
                                    <div class="star-rating mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?= $i <= $rating ? '' : ($i - 0.5 <= $rating ? '-half-alt' : '') ?>"
                                               style="<?= $i > $rating ? 'color:#d1d5db' : '' ?>"></i>
                                        <?php endfor; ?>
                                        <span style="color:var(--gray-500);font-size:.78rem;margin-left:4px"><?= $rating ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="tour-card-footer">
                                    <div>
                                        <div class="tour-price">
                                            <?= formatPrice($tour['base_price']) ?>
                                            <small>/người</small>
                                        </div>
                                    </div>
                                    <a href="<?= BASE_URL ?>?action=tour-detail&id=<?= $tour['id'] ?>" class="btn-detail">
                                        Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="vt-pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= BASE_URL . buildQuery(['page' => $page - 1]) ?>"><i class="fas fa-chevron-left"></i></a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end   = min($totalPages, $page + 2);
                    if ($start > 1) { ?>
                        <a href="<?= BASE_URL . buildQuery(['page' => 1]) ?>">1</a>
                        <?php if ($start > 2): ?><span class="dots">…</span><?php endif;
                    }
                    for ($i = $start; $i <= $end; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="<?= BASE_URL . buildQuery(['page' => $i]) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($end < $totalPages): ?>
                        <?php if ($end < $totalPages - 1): ?><span class="dots">…</span><?php endif; ?>
                        <a href="<?= BASE_URL . buildQuery(['page' => $totalPages]) ?>"><?= $totalPages ?></a>
                    <?php endif; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= BASE_URL . buildQuery(['page' => $page + 1]) ?>"><i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</section>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
