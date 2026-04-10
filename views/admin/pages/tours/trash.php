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
                        <a href="<?= BASE_URL_ADMIN ?>&action=tours" class="breadcrumb-link"><span>Quản lý Tour</span></a>
                        <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
                        <span class="breadcrumb-current">Thùng Rác</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title"><i class="fas fa-trash-alt title-icon text-danger"></i> Thùng Rác Tour</h1>
                        <p class="page-subtitle">Danh sách tour đã xóa – có thể khôi phục hoặc xóa vĩnh viễn</p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=tours" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách Tour
                    </a>
                </div>
            </div>
        </header>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="d-flex gap-2">
                    <input type="hidden" name="action" value="tours/trash">
                    <input type="text" class="form-control" name="keyword" placeholder="Tìm theo tên tour…"
                           value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    <a href="<?= BASE_URL_ADMIN ?>&action=tours/trash" class="btn btn-outline-secondary">Reset</a>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="fas fa-trash-alt me-2 text-danger"></i>
                    Thùng Rác (<?= $trashCount ?? 0 ?> tour)
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($tours)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ảnh</th>
                                    <th>Tên tour</th>
                                    <th>Danh mục</th>
                                    <th>Giá</th>
                                    <th>Đã xóa lúc</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tours as $tour): ?>
                                    <tr>
                                        <td style="width:70px">
                                            <?php $imgUrl = !empty($tour['main_image'])
                                                ? BASE_ASSETS_UPLOADS . $tour['main_image']
                                                : BASE_URL . 'assets/admin/image/no-image.png'; ?>
                                            <img src="<?= $imgUrl ?>" alt="" class="rounded" style="width:60px;height:50px;object-fit:cover;">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($tour['name']) ?></strong>
                                            <br><small class="text-muted">ID: #<?= $tour['id'] ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($tour['category_name'] ?? '—') ?></td>
                                        <td><?= number_format($tour['base_price'] ?? 0, 0, ',', '.') ?> VNĐ</td>
                                        <td>
                                            <small class="text-danger">
                                                <i class="fas fa-clock me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($tour['deleted_at'])) ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <!-- Khôi phục -->
                                            <a href="<?= BASE_URL_ADMIN ?>&action=tours/restore&id=<?= $tour['id'] ?>"
                                               class="btn btn-sm btn-success me-1"
                                               onclick="return confirm('Khôi phục tour này?')"
                                               title="Khôi phục">
                                                <i class="fas fa-undo"></i> Khôi phục
                                            </a>
                                            <!-- Xóa vĩnh viễn -->
                                            <a href="<?= BASE_URL_ADMIN ?>&action=tours/force-delete&id=<?= $tour['id'] ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('⚠️ Xóa VĨNH VIỄN tour này và toàn bộ dữ liệu liên quan? Không thể hoàn tác!')"
                                               title="Xóa vĩnh viễn">
                                                <i class="fas fa-times"></i> Xóa vĩnh viễn
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
                                            <a class="page-link" href="<?= BASE_URL_ADMIN ?>&action=tours/trash&page=<?= $p ?><?= !empty($_GET['keyword']) ? '&keyword=' . urlencode($_GET['keyword']) : '' ?>">
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
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h5 class="text-muted">Thùng rác trống!</h5>
                        <p class="text-muted">Không có tour nào bị xóa.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>
