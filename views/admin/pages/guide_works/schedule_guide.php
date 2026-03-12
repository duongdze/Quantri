<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>
<main class="wrapper">
    <div class="main-content">
        <div class="page-header mb-4">
            <h1 class="h3">Lịch làm việc của tôi</h1>
            <p class="text-muted">Các tour bạn được phân công</p>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if (empty($assignments)): ?>
                    <p class="text-muted">Bạn chưa có tour nào được phân công.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Tour</th>
                                    <th>Thời gian</th>
                                    <th>Trạng thái</th>
                                    <th width="250">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignments as $a): ?>
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
                                            <span class="badge bg-<?= $config['class'] ?>">
                                                <?= $config['label'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= BASE_URL_ADMIN ?>&action=guide/tourDetail&id=<?= $a['tour_id'] ?>&guide_id=<?= $a['guide_id'] ?>" class="btn btn-info">
                                                    <i class="fas fa-eye"></i> Chi tiết
                                                </a>
                                                <?php
                                                // Kiểm tra xem còn >= 3 ngày trước ngày bắt đầu không
                                                $canCancel = false;
                                                $daysUntilStart = 0;

                                                if (!empty($a['start_date'])) {
                                                    try {
                                                        $startDate = new DateTime($a['start_date']);
                                                        $today = new DateTime();
                                                        $daysUntilStart = $today->diff($startDate)->days;
                                                        $canCancel = $daysUntilStart >= 3 && $today < $startDate;
                                                    } catch (Exception $e) {
                                                        // Invalid date, cannot cancel
                                                        $canCancel = false;
                                                    }
                                                }
                                                ?>
                                                <?php if ($canCancel): ?>
                                                    <button type="button" class="btn btn-danger btn-cancel-tour"
                                                        data-assignment-id="<?= $a['id'] ?>"
                                                        data-tour-name="<?= htmlspecialchars($a['tour_name']) ?>"
                                                        data-days-left="<?= $daysUntilStart ?>">
                                                        <i class="fas fa-times"></i> Hủy nhận (còn <?= $daysUntilStart ?> ngày)
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-secondary" disabled title="Không thể hủy (< 3 ngày hoặc đã qua ngày bắt đầu)">
                                                        <i class="fas fa-ban"></i> Không thể hủy
                                                    </button>
                                                <?php endif; ?>
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
    document.querySelectorAll('.btn-cancel-tour').forEach(btn => {
        btn.addEventListener('click', function() {
            const assignmentId = this.dataset.assignmentId;
            const tourName = this.dataset.tourName;
            const daysLeft = this.dataset.daysLeft;

            if (confirm(`Bạn có chắc muốn hủy nhận tour "${tourName}"?\n\nCòn ${daysLeft} ngày trước ngày bắt đầu.\nHành động này không thể hoàn tác.`)) {
                // Send AJAX request
                fetch('<?= BASE_URL_ADMIN ?>&action=guide/cancelAssignment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'assignment_id=' + assignmentId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Lỗi: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi hủy tour');
                    });
            }
        });
    });
</script>

<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>