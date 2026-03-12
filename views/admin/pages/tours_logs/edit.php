<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
?>
<main class="dashboard tour-logs-page">
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
                        <?php if (!empty($log['tour_id'])): ?>
                            <span class="breadcrumb-separator">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                            <a href="<?= BASE_URL_ADMIN ?>&action=tours_logs/tour_detail&id=<?= $log['tour_id'] ?>" class="breadcrumb-link">
                                <span>Chi tiết Tour</span>
                            </a>
                        <?php endif; ?>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current">Chỉnh sửa</span>
                    </div>
                </div>
            </div>
        </header>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title fw-bold text-warning mb-0">
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa nhật ký
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?= BASE_URL_ADMIN . '&action=tours_logs/update' ?>" method="POST">
                            <input type="hidden" name="id" value="<?= $log['id'] ?>">

                            <!-- Tour Selection -->
                            <div class="mb-4">
                                <label for="tour_id" class="form-label fw-medium">Tour <span class="text-danger">*</span></label>
                                <select class="form-select" id="tour_id" name="tour_id" required>
                                    <?php foreach ($tours as $tour): ?>
                                        <option value="<?= $tour['id'] ?>" <?= ($log['tour_id'] == $tour['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tour['name'] ?? '') ?> (#<?= htmlspecialchars($tour['id'] ?? '') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Date & Guide -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="date" class="form-label fw-medium">Ngày ghi nhận <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date" name="date" value="<?= date('Y-m-d', strtotime($log['date'])) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Hướng dẫn viên</label>
                                    <input type="text" class="form-control bg-light" value="<?= $_SESSION['user_name'] ?? 'Admin' ?>" readonly>
                                    <input type="hidden" name="guide_id" value="<?= $log['guide_id'] ?>">
                                </div>
                            </div>

                            <!-- Main Content -->
                            <div class="mb-4">
                                <label for="description" class="form-label fw-medium">Mô tả hoạt động <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($log['description'] ?? '') ?></textarea>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="weather" class="form-label fw-medium">Thời tiết</label>
                                    <input type="text" class="form-control" id="weather" name="weather" value="<?= htmlspecialchars($log['weather'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="health_status" class="form-label fw-medium">Tình trạng sức khỏe đoàn</label>
                                    <input type="text" class="form-control" id="health_status" name="health_status" value="<?= htmlspecialchars($log['health_status'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- Issues -->
                            <div class="mb-4">
                                <label for="issue" class="form-label fw-medium text-danger">Vấn đề phát sinh</label>
                                <textarea class="form-control border-danger bg-danger bg-opacity-10" id="issue" name="issue" rows="2"><?= htmlspecialchars($log['issue'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="solution" class="form-label fw-medium text-success">Giải pháp đã thực hiện</label>
                                <textarea class="form-control border-success bg-success bg-opacity-10" id="solution" name="solution" rows="2"><?= htmlspecialchars($log['solution'] ?? '') ?></textarea>
                            </div>

                            <!-- Feedback & Rating -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-8">
                                    <label for="customer_feedback" class="form-label fw-medium">Phản hồi của khách</label>
                                    <input type="text" class="form-control" id="customer_feedback" name="customer_feedback" value="<?= htmlspecialchars($log['customer_feedback'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="guide_rating" class="form-label fw-medium">Tự đánh giá (1-5)</label>
                                    <select class="form-select" id="guide_rating" name="guide_rating">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <option value="<?= $i ?>" <?= ($log['guide_rating'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                <a href="<?= !empty($log['tour_id']) ? BASE_URL_ADMIN . '&action=tours_logs/tour_detail&id=' . $log['tour_id'] : BASE_URL_ADMIN . '&action=tours_logs' ?>" class="btn btn-light">
                                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                                </a>
                                <button type="submit" class="btn btn-warning px-4">
                                    <i class="fas fa-save me-2"></i>Cập nhật
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>