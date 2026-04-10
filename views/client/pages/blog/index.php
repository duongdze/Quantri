<?php
$pageTitle = $pageTitle ?? 'Tin Tức & Blog Du Lịch';
$metaDescription = 'Khám phá những bài viết du lịch hay nhất, kinh nghiệm du lịch, cẩm nang khám phá Việt Nam.';
require_once PATH_VIEW_CLIENT . 'default/header.php';
?>

<style>
.blog-hero {
    background: linear-gradient(135deg, #0a1628 0%, #1a2f5e 60%, #1e6fff 100%);
    padding: 80px 0 60px;
    position: relative;
    overflow: hidden;
}
.blog-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 30% 50%, rgba(30,111,255,.18) 0%, transparent 60%);
}
.blog-hero h1 { color: #fff; font-size: clamp(1.8rem, 4vw, 2.8rem); font-weight: 800; }
.blog-hero p { color: rgba(255,255,255,.75); font-size: 1.05rem; }

.blog-search-bar {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.25);
    border-radius: 50px;
    overflow: hidden;
    display: flex;
    max-width: 500px;
    backdrop-filter: blur(10px);
}
.blog-search-bar input {
    background: transparent; border: none; outline: none;
    color: #fff; padding: 12px 20px; flex: 1; font-size: .92rem;
}
.blog-search-bar input::placeholder { color: rgba(255,255,255,.55); }
.blog-search-bar button {
    background: #1e6fff; border: none; color: #fff;
    padding: 12px 22px; cursor: pointer; transition: background .2s;
}
.blog-search-bar button:hover { background: #1558e0; }

/* Featured Posts */
.featured-post-card {
    border-radius: 20px; overflow: hidden;
    background: #fff; box-shadow: 0 4px 24px rgba(0,0,0,.08);
    border: 1.5px solid #f1f5f9; transition: all .3s;
    text-decoration: none; color: inherit; display: block;
}
.featured-post-card:hover { transform: translateY(-6px); box-shadow: 0 16px 48px rgba(0,0,0,.12); }
.featured-post-card .card-img { width: 100%; height: 220px; object-fit: cover; }
.featured-post-card .card-img-placeholder {
    height: 220px; background: linear-gradient(135deg, #e0e7ff, #dbeafe);
    display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #93c5fd;
}
.featured-post-card .card-body { padding: 20px; }
.featured-badge {
    background: linear-gradient(135deg, #f59e0b, #f97316);
    color: #fff; font-size: .7rem; font-weight: 700;
    padding: 3px 10px; border-radius: 50px; display: inline-block; margin-bottom: 10px;
}
.post-title { font-size: 1.05rem; font-weight: 700; color: #111827; margin-bottom: 8px; line-height: 1.4; }
.post-excerpt { color: #6b7280; font-size: .88rem; line-height: 1.6; margin-bottom: 12px; }
.post-meta { display: flex; gap: 12px; color: #9ca3af; font-size: .78rem; }

/* Post List */
.post-list-card {
    background: #fff; border-radius: 16px;
    border: 1.5px solid #f1f5f9; overflow: hidden;
    display: flex; gap: 0; transition: all .3s;
    text-decoration: none; color: inherit;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
    margin-bottom: 20px;
}
.post-list-card:hover { border-color: #1e6fff33; box-shadow: 0 8px 32px rgba(30,111,255,.1); transform: translateX(4px); }
.post-list-card .post-thumb { width: 180px; height: 130px; flex-shrink: 0; object-fit: cover; }
.post-list-card .post-thumb-placeholder {
    width: 180px; height: 130px; flex-shrink: 0;
    background: linear-gradient(135deg, #e0e7ff, #dbeafe);
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; color: #93c5fd;
}
.post-list-card .post-body { padding: 16px 20px; flex: 1; }
.post-list-card .post-title { font-size: 1rem; font-weight: 700; margin-bottom: 6px; }
.post-list-card .post-excerpt { font-size: .84rem; color: #6b7280; }

@media (max-width: 576px) {
    .post-list-card { flex-direction: column; }
    .post-list-card .post-thumb,
    .post-list-card .post-thumb-placeholder { width: 100%; }
}
</style>

<!-- Hero -->
<section class="blog-hero">
    <div class="container" style="position:relative;z-index:2">
        <h1 class="mb-2"><i class="fas fa-newspaper me-2"></i>Tin Tức & Blog Du Lịch</h1>
        <p class="mb-4">Khám phá cẩm nang, kinh nghiệm và bài viết du lịch hấp dẫn nhất</p>
        <form action="<?= BASE_URL ?>" method="GET" class="blog-search-bar">
            <input type="hidden" name="action" value="blog">
            <input type="text" name="keyword" placeholder="Tìm bài viết…"
                   value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>
</section>

<div class="container py-5">

    <?php if (empty($_GET['keyword']) && !empty($featured)): ?>
    <!-- Featured posts -->
    <div class="mb-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 style="font-size:1.5rem;font-weight:800;color:#111827"><i class="fas fa-fire text-warning me-2"></i>Bài viết nổi bật</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($featured as $fp): ?>
            <div class="col-md-4">
                <a href="<?= BASE_URL ?>?action=blog/detail&slug=<?= $fp['slug'] ?>" class="featured-post-card">
                    <?php if (!empty($fp['thumbnail'])): ?>
                        <img src="<?= BASE_ASSETS_UPLOADS . $fp['thumbnail'] ?>" alt="<?= htmlspecialchars($fp['title']) ?>" class="card-img">
                    <?php else: ?>
                        <div class="card-img-placeholder"><i class="fas fa-newspaper"></i></div>
                    <?php endif; ?>
                    <div class="card-body">
                        <span class="featured-badge"><i class="fas fa-star me-1"></i>Nổi bật</span>
                        <div class="post-title"><?= htmlspecialchars($fp['title']) ?></div>
                        <?php if (!empty($fp['excerpt'])): ?>
                            <div class="post-excerpt"><?= htmlspecialchars(mb_substr($fp['excerpt'], 0, 90)) ?>…</div>
                        <?php endif; ?>
                        <div class="post-meta">
                            <span><i class="fas fa-user me-1"></i><?= htmlspecialchars($fp['author_name'] ?? 'Admin') ?></span>
                            <span><i class="far fa-calendar me-1"></i><?= date('d/m/Y', strtotime($fp['published_at'] ?? $fp['created_at'])) ?></span>
                            <span><i class="fas fa-eye me-1"></i><?= number_format($fp['views'] ?? 0) ?></span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <hr class="mb-5">
    <?php endif; ?>

    <!-- All posts -->
    <div class="row">
        <div class="col-lg-8">
            <h2 style="font-size:1.4rem;font-weight:800;color:#111827;margin-bottom:24px">
                <?php if (!empty($_GET['keyword'])): ?>
                    <i class="fas fa-search me-2"></i>Kết quả tìm kiếm: "<?= htmlspecialchars($_GET['keyword']) ?>"
                    <span class="badge bg-secondary ms-2" style="font-size:.8rem"><?= $pagination['total'] ?> bài</span>
                <?php else: ?>
                    <i class="fas fa-list me-2"></i>Tất cả bài viết
                <?php endif; ?>
            </h2>

            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                <a href="<?= BASE_URL ?>?action=blog/detail&slug=<?= $post['slug'] ?>" class="post-list-card">
                    <?php if (!empty($post['thumbnail'])): ?>
                        <img src="<?= BASE_ASSETS_UPLOADS . $post['thumbnail'] ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="post-thumb">
                    <?php else: ?>
                        <div class="post-thumb-placeholder"><i class="fas fa-newspaper"></i></div>
                    <?php endif; ?>
                    <div class="post-body">
                        <?php if (!empty($post['featured'])): ?>
                            <span class="featured-badge mb-2"><i class="fas fa-star me-1"></i>Nổi bật</span>
                        <?php endif; ?>
                        <div class="post-title"><?= htmlspecialchars($post['title']) ?></div>
                        <?php if (!empty($post['excerpt'])): ?>
                            <div class="post-excerpt"><?= htmlspecialchars(mb_substr($post['excerpt'], 0, 120)) ?>…</div>
                        <?php endif; ?>
                        <div class="post-meta mt-2">
                            <span><i class="fas fa-user me-1"></i><?= htmlspecialchars($post['author_name'] ?? 'Admin') ?></span>
                            <span><i class="far fa-calendar me-1"></i><?= date('d/m/Y', strtotime($post['published_at'] ?? $post['created_at'])) ?></span>
                            <span><i class="fas fa-eye me-1"></i><?= number_format($post['views'] ?? 0) ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>

                <!-- Pagination -->
                <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                <nav class="mt-4 d-flex justify-content-center">
                    <ul class="pagination">
                        <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                        <li class="page-item <?= $p == $pagination['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>?action=blog&page=<?= $p ?><?= !empty($_GET['keyword']) ? '&keyword=' . urlencode($_GET['keyword']) : '' ?>">
                                <?= $p ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-newspaper text-muted fa-3x mb-3 opacity-25"></i>
                    <h5 class="text-muted">Chưa có bài viết nào</h5>
                    <?php if (!empty($_GET['keyword'])): ?>
                        <a href="<?= BASE_URL ?>?action=blog" class="btn btn-outline-primary mt-2">Xem tất cả bài viết</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-compass text-primary me-2"></i>Khám phá tour</h5>
                    <p class="text-muted small">Tìm và đặt ngay những tour du lịch tuyệt vời nhất!</p>
                    <a href="<?= BASE_URL ?>?action=tour-list" class="btn btn-primary w-100 rounded-pill">
                        <i class="fas fa-map-marked-alt me-2"></i>Xem tất cả tour
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
