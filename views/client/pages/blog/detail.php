<?php
$pageTitle = $pageTitle ?? ($post['title'] ?? 'Chi tiết bài viết');
$metaDescription = htmlspecialchars($post['excerpt'] ?? '');
require_once PATH_VIEW_CLIENT . 'default/header.php';
?>

<style>
.blog-detail-hero {
    background: linear-gradient(135deg, #0a1628, #1e2d5a);
    padding: 70px 0 50px;
    position: relative;
    overflow: hidden;
}
.blog-detail-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 20% 50%, rgba(30,111,255,.15) 0%, transparent 60%);
}
.blog-detail-hero .container { position: relative; z-index: 2; }
.blog-category-badge {
    background: rgba(30,111,255,.2); color: #60a5fa;
    border: 1px solid rgba(96,165,250,.3);
    padding: 4px 14px; border-radius: 50px;
    font-size: .78rem; font-weight: 600;
    display: inline-block; margin-bottom: 16px;
}
.blog-detail-title { color: #fff; font-size: clamp(1.6rem, 4vw, 2.4rem); font-weight: 800; line-height: 1.3; }
.blog-detail-meta { color: rgba(255,255,255,.65); font-size: .88rem; margin-top: 16px; }
.blog-detail-meta span { margin-right: 20px; }
.blog-detail-meta i { margin-right: 4px; }

.blog-content-wrap { background: #fff; border-radius: 20px; padding: 40px; box-shadow: 0 4px 24px rgba(0,0,0,.06); margin-bottom: 32px; }
.blog-thumbnail { width: 100%; max-height: 420px; object-fit: cover; border-radius: 16px; margin-bottom: 32px; }
.blog-content { font-size: 1rem; line-height: 1.9; color: #374151; }
.blog-content h2, .blog-content h3 { font-weight: 700; color: #111827; margin: 24px 0 12px; }
.blog-content p { margin-bottom: 16px; }
.blog-content img { max-width: 100%; border-radius: 12px; margin: 16px 0; }
.blog-content a { color: #1e6fff; }
.blog-content blockquote {
    border-left: 4px solid #1e6fff; padding-left: 20px;
    margin: 20px 0; color: #6b7280; font-style: italic;
    background: #f0f7ff; border-radius: 0 12px 12px 0; padding: 16px 20px;
}

/* Review Form */
.review-form-card {
    background: #fff; border-radius: 20px;
    padding: 32px; box-shadow: 0 4px 24px rgba(0,0,0,.06);
}
.star-input { display: flex; gap: 6px; margin-bottom: 16px; }
.star-input label {
    font-size: 1.8rem; cursor: pointer; color: #d1d5db;
    transition: color .1s; line-height: 1;
}
.star-input input[type=radio] { display: none; }
.star-input input:checked ~ label,
.star-input label:hover,
.star-input label:hover ~ label { color: #f59e0b; }

/* Related posts */
.related-card {
    background: #fff; border-radius: 14px;
    border: 1.5px solid #f1f5f9;
    overflow: hidden; transition: all .3s;
    text-decoration: none; color: inherit;
    display: block; margin-bottom: 16px;
    display: flex; gap: 12px; padding: 12px;
}
.related-card:hover { border-color: #1e6fff33; box-shadow: 0 4px 16px rgba(30,111,255,.1); }
.related-card img { width: 80px; height: 70px; object-fit: cover; border-radius: 10px; flex-shrink: 0; }
.related-card .rc-title { font-size: .88rem; font-weight: 700; color: #111827; margin-bottom: 4px; line-height: 1.3; }
.related-card .rc-date { font-size: .76rem; color: #9ca3af; }
</style>

<!-- Hero -->
<section class="blog-detail-hero">
    <div class="container">
        <span class="blog-category-badge"><i class="fas fa-newspaper me-1"></i>Bài viết</span>
        <h1 class="blog-detail-title"><?= htmlspecialchars($post['title']) ?></h1>
        <div class="blog-detail-meta">
            <span><i class="fas fa-user"></i><?= htmlspecialchars($post['author_name'] ?? 'Admin') ?></span>
            <span><i class="far fa-calendar"></i><?= date('d/m/Y', strtotime($post['published_at'] ?? $post['created_at'])) ?></span>
            <span><i class="fas fa-eye"></i><?= number_format($post['views'] ?? 0) ?> lượt xem</span>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="row g-4">
        <!-- Main content -->
        <div class="col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?action=blog">Blog</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars(mb_substr($post['title'], 0, 50)) ?>…</li>
                </ol>
            </nav>

            <div class="blog-content-wrap">
                <?php if (!empty($post['thumbnail'])): ?>
                    <img src="<?= BASE_ASSETS_UPLOADS . $post['thumbnail'] ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="blog-thumbnail">
                <?php endif; ?>
                <div class="blog-content">
                    <?= $post['content'] ?>
                </div>
            </div>

            <!-- Review section (nếu là trang blog – không áp dụng) -->

            <!-- Share buttons -->
            <div class="card border-0 bg-light rounded-3 p-4 mb-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-share-alt me-2 text-primary"></i>Chia sẻ bài viết</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <?php $shareUrl = urlencode(BASE_URL . '?action=blog/detail&slug=' . $post['slug']); ?>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" class="btn btn-sm" style="background:#1877f2;color:#fff;border-radius:50px">
                        <i class="fab fa-facebook-f me-1"></i>Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= urlencode($post['title']) ?>" target="_blank" class="btn btn-sm" style="background:#1da1f2;color:#fff;border-radius:50px">
                        <i class="fab fa-twitter me-1"></i>Twitter
                    </a>
                    <button onclick="navigator.clipboard.writeText('<?= BASE_URL ?>?action=blog/detail&slug=<?= $post['slug'] ?>').then(()=>alert('Đã sao chép link!'))" class="btn btn-sm btn-outline-secondary" style="border-radius:50px">
                        <i class="fas fa-link me-1"></i>Sao chép link
                    </button>
                </div>
            </div>

            <!-- Back -->
            <a href="<?= BASE_URL ?>?action=blog" class="btn btn-outline-primary rounded-pill">
                <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách bài viết
            </a>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Xem tour -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-compass text-primary me-2"></i>Khám phá tour</h5>
                    <p class="text-muted small mb-3">Tìm và đặt ngay những tour du lịch tuyệt vời nhất!</p>
                    <a href="<?= BASE_URL ?>?action=tour-list" class="btn btn-primary w-100 rounded-pill">
                        <i class="fas fa-map-marked-alt me-2"></i>Xem tất cả tour
                    </a>
                </div>
            </div>

            <!-- Related posts -->
            <?php if (!empty($related)): ?>
            <div class="card border-0 shadow-sm" style="border-radius:16px">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-bookmark text-warning me-2"></i>Bài viết liên quan</h5>
                    <?php foreach ($related as $r): ?>
                    <a href="<?= BASE_URL ?>?action=blog/detail&slug=<?= $r['slug'] ?>" class="related-card">
                        <?php if (!empty($r['thumbnail'])): ?>
                            <img src="<?= BASE_ASSETS_UPLOADS . $r['thumbnail'] ?>" alt="">
                        <?php else: ?>
                            <div style="width:80px;height:70px;background:#e0e7ff;border-radius:10px;flex-shrink:0;display:flex;align-items:center;justify-content:center;color:#93c5fd;font-size:1.3rem"><i class="fas fa-newspaper"></i></div>
                        <?php endif; ?>
                        <div>
                            <div class="rc-title"><?= htmlspecialchars(mb_substr($r['title'], 0, 70)) ?><?= mb_strlen($r['title']) > 70 ? '…' : '' ?></div>
                            <div class="rc-date"><i class="far fa-calendar me-1"></i><?= date('d/m/Y', strtotime($r['published_at'] ?? $r['created_at'])) ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
