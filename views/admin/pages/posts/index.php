<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>
<main class="dashboard">
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-modern">
                        <a href="<?= BASE_URL_ADMIN ?>&action=/" class="breadcrumb-link"><i class="fas fa-home"></i><span>Dashboard</span></a>
                        <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
                        <span class="breadcrumb-current">Bài Viết / Blog</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title"><i class="fas fa-newspaper title-icon"></i> Quản lý Bài Viết</h1>
                        <p class="page-subtitle">Quản lý tin tức, blog du lịch hiển thị ngoài website</p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=posts/create" class="btn btn-modern btn-primary btn-lg">
                        <i class="fas fa-plus-circle me-2"></i>Thêm bài viết mới
                    </a>
                </div>
            </div>
        </header>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?php unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?php unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="d-flex flex-wrap gap-2">
                    <input type="hidden" name="action" value="posts">
                    <input type="text" class="form-control" style="max-width:300px" name="keyword"
                           placeholder="Tìm tiêu đề, mô tả…"
                           value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                    <select class="form-select" style="max-width:160px" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="draft"     <?= ($_GET['status'] ?? '') === 'draft'     ? 'selected' : '' ?>>Nháp</option>
                        <option value="published" <?= ($_GET['status'] ?? '') === 'published' ? 'selected' : '' ?>>Đã đăng</option>
                        <option value="archived"  <?= ($_GET['status'] ?? '') === 'archived'  ? 'selected' : '' ?>>Lưu trữ</option>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Tìm</button>
                    <a href="<?= BASE_URL_ADMIN ?>&action=posts" class="btn btn-outline-secondary">Reset</a>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>
                    Danh sách bài viết (<?= $pagination['total'] ?? 0 ?> bài)
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($posts)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Thumbnail</th>
                                    <th>Tiêu đề</th>
                                    <th>Tác giả</th>
                                    <th>Trạng thái</th>
                                    <th>Nổi bật</th>
                                    <th>Lượt xem</th>
                                    <th>Ngày tạo</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td style="width:80px">
                                            <?php if (!empty($post['thumbnail'])): ?>
                                                <img src="<?= BASE_ASSETS_UPLOADS . $post['thumbnail'] ?>" alt=""
                                                     class="rounded" style="width:70px;height:50px;object-fit:cover;">
                                            <?php else: ?>
                                                <div class="rounded bg-light d-flex align-items-center justify-content-center"
                                                     style="width:70px;height:50px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($post['title']) ?></strong>
                                            <br><small class="text-muted">/blog/<?= htmlspecialchars($post['slug']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($post['author_name'] ?? '—') ?></td>
                                        <td>
                                            <?php
                                            $badges = [
                                                'draft'     => ['warning', 'Nháp'],
                                                'published' => ['success', 'Đã đăng'],
                                                'archived'  => ['secondary', 'Lưu trữ'],
                                            ];
                                            [$color, $label] = $badges[$post['status']] ?? ['secondary', $post['status']];
                                            ?>
                                            <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                                        </td>
                                        <td>
                                            <?php if ($post['featured']): ?>
                                                <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>Nổi bật</span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= number_format($post['views'] ?? 0) ?></td>
                                        <td><small><?= date('d/m/Y', strtotime($post['created_at'])) ?></small></td>
                                        <td class="text-center">
                                            <a href="<?= BASE_URL_ADMIN ?>&action=posts/edit&id=<?= $post['id'] ?>"
                                               class="btn btn-sm btn-outline-primary me-1" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($post['status'] === 'published'): ?>
                                                <a href="<?= BASE_URL ?>?action=blog/detail&slug=<?= $post['slug'] ?>"
                                                   class="btn btn-sm btn-outline-info me-1" target="_blank" title="Xem trước">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= BASE_URL_ADMIN ?>&action=posts/delete&id=<?= $post['id'] ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Xóa bài viết này?')" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                        <div class="p-3 d-flex justify-content-center">
                            <nav>
                                <ul class="pagination mb-0">
                                    <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                                        <li class="page-item <?= $p == $pagination['page'] ? 'active' : '' ?>">
                                            <a class="page-link"
                                               href="<?= BASE_URL_ADMIN ?>&action=posts&page=<?= $p ?><?= !empty($_GET['keyword']) ? '&keyword=' . urlencode($_GET['keyword']) : '' ?><?= !empty($_GET['status']) ? '&status=' . $_GET['status'] : '' ?>">
                                                <?= $p ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-newspaper text-muted fa-3x mb-3"></i>
                        <h5 class="text-muted">Chưa có bài viết nào</h5>
                        <a href="<?= BASE_URL_ADMIN ?>&action=posts/create" class="btn btn-primary mt-2">
                            <i class="fas fa-plus me-2"></i>Tạo bài viết đầu tiên
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>
