<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

$user = $_SESSION['user'] ?? null;
?>

<main class="wrapper">
    <div class="main-content">
        <div class="page-header">
            <h1 class="h2">Thông tin tài khoản</h1>
            <p class="text-muted">Quản lý thông tin cá nhân của bạn</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Thông tin chung</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Tên:</label>
                                <p class="fw-500"><?= htmlspecialchars($user['full_name'] ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Email:</label>
                                <p class="fw-500"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Vai trò:</label>
                                <p class="fw-500">
                                    <?php
                                    $roleLabel = match ($user['role'] ?? 'user') {
                                        'admin' => 'Quản trị viên',
                                        'guide' => 'Hướng dẫn viên',
                                        'supplier' => 'Nhà cung cấp',
                                        default => ucfirst($user['role'] ?? 'user')
                                    };
                                    echo $roleLabel;
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Số điện thoại:</label>
                                <p class="fw-500"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Ngày tham gia:</label>
                                <p class="fw-500"><?= !empty($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : 'N/A' ?></p>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editAccountModal">
                                <i class="fas fa-edit"></i> Chỉnh sửa thông tin
                            </button>
                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="fas fa-lock"></i> Đổi mật khẩu
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Ảnh đại diện</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php
                        $avatarUrl = !empty($user['avatar']) ? BASE_ASSETS_UPLOADS . $user['avatar'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name'] ?? 'User') . '&background=0D6EFD&color=fff&size=200';
                        ?>
                        <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($user['full_name']) ?>" class="rounded-circle mb-3" id="avatarPreview" style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #e9ecef;">

                        <div>
                            <input type="file" id="avatarInput" accept="image/*" style="display: none;">
                            <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('avatarInput').click()">
                                <i class="fas fa-camera"></i> Đổi ảnh đại diện
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">Hoạt động</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            <i class="fas fa-clock"></i> Truy cập lần cuối: <br>
                            <strong>
                                <?php
                                $timezone = new DateTimeZone('Asia/Ho_Chi_Minh');
                                $lastLoginTime = !empty($_SESSION['user']['last_login']) ? new DateTime($_SESSION['user']['last_login'], new DateTimeZone('UTC')) : new DateTime('now', new DateTimeZone('UTC'));
                                $lastLoginTime->setTimezone($timezone);
                                echo $lastLoginTime->format('d/m/Y H:i');
                                ?>
                            </strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal Chỉnh sửa thông tin -->
<div class="modal fade" id="editAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa thông tin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editProfileForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Đổi mật khẩu -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Đổi mật khẩu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="changePasswordForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="new_password" required minlength="6">
                        <small class="text-muted">Tối thiểu 6 ký tự</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="confirm_password" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Đổi mật khẩu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Handle edit profile form
    document.getElementById('editProfileForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';

        fetch('<?= BASE_URL_ADMIN ?>&action=account/update-profile', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Lưu thay đổi';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra!');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Lưu thay đổi';
            });
    });

    // Handle change password form
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

        fetch('<?= BASE_URL_ADMIN ?>&action=account/change-password', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
                    this.reset();
                } else {
                    alert('❌ ' + data.message);
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Đổi mật khẩu';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra!');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Đổi mật khẩu';
            });
    });

    // Handle avatar upload
    document.getElementById('avatarInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Vui lòng chọn file ảnh!');
            return;
        }

        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Kích thước ảnh tối đa 5MB!');
            return;
        }

        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(file);

        // Upload image
        const formData = new FormData();
        formData.append('avatar', file);

        fetch('<?= BASE_URL_ADMIN ?>&action=account/update-avatar', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi upload ảnh!');
            });
    });
</script>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>