<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>
<main class="wrapper">
    <div class="main-content">
        <div class="page-header mb-4">
            <h1 class="h3">Lịch làm việc của tất cả HDV</h1>
            <p class="text-muted">Danh sách tour được phân công cho từng hướng dẫn viên</p>
        </div>

        <?php foreach ($guideAssignments as $group): ?>
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <strong><?= htmlspecialchars($group['guide']['full_name'] ?? 'N/A') ?></strong>
                    — <?= htmlspecialchars($group['guide']['email'] ?? 'N/A') ?> | <?= htmlspecialchars($group['guide']['phone'] ?? 'N/A') ?>
                </div>
                <div class="card-body">
                    <?php if (empty($group['assignments'])): ?>
                        <p class="text-muted">Chưa có tour nào được phân công.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tour</th>
                                        <th>Thời gian</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($group['assignments'] as $a): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($a['tour_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($a['start_date'] ?? '') ?> - <?= htmlspecialchars($a['end_date'] ?? '') ?></td>
                                            <td>
                                                <?php
                                                $status = $a['status'] ?? 'pending';
                                                $statusConfig = [
                                                    'pending' => ['class' => 'warning', 'label' => 'Chưa bắt đầu'],
                                                    'active' => ['class' => 'success', 'label' => 'Đang diễn ra'],
                                                    'completed' => ['class' => 'secondary', 'label' => 'Hoàn thành']
                                                ];
                                                $config = $statusConfig[$status] ?? ['class' => 'secondary', 'label' => $status];
                                                ?>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-<?= $config['class'] ?>">
                                                        <?= $config['label'] ?>
                                                    </span>
                                                    <!-- <select class="form-select form-select-sm status-select"
                                                        style="max-width: 150px;"
                                                        data-assignment-id="<?= $a['id'] ?>">
                                                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Chưa bắt đầu</option>
                                                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Đang diễn ra</option>
                                                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                                                    </select> -->
                                                </div>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL_ADMIN ?>&action=guide/tourDetail&id=<?= $a['tour_id'] ?>&guide_id=<?= $group['guide']['id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Chi tiết
                                                </a>
                                                <a href="<?= BASE_URL_ADMIN ?>&action=tour_vehicles&assignment_id=<?= $a['id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-bus"></i> Xe
                                                </a>
                                                <button class="btn btn-sm btn-danger remove-assignment-btn"
                                                    data-assignment-id="<?= $a['id'] ?>"
                                                    data-tour-name="<?= htmlspecialchars($a['tour_name'] ?? '') ?>"
                                                    data-guide-name="<?= htmlspecialchars($group['guide']['full_name'] ?? '') ?>">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle status change
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                const assignmentId = this.dataset.assignmentId;
                const newStatus = this.value;
                const originalValue = this.querySelector('option[selected]')?.value || 'pending';

                if (!confirm('Bạn có chắc muốn cập nhật trạng thái?')) {
                    this.value = originalValue;
                    return;
                }

                // Disable select
                this.disabled = true;

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
                            alert('✅ ' + data.message);
                            location.reload();
                        } else {
                            alert('❌ ' + data.message);
                            this.value = originalValue;
                            this.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi cập nhật trạng thái');
                        this.value = originalValue;
                        this.disabled = false;
                    });
            });
        });

        // Handle remove assignment buttons
        document.querySelectorAll('.remove-assignment-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const assignmentId = this.dataset.assignmentId;
                const tourName = this.dataset.tourName;
                const guideName = this.dataset.guideName;

                if (confirm(`Bạn có chắc muốn xóa phân công tour "${tourName}" của HDV "${guideName}"?`)) {
                    // Disable button và hiển thị loading
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xóa...';

                    // Send AJAX request
                    fetch('<?= BASE_URL_ADMIN ?>&action=guides/remove-assignment', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `assignment_id=${assignmentId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('✅ ' + data.message);
                                window.location.reload();
                            } else {
                                alert('❌ ' + data.message);
                                // Re-enable button
                                this.disabled = false;
                                this.innerHTML = '<i class="fas fa-trash"></i> Xóa';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Có lỗi xảy ra! Vui lòng thử lại.');
                            // Re-enable button
                            this.disabled = false;
                            this.innerHTML = '<i class="fas fa-trash"></i> Xóa';
                        });
                }
            });
        });
    });
</script>

<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>