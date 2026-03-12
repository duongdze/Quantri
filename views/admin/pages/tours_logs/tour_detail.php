<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>
<main class="dashboard tour-logs-detail-page">
    <div class="dashboard-container">
        <!-- Page Header -->
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
                        <a href="<?= BASE_URL_ADMIN ?>&action=tours_logs" class="breadcrumb-link">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Nhật ký Tour</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current"><?= htmlspecialchars($tour['name'] ?? '') ?></span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-history title-icon"></i>
                            Lịch sử Nhật ký
                        </h1>
                        <p class="page-subtitle">Danh sách nhật ký hoạt động của tour: <strong><?= htmlspecialchars($tour['name'] ?? '') ?></strong></p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN . '&action=tours_logs/create&tour_id=' . ($tour['id'] ?? '') ?>" class="btn btn-modern btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>
                        Thêm nhật ký ngày
                    </a>
                </div>
            </div>
        </header>

        <!-- Special Requests Alert Panel -->
        <?php if (!empty($specialRequests)): ?>
            <div class="card border-warning shadow-sm mb-4">
                <div class="card-header bg-warning bg-opacity-10 border-warning">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 text-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Yêu cầu đặc biệt của khách (<?= count($specialRequests) ?> yêu cầu)
                        </h5>
                        <span class="badge bg-warning"><?= count($specialRequests) ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4">Khách hàng</th>
                                    <th>Ngày khởi hành</th>
                                    <th>Loại khách</th>
                                    <th>Yêu cầu đặc biệt</th>
                                    <th class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($specialRequests as $customer): ?>
                                    <tr id="request-row-<?= $customer['id'] ?>" class="<?= $customer['request_handled'] ? 'table-success' : '' ?>">
                                        <td class="px-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary bg-opacity-10 text-primary me-3">
                                                    <?= strtoupper(substr($customer['full_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($customer['full_name']) ?></div>
                                                    <?php if (!empty($customer['phone'])): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-phone me-1"></i><?= htmlspecialchars($customer['phone']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= date('d/m/Y', strtotime($customer['departure_date'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $typeLabels = [
                                                'adult' => ['label' => 'Người lớn', 'color' => 'primary'],
                                                'child' => ['label' => 'Trẻ em', 'color' => 'success'],
                                                'infant' => ['label' => 'Em bé', 'color' => 'info']
                                            ];
                                            $type = $typeLabels[$customer['passenger_type']] ?? ['label' => 'N/A', 'color' => 'secondary'];
                                            ?>
                                            <span class="badge bg-<?= $type['color'] ?> bg-opacity-10 text-<?= $type['color'] ?> border border-<?= $type['color'] ?>">
                                                <?= $type['label'] ?>
                                            </span>
                                            <?php if ($customer['is_foc']): ?>
                                                <span class="badge bg-warning ms-1">FOC</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="special-request-content">
                                                <i class="fas fa-info-circle text-warning me-2"></i>
                                                <span class="text-dark"><?= nl2br(htmlspecialchars($customer['special_request'])) ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block">
                                                <input
                                                    class="form-check-input request-handled-checkbox"
                                                    type="checkbox"
                                                    id="handled-<?= $customer['id'] ?>"
                                                    data-customer-id="<?= $customer['id'] ?>"
                                                    <?= $customer['request_handled'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="handled-<?= $customer['id'] ?>">
                                                    <span class="status-text-<?= $customer['id'] ?>">
                                                        <?= $customer['request_handled'] ? 'Đã xử lý' : 'Chưa xử lý' ?>
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light text-muted">
                    <small>
                        <i class="fas fa-lightbulb me-1"></i>
                        <strong>Lưu ý:</strong> Hướng dẫn viên cần chú ý các yêu cầu đặc biệt này trong suốt hành trình.
                        Ghi chú quá trình xử lý vào phần "Ghi chú xử lý" khi tạo nhật ký hàng ngày.
                    </small>
                </div>
            </div>
        <?php endif; ?>

        <!-- Logs List Card -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Ngày</th>
                                <th>Hướng dẫn viên</th>
                                <th>Tóm tắt hoạt động</th>
                                <th>Thời tiết</th>
                                <th>Đánh giá</th>
                                <th class="text-center pe-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($logs)): ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td class="ps-4 fw-medium text-nowrap">
                                            <?= date('d/m/Y', strtotime($log['date'])) ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2 bg-light rounded-circle d-flex align-items-center justify-content-center text-primary">
                                                    <i class="fas fa-user-tie"></i>
                                                </div>
                                                <span><?= htmlspecialchars($log['guide_name'] ?? 'N/A') ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 300px;">
                                                <?= htmlspecialchars($log['description'] ?? '') ?>
                                            </div>
                                            <?php if (!empty($log['issue'])): ?>
                                                <small class="text-danger">
                                                    <i class="fas fa-exclamation-circle me-1"></i>Có vấn đề phát sinh
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($log['weather'])): ?>
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                                    <i class="fas fa-cloud-sun me-1"></i>
                                                    <?= htmlspecialchars($log['weather']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($log['guide_rating']) && $log['guide_rating'] > 0): ?>
                                                <div class="d-flex align-items-center text-warning">
                                                    <span class="fw-bold me-1"><?= $log['guide_rating'] ?></span>
                                                    <i class="fas fa-star small"></i>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small">Chưa đánh giá</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center pe-4">
                                            <div class="btn-group">
                                                <a href="<?= BASE_URL_ADMIN . '&action=tours_logs/detail&id=' . urlencode($log['id']) ?>"
                                                    class="btn btn-sm btn-outline-info"
                                                    data-bs-toggle="tooltip"
                                                    title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= BASE_URL_ADMIN . '&action=tours_logs/edit&id=' . urlencode($log['id']) ?>"
                                                    class="btn btn-sm btn-outline-warning"
                                                    data-bs-toggle="tooltip"
                                                    title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="post" action="<?= BASE_URL_ADMIN . '&action=tours_logs/delete' ?>"
                                                    onsubmit="return confirm('Bạn có chắc muốn xóa nhật ký này?')"
                                                    class="d-inline">
                                                    <input type="hidden" name="id" value="<?= htmlspecialchars($log['id']) ?>">
                                                    <input type="hidden" name="tour_id" value="<?= htmlspecialchars($tour['id'] ?? '') ?>">
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="tooltip"
                                                        title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Chưa có nhật ký nào cho tour này</h5>
                                            <p class="text-muted mb-3">Bắt đầu ghi nhận hoạt động của tour ngay hôm nay.</p>
                                            <a href="<?= BASE_URL_ADMIN . '&action=tours_logs/create&tour_id=' . ($tour['id'] ?? '') ?>" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>Thêm nhật ký đầu tiên
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle special request checkbox changes
    document.querySelectorAll('.request-handled-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const customerId = this.dataset.customerId;
            const handled = this.checked ? 1 : 0;
            const row = document.getElementById('request-row-' + customerId);
            const statusText = document.querySelector('.status-text-' + customerId);

            fetch('<?= BASE_URL_ADMIN ?>&action=tours_logs/mark_request_handled', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'customer_id=' + customerId + '&handled=' + handled
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (handled) {
                            row.classList.add('table-success');
                            statusText.textContent = 'Đã xử lý';
                        } else {
                            row.classList.remove('table-success');
                            statusText.textContent = 'Chưa xử lý';
                        }
                        alert(data.message);
                    } else {
                        this.checked = !this.checked;
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.checked = !this.checked;
                    alert('Có lỗi xảy ra');
                });
        });
    });
</script>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>