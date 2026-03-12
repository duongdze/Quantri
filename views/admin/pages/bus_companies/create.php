<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);
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
                        <span class="breadcrumb-current">Thêm Nhà Xe Mới</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-plus-circle title-icon"></i>
                            Thêm Nhà Xe Mới
                        </h1>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=bus-companies" class="btn btn-modern btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        Hủy bỏ
                    </a>
                    <button type="submit" form="bus-company-form" class="btn btn-modern btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Lưu Nhà Xe
                    </button>
                </div>
            </div>
        </header>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-modern alert-danger alert-dismissible fade show">
                <div class="alert-content">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <span><?= $_SESSION['error'] ?></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL_ADMIN ?>&action=bus-companies/store" id="bus-company-form">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Thông tin cơ bản -->
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
                                    <div class="form-floating">
                                        <input type="text" class="form-control <?= isset($errors['company_code']) ? 'is-invalid' : '' ?>"
                                            id="company_code" name="company_code" required
                                            value="<?= htmlspecialchars($old['company_code'] ?? '') ?>" placeholder=" ">
                                        <label>Mã nhà xe <span class="text-danger">*</span></label>
                                        <?php if (isset($errors['company_code'])): ?>
                                            <div class="invalid-feedback"><?= $errors['company_code'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control <?= isset($errors['company_name']) ? 'is-invalid' : '' ?>"
                                            id="company_name" name="company_name" required
                                            value="<?= htmlspecialchars($old['company_name'] ?? '') ?>" placeholder=" ">
                                        <label>Tên nhà xe <span class="text-danger">*</span></label>
                                        <?php if (isset($errors['company_name'])): ?>
                                            <div class="invalid-feedback"><?= $errors['company_name'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="contact_person" name="contact_person"
                                            value="<?= htmlspecialchars($old['contact_person'] ?? '') ?>" placeholder=" ">
                                        <label>Người liên hệ</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                                            id="phone" name="phone"
                                            value="<?= htmlspecialchars($old['phone'] ?? '') ?>" placeholder=" ">
                                        <label>Số điện thoại</label>
                                        <?php if (isset($errors['phone'])): ?>
                                            <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                            id="email" name="email"
                                            value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder=" ">
                                        <label>Email</label>
                                        <?php if (isset($errors['email'])): ?>
                                            <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="business_license" name="business_license"
                                            value="<?= htmlspecialchars($old['business_license'] ?? '') ?>" placeholder=" ">
                                        <label>Giấy phép kinh doanh</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="address" name="address" style="height: 80px" placeholder=" "><?= htmlspecialchars($old['address'] ?? '') ?></textarea>
                                        <label>Địa chỉ trụ sở</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin xe -->
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
                                    <div class="form-floating">
                                        <input type="number" class="form-control" id="total_vehicles" name="total_vehicles" min="0"
                                            value="<?= htmlspecialchars($old['total_vehicles'] ?? '0') ?>" placeholder=" ">
                                        <label>Tổng số xe</label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="vehicle_brand" name="vehicle_brand"
                                            value="<?= htmlspecialchars($old['vehicle_brand'] ?? '') ?>" placeholder=" ">
                                        <label>Hãng xe chủ yếu</label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" id="rating" name="rating" min="0" max="5" step="0.1"
                                            value="<?= htmlspecialchars($old['rating'] ?? '5.0') ?>" placeholder=" ">
                                        <label>Đánh giá</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="notes" name="notes" style="height: 100px" placeholder=" "><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
                                        <label>Ghi chú</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Trạng thái</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-floating">
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?= ($old['status'] ?? 'active') == 'active' ? 'selected' : '' ?>>Hoạt động</option>
                                    <option value="inactive" <?= ($old['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>Ngừng hoạt động</option>
                                </select>
                                <label>Trạng thái</label>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thao tác</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Tạo nhà xe
                                </button>
                                <a href="<?= BASE_URL_ADMIN ?>&action=bus-companies" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Hủy
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>