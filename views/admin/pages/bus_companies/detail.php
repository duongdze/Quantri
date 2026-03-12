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
                        <a href="<?= BASE_URL_ADMIN ?>&action=/" class="breadcrumb-link">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                        <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
                        <a href="<?= BASE_URL_ADMIN ?>&action=bus-companies" class="breadcrumb-link">
                            <i class="fas fa-bus"></i>
                            <span>Quản lý Nhà Xe</span>
                        </a>
                        <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
                        <span class="breadcrumb-current">Chi tiết Nhà Xe</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-info-circle title-icon"></i>
                            <?= htmlspecialchars($busCompany['company_name']) ?>
                        </h1>
                        <p class="page-subtitle">Mã: <?= htmlspecialchars($busCompany['company_code']) ?></p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=bus-companies" class="btn btn-modern btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Quay lại
                    </a>
                    <a href="<?= BASE_URL_ADMIN ?>&action=bus-companies/edit&id=<?= $busCompany['id'] ?>" class="btn btn-modern btn-primary">
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa
                    </a>
                </div>
            </div>
        </header>

        <div class="row">
            <!-- Thông tin chính -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-building text-primary me-2"></i>
                            Thông tin nhà xe
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Mã nhà xe</label>
                                <p class="mb-0"><strong><?= htmlspecialchars($busCompany['company_code']) ?></strong></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Tên nhà xe</label>
                                <p class="mb-0"><strong><?= htmlspecialchars($busCompany['company_name']) ?></strong></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Người liên hệ</label>
                                <p class="mb-0"><?= htmlspecialchars($busCompany['contact_person'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Số điện thoại</label>
                                <p class="mb-0">
                                    <?php if (!empty($busCompany['phone'])): ?>
                                        <i class="fas fa-phone text-success me-2"></i>
                                        <?= htmlspecialchars($busCompany['phone']) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Email</label>
                                <p class="mb-0">
                                    <?php if (!empty($busCompany['email'])): ?>
                                        <i class="fas fa-envelope text-info me-2"></i>
                                        <?= htmlspecialchars($busCompany['email']) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Giấy phép kinh doanh</label>
                                <p class="mb-0"><?= htmlspecialchars($busCompany['business_license'] ?? '-') ?></p>
                            </div>
                            <div class="col-12">
                                <label class="text-muted small">Địa chỉ trụ sở</label>
                                <p class="mb-0">
                                    <?php if (!empty($busCompany['address'])): ?>
                                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                        <?= htmlspecialchars($busCompany['address']) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-truck text-success me-2"></i>
                            Thông tin phương tiện
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="text-muted small">Tổng số xe</label>
                                <p class="mb-0">
                                    <span class="badge bg-info fs-6"><?= number_format($busCompany['total_vehicles'] ?? 0) ?> xe</span>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Hãng xe chủ yếu</label>
                                <p class="mb-0"><?= htmlspecialchars($busCompany['vehicle_brand'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Đánh giá</label>
                                <p class="mb-0">
                                <div class="rating-display">
                                    <div class="stars">
                                        <?php
                                        $rating = $busCompany['rating'] ?? 5;
                                        $fullStars = floor($rating);
                                        for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= $fullStars ? 'filled' : 'empty' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-value"><?= number_format($rating, 1) ?></span>
                                </div>
                                </p>
                            </div>
                            <?php if (!empty($busCompany['notes'])): ?>
                                <div class="col-12">
                                    <label class="text-muted small">Ghi chú</label>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($busCompany['notes'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Trạng thái</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $statusClass = $busCompany['status'] == 'active' ? 'success' : 'secondary';
                        $statusText = $busCompany['status'] == 'active' ? 'Đang hoạt động' : 'Ngừng hoạt động';
                        $statusIcon = $busCompany['status'] == 'active' ? 'check-circle' : 'pause-circle';
                        ?>
                        <span class="badge badge-modern badge-<?= $statusClass ?> fs-6">
                            <i class="fas fa-<?= $statusIcon ?> me-1"></i>
                            <?= $statusText ?>
                        </span>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Thông tin hệ thống</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Ngày tạo</label>
                            <p class="mb-0">
                                <i class="fas fa-calendar text-primary me-2"></i>
                                <?= date('d/m/Y H:i', strtotime($busCompany['created_at'])) ?>
                            </p>
                        </div>
                        <div>
                            <label class="text-muted small">Cập nhật lần cuối</label>
                            <p class="mb-0">
                                <i class="fas fa-clock text-warning me-2"></i>
                                <?= date('d/m/Y H:i', strtotime($busCompany['updated_at'])) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Thao tác</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?= BASE_URL_ADMIN ?>&action=bus-companies/edit&id=<?= $busCompany['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>
                                Chỉnh sửa
                            </a>
                            <button type="button" class="btn btn-danger" onclick="deleteBusCompany()">
                                <i class="fas fa-trash me-2"></i>
                                Xóa nhà xe
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<form id="deleteForm" method="POST" action="<?= BASE_URL_ADMIN ?>&action=bus-companies/delete" style="display: none;">
    <input type="hidden" name="id" value="<?= $busCompany['id'] ?>">
</form>

<script>
    function deleteBusCompany() {
        if (confirm('Bạn có chắc muốn xóa nhà xe "<?= htmlspecialchars($busCompany['company_name']) ?>"?\nLưu ý: Các booking và tour assignment liên quan sẽ bị ảnh hưởng.')) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>

<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>