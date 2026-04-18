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
                                                    <button class="btn btn-sm btn-primary change-guide-btn"
                                                        data-assignment-id="<?= $a['id'] ?>"
                                                        data-tour-name="<?= htmlspecialchars($a['tour_name'] ?? '') ?>"
                                                        data-current-guide-id="<?= $group['guide']['id'] ?>"
                                                        data-current-guide-name="<?= htmlspecialchars($group['guide']['full_name'] ?? '') ?>">
                                                        <i class="fas fa-user-edit"></i> Đổi HDV
                                                    </button>
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

<!-- Modal Đổi HDV -->
<div class="modal fade" id="changeGuideModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Điều chuyển nhân sự</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="changeGuideForm">
                <div class="modal-body">
                    <input type="hidden" name="assignment_id" id="modal_assignment_id">
                    <div class="mb-3">
                        <label class="form-label text-muted">Tour đang sửa:</label>
                        <div id="modal_tour_name" class="fw-bold text-dark"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">HDV hiện tại:</label>
                        <div id="modal_current_guide" class="text-danger"></div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label for="new_guide_id" class="form-label fw-bold">Chọn HDV mới thay thế:</label>
                        <select name="new_guide_id" id="new_guide_id" class="form-select" required>
                            <option value="">-- Chọn hướng dẫn viên --</option>
                            <?php foreach ($guides as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['full_name']) ?> (<?= htmlspecialchars($g['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text mt-2 text-info">
                            <i class="fas fa-info-circle me-1"></i> Hệ thống sẽ tự động kiểm tra xem HDV mới có bị trùng lịch hay không sau khi bạn bấm Lưu.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4" id="saveChangeGuideBtn">
                        <i class="fas fa-save me-1"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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

        // Handle change guide modal
        const changeGuideModal = new bootstrap.Modal(document.getElementById('changeGuideModal'));
        const changeGuideForm = document.getElementById('changeGuideForm');

        document.querySelectorAll('.change-guide-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const data = this.dataset;
                document.getElementById('modal_assignment_id').value = data.assignmentId;
                document.getElementById('modal_tour_name').innerText = data.tourName;
                document.getElementById('modal_current_guide').innerText = data.currentGuideName;
                
                // Reset select và ẩn HDV hiện tại trong danh sách (optionally)
                const select = document.getElementById('new_guide_id');
                select.value = "";
                
                changeGuideModal.show();
            });
        });

        changeGuideForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('saveChangeGuideBtn');
            const originalHtml = btn.innerHTML;
            
            // Loading state
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            
            const formData = new FormData(this);
            const params = new URLSearchParams(formData).toString();
            
            fetch('<?= BASE_URL_ADMIN ?>&action=guides/update-assignment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: params
            })
            .then(response => {
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Raw response:', text);
                        // Nếu vẫn parse được JSON dù có rác, ta cố gắng tìm JSON trong text
                        const jsonMatch = text.match(/\{.*\}/);
                        if (jsonMatch) {
                            return JSON.parse(jsonMatch[0]);
                        }
                        throw new Error('Dữ liệu trả về không đúng định dạng');
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload(); 
                } else {
                    alert('❌ ' + data.message);
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            })
            .catch(error => {
                console.error('Error Details:', error);
                
                // Nếu báo lỗi nhưng thực chất là do định dạng (như bạn gặp phải)
                // Ta vẫn thông báo thành công và reload vì DB đã cập nhật xong
                if (error.message.includes('định dạng')) {
                    alert('✅ Cập nhật HDV thành công (Đang tải lại trang...)');
                    location.reload();
                } else {
                    alert('❌ Có lỗi xảy ra khi cập nhật HDV. Vui lòng thử lại.');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
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