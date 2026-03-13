<?php
$pageTitle = 'VietTour – Khám phá Việt Nam cùng chúng tôi';
$metaDescription = 'VietTour – Nền tảng đặt tour du lịch trực tuyến hàng đầu Việt Nam. Hàng trăm tour chất lượng cao, giá tốt, đặt dễ dàng.';
require_once PATH_VIEW_CLIENT . 'default/header.php';

function homFormatPrice($price) {
    return number_format((float)$price, 0, ',', '.') . 'đ';
}
?>

<!-- ===================== HERO ===================== -->
<section class="vt-home-hero">
    <div class="vt-home-hero-bg"></div>
    <div class="container vt-home-hero-content">
        <div class="row align-items-center min-vh-hero">
            <div class="col-lg-7">
                <span class="hero-badge"><i class="fas fa-star me-1"></i>Nền tảng du lịch #1 Việt Nam</span>
                <h1 class="hero-title">Khám phá vẻ đẹp <span class="text-gradient">Việt Nam</span> cùng chúng tôi</h1>
                <p class="hero-subtitle">Hàng trăm tour chất lượng cao, giá tốt nhất — đặt ngay hôm nay và tạo nên những kỷ niệm đáng nhớ.</p>

                <!-- Search Bar -->
                <form action="<?= BASE_URL ?>" method="GET" class="hero-search-form">
                    <input type="hidden" name="action" value="tour-list">
                    <div class="hero-search-inner">
                        <div class="hero-search-field">
                            <i class="fas fa-search"></i>
                            <input type="text" name="keyword" placeholder="Tìm tên tour, địa điểm..." autocomplete="off">
                        </div>
                        <div class="hero-search-field">
                            <i class="fas fa-tag"></i>
                            <select name="category_id">
                                <option value="">Tất cả danh mục</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="hero-search-btn">
                            <i class="fas fa-search me-2"></i>Tìm kiếm
                        </button>
                    </div>
                </form>

                <!-- Quick Tags -->
                <div class="hero-tags">
                    <span>Tìm nhanh:</span>
                    <a href="<?= BASE_URL ?>?action=tour-list&sort_by=price&sort_dir=ASC">Tour giá rẻ</a>
                    <a href="<?= BASE_URL ?>?action=tour-list&sort_by=rating&sort_dir=DESC">Đánh giá cao</a>
                    <?php foreach (array_slice($categories, 0, 3) as $cat): ?>
                        <a href="<?= BASE_URL ?>?action=tour-list&category_id=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-lg-5 d-none d-lg-block">
                <div class="hero-stats-grid">
                    <div class="hero-stat-card">
                        <i class="fas fa-map-marked-alt"></i>
                        <span class="stat-num"><?= number_format($statsRow['total_tours'] ?? 0) ?>+</span>
                        <span class="stat-label">Tour du lịch</span>
                    </div>
                    <div class="hero-stat-card">
                        <i class="fas fa-users"></i>
                        <span class="stat-num"><?= number_format($statsRow['total_customers'] ?? 0) ?>+</span>
                        <span class="stat-label">Khách hàng</span>
                    </div>
                    <div class="hero-stat-card">
                        <i class="fas fa-calendar-check"></i>
                        <span class="stat-num"><?= number_format($statsRow['total_bookings'] ?? 0) ?>+</span>
                        <span class="stat-label">Đặt tour</span>
                    </div>
                    <div class="hero-stat-card">
                        <i class="fas fa-star"></i>
                        <span class="stat-num">4.8</span>
                        <span class="stat-label">Đánh giá TB</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-wave">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none"><path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" fill="#f8fafc"/></svg>
    </div>
</section>

<!-- ===================== DANH MỤC ===================== -->
<?php if (!empty($categories)): ?>
<section class="vt-section py-5 bg-light-subtle">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-tag">Khám phá</span>
            <h2 class="section-title">Danh mục tour</h2>
            <p class="section-sub">Lựa chọn loại hình du lịch phù hợp với bạn</p>
        </div>
        <div class="row g-3 justify-content-center">
            <?php
            $catIcons = ['fa-mountain','fa-globe-asia','fa-umbrella-beach','fa-city','fa-tree','fa-ship','fa-hiking','fa-campground'];
            $catColors = ['#1e6fff','#e74c3c','#f39c12','#27ae60','#8e44ad','#16a085','#d35400','#2980b9'];
            foreach ($categories as $i => $cat):
                $icon  = $catIcons[$i % count($catIcons)];
                $color = $catColors[$i % count($catColors)];
            ?>
            <div class="col-6 col-md-4 col-lg-3">
                <a href="<?= BASE_URL ?>?action=tour-list&category_id=<?= $cat['id'] ?>" class="cat-card">
                    <div class="cat-icon" style="background:<?= $color ?>22; color:<?= $color ?>">
                        <i class="fas <?= $icon ?>"></i>
                    </div>
                    <div class="cat-info">
                        <h6><?= htmlspecialchars($cat['name']) ?></h6>
                        <small><?= $cat['tour_count'] ?? '0' ?> tour</small>
                    </div>
                    <i class="fas fa-arrow-right cat-arrow"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== TOUR NỔI BẬT ===================== -->
