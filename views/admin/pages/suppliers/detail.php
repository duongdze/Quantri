<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>

<main class="dashboard supplier-detail-page">
    <div class="dashboard-container">
        <!-- Modern Page Header -->
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
                        <a href="<?= BASE_URL_ADMIN ?>&action=suppliers" class="breadcrumb-link">
                            <i class="fas fa-handshake"></i>
                            <span>Nhà Cung Cấp</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current">Chi Tiết</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-building title-icon"></i>
                            <?= htmlspecialchars($supplier['name']) ?>
                        </h1>
                        <p class="page-subtitle">
                            Thông tin chi tiết nhà cung cấp - 
                            <?= !empty($supplier['type']) ? ucfirst($supplier['type']) : 'N/A' ?>
                        </p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN . '&action=suppliers/edit&id=' . $supplier['id'] ?>" 
                       class="btn btn-modern btn-secondary">
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa
                    </a>
                    <a href="<?= BASE_URL_ADMIN . '&action=suppliers' ?>" 
                       class="btn btn-modern btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Quay lại
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content Grid -->
        <div class="row">
            <!-- Main Column (Left) -->
            <div class="col-lg-8">
                <!-- Personal Information Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Thông tin nhà cung cấp
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Tên nhà cung cấp</label>
                                    <div class="info-value"><?= htmlspecialchars($supplier['name']) ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Loại dịch vụ</label>
                                    <div class="info-value">
                                        <?= !empty($supplier['type']) ? ucfirst($supplier['type']) : 'N/A' ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Người liên hệ</label>
                                    <div class="info-value"><?= htmlspecialchars($supplier['contact_person'] ?? 'N/A') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Số điện thoại</label>
                                    <div class="info-value"><?= htmlspecialchars($supplier['phone'] ?? 'N/A') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Email</label>
                                    <div class="info-value"><?= htmlspecialchars($supplier['email'] ?? 'N/A') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Đánh giá</label>
                                    <div class="info-value">
                                        <?php if (!empty($supplier['rating'])): ?>
                                            <?php
                                            $rating = $supplier['rating'];
                                            $fullStars = floor($rating);
                                            ?>
                                            <div class="rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $fullStars ? 'text-warning' : 'text-muted' ?>"></i>
                                                <?php endfor; ?>
                                                <span class="text-muted ms-1">(<?= number_format($rating, 1) ?>)</span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa đánh giá</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($supplier['address'])): ?>
                                <div class="col-12">
                                    <div class="info-item">
                                        <label class="info-label">Địa chỉ</label>
                                        <div class="info-value"><?= htmlspecialchars($supplier['address']) ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Description Card -->
                <?php if (!empty($supplier['description'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-alt text-warning me-2"></i>
                                Mô tả chi tiết
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?= nl2br(htmlspecialchars($supplier['description'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Contracts List Card -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-contract text-success me-2"></i>
                            Danh sách hợp đồng
                        </h5>
                        <span class="badge bg-primary"><?= count($contracts) ?> hợp đồng</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($contracts)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tên hợp đồng</th>
                                            <th>Thời gian</th>
                                            <th>Thông tin giá</th>
                                            <th>Trạng thái</th>
                                            <th>Ghi chú</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($contracts as $contract): 
                                            $today = date('Y-m-d');
                                            $status = 'active';
                                            $statusClass = 'success';
                                            $statusText = 'Đang hiệu lực';
                                            
                                            if ($contract['status'] === 'inactive') {
                                                $status = 'inactive';
                                                $statusClass = 'secondary';
                                                $statusText = 'Không hoạt động';
                                            } elseif ($today > $contract['end_date']) {
                                                $status = 'expired';
                                                $statusClass = 'warning';
                                                $statusText = 'Hết hạn';
                                            }
                                        ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($contract['contract_name']) ?></strong>
                                                </td>
                                                <td>
                                                    <div>
                                                        <small class="text-muted">Từ:</small> 
                                                        <?= date('d/m/Y', strtotime($contract['start_date'])) ?>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted">Đến:</small> 
                                                        <?= date('d/m/Y', strtotime($contract['end_date'])) ?>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($contract['price_info'] ?? '-') ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $statusClass ?>">
                                                        <?= $statusText ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($contract['notes'] ?? '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-file-contract fa-3x mb-3 text-muted"></i>
                                <h5 class="text-muted">Chưa có hợp đồng nào</h5>
                                <p class="text-muted">Nhà cung cấp này chưa có hợp đồng trong hệ thống</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar (Right) -->
            <div class="col-lg-4">
                <!-- Contact Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-address-card text-primary me-2"></i>
                            Thông tin liên hệ
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar-placeholder rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                                 style="width: 100px; height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="fas fa-building fa-3x text-white"></i>
                            </div>
                            <h5><?= htmlspecialchars($supplier['name']) ?></h5>
                            <p class="text-muted mb-2">
                                <?= !empty($supplier['type']) ? ucfirst($supplier['type']) : 'Nhà cung cấp' ?>
                            </p>
                            <?php if (!empty($supplier['rating'])): ?>
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= floor($supplier['rating']) ? 'text-warning' : 'text-muted' ?>"></i>
                                    <?php endfor; ?>
                                    <span class="text-muted ms-1">(<?= number_format($supplier['rating'], 1) ?>)</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <hr>
                        <div class="contact-item mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Email</small>
                                    <span><?= htmlspecialchars($supplier['email'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-phone text-success me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Điện thoại</small>
                                    <span><?= htmlspecialchars($supplier['phone'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt text-warning me-2"></i>
                            Thao tác nhanh
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?= BASE_URL_ADMIN ?>&action=suppliers/edit&id=<?= $supplier['id'] ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>
                                Chỉnh sửa thông tin
                            </a>
                            <a href="<?= BASE_URL_ADMIN ?>&action=suppliers" 
                               class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Quay lại danh sách
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.info-item {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

.info-label {
    display: block;
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 4px;
}

.info-value {
    font-size: 1rem;
    font-weight: 500;
    color: #212529;
}

.contact-item {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
}
</style>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>
