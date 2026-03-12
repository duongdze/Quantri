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
    <div class="container-fluid">
      <!-- Header -->
      <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
          <h1 class="h3 mb-0">
            <i class="fas fa-route text-primary"></i>
            <?= htmlspecialchars($tour['name']) ?>
          </h1>
          <p class="text-muted small">
            <?= htmlspecialchars($assignment['start_date'] ?? '') ?> - <?= htmlspecialchars($assignment['end_date'] ?? '') ?>
          </p>
        </div>
        <div class="d-flex gap-2">
          <?php if (!empty($allCustomers)): ?>
            <a href="<?= BASE_URL_ADMIN . '&action=bookings/print-group-list&id=' . ($bookings[0]['id'] ?? '') ?>"
              class="btn btn-outline-primary"
              target="_blank">
              <i class="fas fa-print"></i> In danh sách
            </a>
          <?php endif; ?>
          <a href="<?= BASE_URL_ADMIN . '&action=guide/schedule' ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
          </a>
        </div>
      </div>

      <!-- Stats Cards -->
      <?php if (!empty($allCustomers)): ?>
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
      <?php endif; ?>

      <!-- Tour Info Card -->
      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title mb-3"><i class="fas fa-info-circle"></i> Thông tin tour</h5>
          <div class="row">
            <div class="col-md-6">
              <p><strong>Danh mục:</strong> <?= htmlspecialchars($tour['category_name'] ?? 'N/A') ?></p>
              <p><strong>Nhà cung cấp:</strong> <?= htmlspecialchars($tour['supplier_name'] ?? 'N/A') ?></p>
            </div>
            <div class="col-md-6">
              <?php if (!empty($assignment)): ?>
                <div class="mb-3">
                  <label class="form-label"><strong>Trạng thái:</strong></label>
                  <div class="d-flex gap-2 align-items-center">
                    <select class="form-select form-select-sm" id="tour-status-select" style="max-width: 200px;">
                      <?php
                      $currentStatus = $assignment['status'] ?? 'pending';
                      $statuses = [
                        'pending' => 'Chưa bắt đầu',
                        'active' => 'Đang diễn ra',
                        'completed' => 'Hoàn thành'
                      ];
                      foreach ($statuses as $value => $label):
                      ?>
                        <option value="<?= $value ?>" <?= $currentStatus === $value ? 'selected' : '' ?>>
                          <?= $label ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-update-status"
                      data-assignment-id="<?= $assignment['id'] ?? '' ?>">
                      <i class="fas fa-save"></i> Cập nhật
                    </button>
                  </div>
                </div>
                <p><strong>Tài xế:</strong> <?= htmlspecialchars($assignment['driver_name'] ?? 'N/A') ?></p>
              <?php else: ?>
                <div class="alert alert-warning">
                  <i class="fas fa-exclamation-triangle"></i>
                  Chưa có phân công HDV cho tour này.
                </div>
              <?php endif; ?>
            </div>
          </div>
          <?php if (!empty($tour['description'])): ?>
            <hr>
            <p><strong>Mô tả:</strong></p>
            <p><?= nl2br(htmlspecialchars($tour['description'])) ?></p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Customer List with Check-in -->
      <?php if (!empty($allCustomers)): ?>
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0">
                <i class="fas fa-users"></i> Danh sách khách (<?= count($allCustomers) ?>)
              </h5>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-success btn-sm" id="btn-checkin-all">
                  <i class="fas fa-check-double"></i> Check-in đã chọn
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-select-all">
                  <i class="fas fa-check-square"></i> Chọn tất cả
                </button>
              </div>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th width="50">
                      <input type="checkbox" class="form-check-input" id="checkbox-all">
                    </th>
                    <th width="50">STT</th>
                    <th>Họ tên</th>
                    <th>Liên hệ</th>
                    <th>Booking</th>
                    <th>Loại khách</th>
                    <th>Ghi chú đặc biệt</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                    <th width="150">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($allCustomers as $index => $customer): ?>
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
                        <?php if (!empty($customer['is_main'])): ?>
                          <span class="badge bg-primary ms-1">Người đặt</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if (!empty($customer['phone'])): ?>
                          <small><i class="fas fa-phone me-1"></i><?= htmlspecialchars($customer['phone']) ?></small>
                        <?php else: ?>
                          <small class="text-muted">-</small>
                        <?php endif; ?>
                      </td>
                      <td><small>#<?= $customer['booking_code'] ?></small></td>
                      <td>
                        <span class="badge bg-secondary">
                          <?= $passengerTypeLabels[$customer['passenger_type']] ?? $customer['passenger_type'] ?>
                        </span>
                      </td>
                      <td>
                        <?php if (!empty($customer['special_request'])): ?>
                          <small class="text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <?= htmlspecialchars($customer['special_request']) ?>
                          </small>
                        <?php else: ?>
                          <small class="text-muted">-</small>
                        <?php endif; ?>
                      </td>
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
          </div>
        </div>
      <?php endif; ?>

      <!-- Itinerary -->
      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title mb-3"><i class="fas fa-map-marked-alt"></i> Lịch trình tour</h5>
          <?php if (empty($itineraries)): ?>
            <p class="text-muted">Chưa có lịch trình</p>
          <?php else: ?>
            <?php foreach ($itineraries as $day): ?>
              <div class="card mb-3">
                <div class="card-header bg-light">
                  <strong>Ngày <?= htmlspecialchars($day['day_number']) ?></strong> - <?= htmlspecialchars($day['day_label']) ?>
                </div>
                <div class="card-body">
                  <p><strong>Thời gian:</strong> <?= htmlspecialchars($day['time_start']) ?> - <?= htmlspecialchars($day['time_end']) ?></p>
                  <p><strong>Hoạt động:</strong></p>
                  <p><?= nl2br(htmlspecialchars($day['activities'])) ?></p>
                  <?php if (!empty($day['description'])): ?>
                    <p><strong>Mô tả:</strong></p>
                    <p><?= nl2br(htmlspecialchars($day['description'])) ?></p>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php if (!empty($allCustomers)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
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
        location.reload();
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

      // Handle tour status update
      const btnUpdateStatus = document.getElementById('btn-update-status');
      if (btnUpdateStatus) {
        btnUpdateStatus.addEventListener('click', function() {
          const assignmentId = this.dataset.assignmentId;
          const statusSelect = document.getElementById('tour-status-select');
          const newStatus = statusSelect.value;

          // Validate assignment ID
          if (!assignmentId || assignmentId === '') {
            showToast('error', 'Không tìm thấy thông tin phân công. Vui lòng thử lại.');
            return;
          }

          if (!confirm('Bạn có chắc muốn cập nhật trạng thái tour?')) {
            return;
          }

          // Disable button
          this.disabled = true;
          this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';

          fetch('<?= BASE_URL_ADMIN ?>&action=guide/updateStatus', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: `assignment_id=${assignmentId}&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                showToast('success', data.message);
                // Reload page after 1 second to show updated status
                setTimeout(() => location.reload(), 1000);
              } else {
                showToast('error', data.message);
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-save"></i> Cập nhật';
              }
            })
            .catch(error => {
              console.error('Error:', error);
              // Comment để xem error thật
              // showToast('error', 'Có lỗi xảy ra khi cập nhật trạng thái');
              showToast('error', 'Lỗi: ' + error.message);
              this.disabled = false;
              this.innerHTML = '<i class="fas fa-save"></i> Cập nhật';
            });
        });
      }
    });
  </script>
<?php endif; ?>

<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>