<section class="vt-section py-5">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-end mb-5">
            <div>
                <span class="section-tag">Được yêu thích</span>
                <h2 class="section-title mb-0">Tour nổi bật</h2>
            </div>
            <a href="<?= BASE_URL ?>?action=tour-list" class="btn-see-all">
                Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>

        <?php if (empty($featuredTours)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-map-signs fa-3x mb-3 opacity-25"></i>
                <p>Chưa có tour nào được đăng. Hãy quay lại sau!</p>
                <a href="<?= BASE_URL ?>?action=tour-list" class="btn btn-primary">Xem danh sách tour</a>
            </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($featuredTours as $tour): ?>
            <div class="col-lg-4 col-md-6">
                <div class="tour-card home-tour-card">
                    <!-- Image -->
                    <div class="tour-card-img-wrap position-relative">
                        <?php if (!empty($tour['main_image'])): ?>
                            <img src="<?= BASE_ASSETS_UPLOADS . htmlspecialchars($tour['main_image']) ?>"
                                 alt="<?= htmlspecialchars($tour['name']) ?>"
                                 class="tour-card-img">
                        <?php else: ?>
                            <div class="tour-card-img-placeholder">
                                <i class="fas fa-mountain"></i>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($tour['featured'])): ?>
                            <span class="tour-card-badge featured"><i class="fas fa-star me-1"></i>Nổi bật</span>
                        <?php endif; ?>

                        <?php if (!empty($tour['category_name'])): ?>
                            <span class="tour-card-badge category"><?= htmlspecialchars($tour['category_name']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Body -->
                    <div class="tour-card-body">
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
                                    <?= homFormatPrice($tour['base_price']) ?>
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
        <?php endif; ?>
    </div>
</section>

<!-- ===================== TẠI SAO CHỌN VIETTOUR ===================== -->
<section class="vt-section py-5 why-us-section">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-tag">Cam kết</span>
            <h2 class="section-title">Tại sao chọn VietTour?</h2>
            <p class="section-sub">Chúng tôi cam kết mang đến trải nghiệm du lịch tốt nhất cho bạn</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="why-card">
                    <div class="why-icon" style="background: #e8f4fd; color: #1e6fff">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5>Đảm bảo chất lượng</h5>
                    <p>Tất cả tour được kiểm duyệt kỹ lưỡng, cam kết đúng như mô tả.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="why-card">
                    <div class="why-icon" style="background: #fef9e7; color: #f39c12">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h5>Giá tốt nhất</h5>
                    <p>Cam kết giá cạnh tranh, không phát sinh phí ẩn sau khi đặt tour.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="why-card">
                    <div class="why-icon" style="background: #eafaf1; color: #27ae60">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5>Hỗ trợ 24/7</h5>
                    <p>Đội ngũ tư vấn luôn sẵn sàng hỗ trợ bạn mọi lúc, mọi nơi.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="why-card">
                    <div class="why-icon" style="background: #f5eef8; color: #8e44ad">
                        <i class="fas fa-undo-alt"></i>
                    </div>
                    <h5>Đổi/Hủy linh hoạt</h5>
                    <p>Chính sách hoàn tiền minh bạch, dễ dàng thay đổi lịch trình.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== CTA BANNER ===================== -->
<section class="vt-cta-section">
    <div class="container">
        <div class="cta-box">
            <div class="cta-content">
                <h2>Sẵn sàng cho chuyến đi tiếp theo?</h2>
                <p>Đăng ký ngay để nhận ưu đãi độc quyền và thông báo tour mới nhất!</p>
                <div class="cta-actions">
                    <a href="<?= BASE_URL ?>?action=tour-list" class="cta-btn-primary">
                        <i class="fas fa-compass me-2"></i>Khám phá tour ngay
                    </a>
                    <?php if (empty($_SESSION['user'])): ?>
                    <a href="<?= BASE_URL ?>?action=register" class="cta-btn-secondary">
                        <i class="fas fa-user-plus me-2"></i>Đăng ký miễn phí
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="cta-decoration">
                <i class="fas fa-paper-plane cta-icon-1"></i>
                <i class="fas fa-map-marked-alt cta-icon-2"></i>
                <i class="fas fa-globe-asia cta-icon-3"></i>
            </div>
        </div>
    </div>
</section>

<style>
/* ====== HERO ====== */
.vt-home-hero {
    position: relative;
    background: linear-gradient(135deg, #0a1628 0%, #1a2f5e 50%, #1e6fff 100%);
    min-height: 85vh;
    display: flex;
    align-items: center;
    overflow: hidden;
}
.vt-home-hero-bg {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 20% 50%, rgba(30,111,255,.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(107,143,248,.12) 0%, transparent 40%),
        url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.vt-home-hero-content { position: relative; z-index: 2; padding: 120px 0 80px; }
.min-vh-hero { min-height: auto; }
.hero-badge {
    display: inline-flex; align-items: center;
    background: rgba(255,255,255,.12); color: #a5c4f3;
    border: 1px solid rgba(255,255,255,.2);
    padding: 6px 16px; border-radius: 50px;
    font-size: .82rem; font-weight: 600; letter-spacing: .5px;
    margin-bottom: 20px; backdrop-filter: blur(10px);
}
.hero-title {
    font-size: clamp(2rem, 5vw, 3.2rem);
    font-weight: 800; color: #fff; line-height: 1.2;
    margin-bottom: 1rem;
}
.text-gradient {
    background: linear-gradient(90deg, #60a5fa, #a78bfa);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.hero-subtitle { color: rgba(255,255,255,.75); font-size: 1.1rem; margin-bottom: 2rem; }

/* Search Form */
.hero-search-form { margin-bottom: 1.5rem; }
.hero-search-inner {
    display: flex; gap: 0;
    background: #fff; border-radius: 14px;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
    overflow: hidden;
}
.hero-search-field {
    display: flex; align-items: center;
    flex: 1; padding: 0 16px;
    border-right: 1px solid #e5e7eb;
    position: relative;
}
.hero-search-field i { color: #9ca3af; margin-right: 10px; flex-shrink: 0; }
.hero-search-field input, .hero-search-field select {
    border: none; outline: none; width: 100%;
    font-size: .92rem; color: #111827;
    background: transparent; padding: 16px 0;
}
.hero-search-field select { cursor: pointer; }
.hero-search-btn {
    background: linear-gradient(135deg, #1e6fff, #5b8ef8);
    color: #fff; border: none; padding: 0 28px;
    font-weight: 700; font-size: .92rem; cursor: pointer;
    transition: all .2s; white-space: nowrap;
}
.hero-search-btn:hover { background: linear-gradient(135deg, #1558e0, #4a7ef0); }
.hero-tags { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.hero-tags > span { color: rgba(255,255,255,.6); font-size: .82rem; }
.hero-tags a {
    background: rgba(255,255,255,.12); color: #c7d9f8;
    padding: 4px 12px; border-radius: 50px;
    font-size: .8rem; text-decoration: none;
    border: 1px solid rgba(255,255,255,.15); transition: all .2s;
}
.hero-tags a:hover { background: rgba(255,255,255,.2); color: #fff; }

/* Stats Grid */
.hero-stats-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 16px; padding: 20px 0 20px 40px;
}
.hero-stat-card {
    background: rgba(255,255,255,.08);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 16px; padding: 24px 20px;
    text-align: center; color: #fff;
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    transition: transform .2s;
}
.hero-stat-card:hover { transform: translateY(-4px); }
.hero-stat-card i { font-size: 1.5rem; color: #60a5fa; }
.stat-num { font-size: 1.6rem; font-weight: 800; }
.stat-label { font-size: .78rem; color: rgba(255,255,255,.7); }

.hero-wave { position: absolute; bottom: 0; left: 0; right: 0; line-height: 0; }
.hero-wave svg { width: 100%; height: 60px; }

/* ====== SECTIONS ====== */
.vt-section { }
.section-tag {
    display: inline-block;
    background: linear-gradient(135deg, #e8f0fe, #d4e4ff);
    color: #1e6fff; padding: 4px 14px; border-radius: 50px;
    font-size: .78rem; font-weight: 700; letter-spacing: .5px;
    text-transform: uppercase; margin-bottom: 12px;
}
.section-title { font-size: 2rem; font-weight: 800; color: #111827; }
.section-sub { color: #6b7280; font-size: 1rem; margin-top: 8px; }
.btn-see-all {
    color: #1e6fff; font-weight: 700; text-decoration: none;
    font-size: .92rem; display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 20px; border: 2px solid #1e6fff; border-radius: 50px;
    transition: all .2s;
}
.btn-see-all:hover { background: #1e6fff; color: #fff; }

/* ====== CATEGORY CARDS ====== */
.cat-card {
    display: flex; align-items: center; gap: 14px;
    background: #fff; border: 1.5px solid #e5e7eb;
    border-radius: 14px; padding: 16px 18px;
    text-decoration: none; color: #111827;
    transition: all .2s; box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.cat-card:hover { border-color: #1e6fff; box-shadow: 0 8px 24px rgba(30,111,255,.12); transform: translateY(-2px); }
.cat-icon {
    width: 46px; height: 46px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.cat-info h6 { font-size: .88rem; font-weight: 700; margin: 0; }
.cat-info small { color: #9ca3af; font-size: .78rem; }
.cat-arrow { margin-left: auto; color: #d1d5db; font-size: .8rem; transition: transform .2s; }
.cat-card:hover .cat-arrow { color: #1e6fff; transform: translateX(4px); }

/* ====== HOME TOUR CARDS ====== */
.home-tour-card .tour-card-img-wrap { position: relative; overflow: hidden; border-radius: 14px 14px 0 0; height: 200px; }
.home-tour-card .tour-card-img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
.home-tour-card:hover .tour-card-img { transform: scale(1.06); }
.home-tour-card .tour-card-img-placeholder { height: 200px; display: flex; align-items: center; justify-content: center; background: #e5e7eb; font-size: 3rem; color: #9ca3af; }
.tour-card-badge.category {
    position: absolute; bottom: 10px; left: 10px;
    background: rgba(0,0,0,.6); color: #fff;
    font-size: .72rem; font-weight: 600; padding: 3px 10px; border-radius: 50px;
}
.tour-card-badge.featured {
    position: absolute; top: 10px; right: 10px;
    background: linear-gradient(135deg, #f59e0b, #f97316);
    color: #fff; font-size: .72rem; font-weight: 700;
    padding: 4px 10px; border-radius: 50px;
}

/* ====== WHY US ====== */
.why-us-section { background: #f8fafc; }
.why-card {
    background: #fff; border-radius: 18px;
    padding: 32px 24px; text-align: center;
    border: 1.5px solid #f1f5f9;
    transition: all .3s; box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.why-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,.08); border-color: #e0e7ff; }
.why-icon {
    width: 64px; height: 64px; border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; margin: 0 auto 16px;
}
.why-card h5 { font-size: 1rem; font-weight: 700; margin-bottom: 8px; color: #111827; }
.why-card p { color: #6b7280; font-size: .88rem; margin: 0; line-height: 1.6; }

/* ====== CTA ====== */
.vt-cta-section { padding: 80px 0; background: linear-gradient(135deg, #0a1628, #1e6fff); }
.cta-box {
    display: flex; justify-content: space-between; align-items: center;
    position: relative; overflow: hidden;
}
.cta-content { position: relative; z-index: 2; }
.cta-content h2 { color: #fff; font-size: 2rem; font-weight: 800; margin-bottom: 10px; }
.cta-content p { color: rgba(255,255,255,.8); font-size: 1rem; margin-bottom: 24px; }
.cta-actions { display: flex; gap: 12px; flex-wrap: wrap; }
.cta-btn-primary {
    background: #fff; color: #1e6fff; font-weight: 700;
    padding: 14px 28px; border-radius: 50px; text-decoration: none;
    transition: all .2s; display: inline-flex; align-items: center;
}
.cta-btn-primary:hover { background: #f1f5ff; transform: translateY(-2px); color: #1558e0; }
.cta-btn-secondary {
    background: rgba(255,255,255,.12); color: #fff; font-weight: 600;
    padding: 14px 28px; border-radius: 50px; text-decoration: none;
    border: 2px solid rgba(255,255,255,.3); transition: all .2s;
    display: inline-flex; align-items: center;
}
.cta-btn-secondary:hover { background: rgba(255,255,255,.2); color: #fff; }
.cta-decoration { position: absolute; right: 0; top: 50%; transform: translateY(-50%); opacity: .07; }
.cta-icon-1 { font-size: 5rem; position: absolute; right: 200px; top: -20px; }
.cta-icon-2 { font-size: 8rem; position: absolute; right: 80px; top: -30px; }
.cta-icon-3 { font-size: 6rem; position: absolute; right: 0; top: 10px; }
</style>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
