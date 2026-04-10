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
                        <span class="breadcrumb-current">Quản lý Đánh Giá</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title"><i class="fas fa-star title-icon text-warning"></i> Quản lý Đánh Giá</h1>
                        <p class="page-subtitle">Duyệt, ẩn hoặc xóa đánh giá từ khách hàng</p>
                    </div>
                </div>
            </div>
        </header>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?php unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="d-flex flex-wrap gap-2">
                    <input type="hidden" name="action" value="reviews">
                    <select class="form-select" style="max-width:180px" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending"  <?= ($_GET['status'] ?? '') === 'pending'  ? 'selected' : '' ?>>⏳ Chờ duyệt</option>
                        <option value="approved" <?= ($_GET['status'] ?? '') === 'approved' ? 'selected' : '' ?>>✅ Đã duyệt</option>
                        <option value="rejected" <?= ($_GET['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>❌ Đã từ chối</option>
                    </select>
                    <select class="form-select" style="max-width:220px" name="tour_id">
                        <option value="">Tất cả tour</option>
                        <?php foreach ($tours ?? [] as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= ($_GET['tour_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select class="form-select" style="max-width:130px" name="rating">
                        <option value="">Tất cả sao</option>
                        <?php for ($s = 5; $s >= 1; $s--): ?>
                            <option value="<?= $s ?>" <?= ($_GET['rating'] ?? '') == $s ? 'selected' : '' ?>><?= $s ?> sao</option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Lọc</button>
                    <a href="<?= BASE_URL_ADMIN ?>&action=reviews" class="btn btn-outline-secondary">Reset</a>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách đánh giá (<?= $pagination['total'] ?? 0 ?>)</h5></div>
            <div class="card-body p-0">
                <?php if (!empty($reviews)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Khách hàng</th>
                                    <th>Tour</th>
                                    <th>Sao</th>
                                    <th>Nội dung</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reviews as $review): ?>
                                    <tr id="review-row-<?= $review['id'] ?>">
                                        <td>
                                            <strong><?= htmlspecialchars($review['full_name'] ?? 'N/A') ?></strong>
                                            <br><small class="text-muted">ID: #<?= $review['user_id'] ?></small>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars($review['tour_name'] ?? '—') ?></small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-0">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>" style="font-size:0.85rem"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <small class="text-muted"><?= $review['rating'] ?>/5</small>
                                        </td>
                                        <td style="max-width:250px">
                                            <small><?= nl2br(htmlspecialchars(mb_substr($review['comment'] ?? '', 0, 120))) ?>
                                            <?= mb_strlen($review['comment'] ?? '') > 120 ? '…' : '' ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $sBadge = [
                                                'pending'  => ['warning', 'Chờ duyệt'],
                                                'approved' => ['success', 'Đã duyệt'],
                                                'rejected' => ['danger',  'Từ chối'],
                                            ];
                                            [$sc, $sl] = $sBadge[$review['status']] ?? ['secondary', $review['status']];
                                            ?>
                                            <span class="badge bg-<?= $sc ?> review-status-badge" id="status-<?= $review['id'] ?>">
                                                <?= $sl ?>
                                            </span>
                                        </td>
                                        <td><small><?= date('d/m/Y', strtotime($review['created_at'])) ?></small></td>
                                        <td class="text-center">
                                            <?php if ($review['status'] !== 'approved'): ?>
                                                <button class="btn btn-sm btn-success me-1 btn-approve"
                                                        data-id="<?= $review['id'] ?>" title="Duyệt">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($review['status'] !== 'rejected'): ?>
                                                <button class="btn btn-sm btn-warning me-1 btn-reject"
                                                        data-id="<?= $review['id'] ?>" title="Từ chối">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                            <a href="<?= BASE_URL_ADMIN ?>&action=reviews/delete&id=<?= $review['id'] ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Xóa đánh giá này?')" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                        <div class="p-3 d-flex justify-content-center">
                            <nav><ul class="pagination mb-0">
                                <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                                    <li class="page-item <?= $p == $pagination['page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= BASE_URL_ADMIN ?>&action=reviews&page=<?= $p ?>"><?= $p ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul></nav>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-star text-muted fa-3x mb-3"></i>
                        <h5 class="text-muted">Không có đánh giá nào</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
const adminUrl = '<?= BASE_URL_ADMIN ?>';

function updateReviewStatus(id, status) {
    const data = new FormData();
    data.append('id', id);
    data.append('status', status);
    fetch(adminUrl + '&action=reviews/update-status', { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const badges = { pending: ['warning','Chờ duyệt'], approved: ['success','Đã duyệt'], rejected: ['danger','Từ chối'] };
                const badge = document.getElementById('status-' + id);
                if (badge) {
                    badge.className = 'badge bg-' + badges[status][0] + ' review-status-badge';
                    badge.textContent = badges[status][1];
                }
                // Ẩn nút vừa dùng
                const row = document.getElementById('review-row-' + id);
                if (status === 'approved') row?.querySelector('.btn-approve')?.remove();
                if (status === 'rejected') row?.querySelector('.btn-reject')?.remove();
            } else {
                alert('Có lỗi xảy ra, vui lòng thử lại.');
            }
        });
}

document.querySelectorAll('.btn-approve').forEach(btn => {
    btn.addEventListener('click', () => updateReviewStatus(btn.dataset.id, 'approved'));
});
document.querySelectorAll('.btn-reject').forEach(btn => {
    btn.addEventListener('click', () => updateReviewStatus(btn.dataset.id, 'rejected'));
});
</script>

<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>
