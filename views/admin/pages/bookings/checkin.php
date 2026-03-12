<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

// Prepare passenger type labels
$passengerTypeLabels = [
    'adult' => 'Người lớn',
    'child' => 'Trẻ em',
    'infant' => 'Em bé'
];

// Prepare status labels and colors
$statusLabels = [
    'not_arrived' => 'Chưa đến',
    'checked_in' => 'Đã đến',
    'absent' => 'Vắng mặt'
];

$statusColors = [
    'not_arrived' => 'warning',
    'checked_in' => 'success',
    'absent' => 'danger'
];
?>

<main class="wrapper">
    <div class="main-content">
        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2">
                    <i class="fas fa-clipboard-check text-primary"></i>
                    Check-in Khách
                </h1>
                <p class="text-muted mb-0">
                    Booking #<?= $booking['id'] ?> - <?= htmlspecialchars($tour['name'] ?? 'N/A') ?>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL_ADMIN . '&action=bookings/print-group-list&id=' . $booking['id'] ?>"
                    class="btn btn-outline-primary"
                    target="_blank">
                    <i class="fas fa-print"></i> In danh sách
                </a>
                <a href="<?= BASE_URL_ADMIN . '&action=bookings/detail&id=' . $booking['id'] ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Tổng khách</h6>
                                <h3 class="mb-0" id="stat-total"><?= $stats['total'] ?? 0 ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Đã đến</h6>
                                <h3 class="mb-0" id="stat-checked-in"><?= $stats['checked_in'] ?? 0 ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Chưa đến</h6>
                                <h3 class="mb-0" id="stat-not-arrived"><?= $stats['not_arrived'] ?? 0 ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Vắng mặt</h6>
                                <h3 class="mb-0" id="stat-absent"><?= $stats['absent'] ?? 0 ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Danh sách khách
                    </h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success btn-sm" id="btn-checkin-all">
                            <i class="fas fa-check-double"></i> Check-in tất cả
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-select-all">
                            <i class="fas fa-check-square"></i> Chọn tất cả
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($customers)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Chưa có khách nào trong booking này</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" class="form-check-input" id="checkbox-all">
                                    </th>
                                    <th width="50">STT</th>
                                    <th>Họ tên</th>
                                    <th>Loại khách</th>
                                    <th>Giới tính</th>
                                    <th>Trạng thái</th>
                                    <th>Thời gian</th>
                                    <th>HDV</th>
                                    <th width="150">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $index => $customer): ?>
                                    <tr data-customer-id="<?= $customer['id'] ?>">
                                        <td>
                                            <input type="checkbox" class="form-check-input customer-checkbox" value="<?= $customer['id'] ?>">
                                        </td>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($customer['full_name']) ?></strong>
                                            <?php if ($customer['is_foc']): ?>
                                                <span class="badge bg-info ms-1">FOC</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= $passengerTypeLabels[$customer['passenger_type']] ?? $customer['passenger_type'] ?>
                                            </span>
                                        </td>
                                        <td><?= $customer['gender'] === 'male' ? 'Nam' : ($customer['gender'] === 'female' ? 'Nữ' : 'Khác') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $statusColors[$customer['checkin_status'] ?? 'not_arrived'] ?> status-badge">
                                                <?= $statusLabels[$customer['checkin_status'] ?? 'not_arrived'] ?>
                                            </span>
                                        </td>
                                        <td class="checkin-time">
                                            <?php if (!empty($customer['checkin_time'])): ?>
                                                <small><?= date('H:i d/m/Y', strtotime($customer['checkin_time'])) ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="checked-by">
                                            <?php if (!empty($customer['checked_by_name'])): ?>
                                                <small><?= htmlspecialchars($customer['checked_by_name']) ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-success btn-checkin" data-customer-id="<?= $customer['id'] ?>" data-status="checked_in" title="Đã đến">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-checkin" data-customer-id="<?= $customer['id'] ?>" data-status="absent" title="Vắng mặt">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-checkin" data-customer-id="<?= $customer['id'] ?>" data-status="not_arrived" title="Reset">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bookingId = <?= $booking['id'] ?>;

        // Select all checkbox
        document.getElementById('checkbox-all')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.customer-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        // Select all button
        document.getElementById('btn-select-all')?.addEventListener('click', function() {
            const checkboxAll = document.getElementById('checkbox-all');
            if (checkboxAll) {
                checkboxAll.checked = !checkboxAll.checked;
                checkboxAll.dispatchEvent(new Event('change'));
            }
        });

        // Single check-in buttons
        document.querySelectorAll('.btn-checkin').forEach(btn => {
            btn.addEventListener('click', function() {
                const customerId = this.dataset.customerId;
                const status = this.dataset.status;
                updateCheckinStatus(customerId, status);
            });
        });

        // Bulk check-in
        document.getElementById('btn-checkin-all')?.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.customer-checkbox:checked'))
                .map(cb => cb.value);

            if (selectedIds.length === 0) {
                alert('Vui lòng chọn ít nhất một khách');
                return;
            }

            if (!confirm(`Xác nhận check-in ${selectedIds.length} khách?`)) {
                return;
            }

            bulkCheckin(selectedIds, 'checked_in');
        });

        // Update single customer check-in status
        function updateCheckinStatus(customerId, status) {
            const formData = new FormData();
            formData.append('customer_id', customerId);
            formData.append('status', status);

            fetch('<?= BASE_URL_ADMIN ?>&action=bookings/update-checkin', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update UI
                        updateCustomerRow(customerId, status, data.timestamp);
                        updateStats();
                        showToast('success', data.message);
                    } else {
                        showToast('error', data.message || 'Cập nhật thất bại');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Lỗi kết nối');
                });
        }

        // Bulk check-in
        function bulkCheckin(customerIds, status) {
            const formData = new FormData();
            customerIds.forEach(id => formData.append('customer_ids[]', id));
            formData.append('status', status);

            fetch('<?= BASE_URL_ADMIN ?>&action=bookings/bulk-checkin', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload page to update all
                        location.reload();
                    } else {
                        showToast('error', data.message || 'Cập nhật thất bại');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Lỗi kết nối');
                });
        }

        // Update customer row UI
        function updateCustomerRow(customerId, status, timestamp) {
            const row = document.querySelector(`tr[data-customer-id="${customerId}"]`);
            if (!row) return;

            const statusBadge = row.querySelector('.status-badge');
            const timeCell = row.querySelector('.checkin-time');

            const statusLabels = {
                'not_arrived': 'Chưa đến',
                'checked_in': 'Đã đến',
                'absent': 'Vắng mặt'
            };

            const statusColors = {
                'not_arrived': 'warning',
                'checked_in': 'success',
                'absent': 'danger'
            };

            if (statusBadge) {
                statusBadge.className = `badge bg-${statusColors[status]} status-badge`;
                statusBadge.textContent = statusLabels[status];
            }

            if (timeCell && timestamp) {
                timeCell.innerHTML = `<small>${timestamp}</small>`;
            }
        }

        // Update stats
        function updateStats() {
            // Simple reload stats from server
            fetch(`<?= BASE_URL_ADMIN ?>&action=bookings/checkin&id=${bookingId}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    ['total', 'checked-in', 'not-arrived', 'absent'].forEach(stat => {
                        const newValue = doc.getElementById(`stat-${stat}`)?.textContent;
                        const currentEl = document.getElementById(`stat-${stat}`);
                        if (currentEl && newValue) {
                            currentEl.textContent = newValue;
                        }
                    });
                })
                .catch(error => console.error('Error updating stats:', error));
        }

        // Toast notification
        function showToast(type, message) {
            const toastClass = type === 'success' ? 'bg-success' : 'bg-danger';
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white ${toastClass} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

            const container = document.querySelector('.toast-container') || createToastContainer();
            container.appendChild(toast);

            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();

            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        }

        function createToastContainer() {
            const container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1080';
            document.body.appendChild(container);
            return container;
        }
    });
</script>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>