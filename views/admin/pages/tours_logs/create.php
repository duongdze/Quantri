<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

$preSelectedTourId = $_GET['tour_id'] ?? null;
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
                        <?php if ($preSelectedTourId): ?>
                            <span class="breadcrumb-separator">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                            <a href="<?= BASE_URL_ADMIN ?>&action=tours_logs/tour_detail&id=<?= $preSelectedTourId ?>" class="breadcrumb-link">
                                <span>Chi tiết Tour</span>
                            </a>
                        <?php endif; ?>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current">Thêm mới</span>
                    </div>
                </div>
            </div>
        </header>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title fw-bold text-primary mb-0">
                            <i class="fas fa-plus-circle me-2"></i>Thêm nhật ký mới
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?= BASE_URL_ADMIN . '&action=tours_logs/store' ?>" method="POST">

                            <!-- Tour Selection -->
                            <div class="mb-4">
                                <label for="tour_id" class="form-label fw-medium">Chọn Tour <span class="text-danger">*</span></label>
                                <select class="form-select" id="tour_id" name="tour_id" required>
                                    <option value="">-- Chọn Tour --</option>
                                    <?php foreach ($tours as $tour): ?>
                                        <option value="<?= $tour['id'] ?>" <?= ($preSelectedTourId == $tour['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tour['name']) ?> (#<?= htmlspecialchars($tour['id']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Date & Guide -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="date" class="form-label fw-medium">Ngày ghi nhận <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date" name="date" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Hướng dẫn viên</label>
                                    <input type="text" class="form-control bg-light" value="<?= $_SESSION['user_name'] ?? 'Admin' ?>" readonly>
                                    <input type="hidden" name="guide_id" value="<?= $_SESSION['guide_id'] ?? '' ?>">
                                    <small class="text-muted">Tự động lấy theo tài khoản đăng nhập</small>
                                </div>
                            </div>

                            <!-- Main Content -->
                            <div class="mb-4">
                                <label for="description" class="form-label fw-medium">Mô tả hoạt động <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="4" placeholder="Mô tả chi tiết các hoạt động trong ngày..." required></textarea>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="weather" class="form-label fw-medium">Thời tiết</label>
                                    <input type="text" class="form-control" id="weather" name="weather" placeholder="VD: Nắng đẹp, 25 độ C">
                                </div>
                                <div class="col-md-6">
                                    <label for="health_status" class="form-label fw-medium">Tình trạng sức khỏe đoàn</label>
                                    <input type="text" class="form-control" id="health_status" name="health_status" placeholder="VD: Tốt, có 1 khách say xe">
                                </div>
                            </div>

                            <!-- Issues -->
                            <div class="mb-4">
                                <label for="issue" class="form-label fw-medium text-danger">Vấn đề phát sinh (nếu có)</label>
                                <textarea class="form-control border-danger bg-danger bg-opacity-10" id="issue" name="issue" rows="2" placeholder="Mô tả sự cố hoặc vấn đề..."></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="solution" class="form-label fw-medium text-success">Giải pháp đã thực hiện</label>
                                <textarea class="form-control border-success bg-success bg-opacity-10" id="solution" name="solution" rows="2" placeholder="Cách xử lý vấn đề..."></textarea>
                            </div>

                            <!-- Feedback & Rating -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-8">
                                    <label for="customer_feedback" class="form-label fw-medium">Phản hồi của khách</label>
                                    <input type="text" class="form-control" id="customer_feedback" name="customer_feedback" placeholder="Ghi nhận ý kiến khách hàng">
                                </div>
                                <div class="col-md-4">
                                    <label for="guide_rating" class="form-label fw-medium">Tự đánh giá (1-5)</label>
                                    <select class="form-select" id="guide_rating" name="guide_rating">
                                        <option value="5">5 - Xuất sắc</option>
                                        <option value="4">4 - Tốt</option>
                                        <option value="3">3 - Khá</option>
                                        <option value="2">2 - Trung bình</option>
                                        <option value="1">1 - Kém</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                <a href="<?= $preSelectedTourId ? BASE_URL_ADMIN . '&action=tours_logs/tour_detail&id=' . $preSelectedTourId : BASE_URL_ADMIN . '&action=tours_logs' ?>" class="btn btn-light">
                                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i>Lưu nhật ký
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