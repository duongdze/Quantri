<?php
$pageTitle = $pageTitle ?? 'Tài khoản – VietTour';
require_once PATH_VIEW_CLIENT . 'default/header.php';
$activeTab = $_GET['tab'] ?? 'info';
?>

<div class="container my-5">
    <div class="row g-4">

        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body text-center p-4" style="background: linear-gradient(135deg,#1e6fff,#6b8ff8);">
                    <div class="mx-auto mb-3" style="width:72px;height:72px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;font-weight:800;">
                        <?= strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <h6 class="text-white fw-bold mb-1"><?= htmlspecialchars($user['full_name'] ?? '') ?></h6>
                    <small class="text-white opacity-75"><?= htmlspecialchars($user['email'] ?? '') ?></small>
                </div>
                <div class="list-group list-group-flush">
                    <a href="?action=my-account&tab=info" class="list-group-item list-group-item-action <?= $activeTab === 'info' ? 'active' : '' ?>">
                        <i class="fas fa-user me-2"></i>Thông tin cá nhân
                    </a>
                    <a href="?action=my-account&tab=password" class="list-group-item list-group-item-action <?= $activeTab === 'password' ? 'active' : '' ?>">
                        <i class="fas fa-lock me-2"></i>Đổi mật khẩu
                    </a>
                    <a href="?action=my-bookings" class="list-group-item list-group-item-action">
                        <i class="fas fa-ticket-alt me-2"></i>Đơn hàng của tôi
                    </a>
                    <a href="?action=logout" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">

            <!-- Alerts -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success border-0 rounded-3 mb-4 d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i> <?= $success ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger border-0 rounded-3 mb-4 d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Tab: Thông tin cá nhân -->
            <?php if ($activeTab === 'info'): ?>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h5 class="fw-bold text-primary"><i class="fas fa-user-edit me-2"></i>Thông tin cá nhân</h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= BASE_URL ?>?action=my-account-update" method="POST">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control bg-light border-0 rounded-3" 
                                       value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control bg-light border-0 rounded-3" 
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                                <small class="text-muted">Email không thể thay đổi.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control bg-light border-0 rounded-3" 
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="0901 234 567">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Vai trò</label>
                                <input type="text" class="form-control bg-light border-0 rounded-3" 
                                       value="<?= ucfirst($user['role'] ?? 'customer') ?>" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Địa chỉ</label>
                                <input type="text" name="address" class="form-control bg-light border-0 rounded-3" 
                                       value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Địa chỉ của bạn">
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary px-5 rounded-pill">
                                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tab: Đổi mật khẩu -->
            <?php elseif ($activeTab === 'password'): ?>
            <div class="card border-0 shadow-sm rounded-4" id="password">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h5 class="fw-bold text-primary"><i class="fas fa-shield-alt me-2"></i>Đổi mật khẩu</h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= BASE_URL ?>?action=my-account-password" method="POST">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                <input type="password" name="current_password" class="form-control bg-light border-0 rounded-3" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mật khẩu mới <span class="text-danger">*</span></label>
                                <input type="password" name="new_password" class="form-control bg-light border-0 rounded-3" minlength="6" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                                <input type="password" name="confirm_password" class="form-control bg-light border-0 rounded-3" minlength="6" required>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-warning px-5 rounded-pill fw-bold">
                                    <i class="fas fa-key me-2"></i>Đổi mật khẩu
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
