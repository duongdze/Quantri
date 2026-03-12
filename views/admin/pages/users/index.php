<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

$currentUserRole = $_SESSION['user']['role'] ?? 'customer';
?>

<main class="dashboard user-index-page">
    <div class="dashboard-container">
        <!-- Modern Page Header -->
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
                        <span class="breadcrumb-current">Quản lý User</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-users title-icon"></i>
                            Quản lý User
                        </h1>
                        <p class="page-subtitle">Quản lý người dùng trong hệ thống</p>
                    </div>
                </div>
                <div class="header-right">
                    <button class="btn btn-modern btn-primary btn-lg" onclick="window.location.href='<?= BASE_URL_ADMIN . '&action=users/create' ?>'">
                        <i class="fas fa-plus-circle me-2"></i>
                        Thêm User Mới
                    </button>
                </div>
            </div>
        </header>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-modern alert-success alert-dismissible fade show" role="alert">
                <div class="alert-content">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <span><?= $_SESSION['success'] ?></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-modern alert-danger alert-dismissible fade show" role="alert">
                <div class="alert-content">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <span><?= $_SESSION['error'] ?></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <section class="stats-section">
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['customers'] ?? 0) ?></div>
                        <div class="stat-label">Tổng Khách hàng</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+<?= $stats['new_users'] ?? 0 ?></span>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['customers'] ?? 0) ?></div>
                        <div class="stat-label">Khách hàng</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filters Section -->
        <section class="filters-section">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Bộ lọc
                    </h5>
                </div>
                <div class="card-body">
                    <form id="user-filters" onsubmit="return false;" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" name="search" class="form-control" placeholder="Tìm theo tên hoặc email..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Vai trò</label>
                            <select name="role" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="customer">Khách hàng</option>
                                <option value="guide">HDV</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" onclick="filterUsers()" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>
                                Tìm kiếm
                            </button>
                            <button type="button" onclick="resetFilters()" class="btn btn-secondary">
                                <i class="fas fa-redo me-1"></i>
                                Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Users Table -->
        <section class="table-section">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Danh sách User (<?= count($users) ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($users)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Họ tên</th>
                                        <th>Email</th>
                                        <th>Điện thoại</th>
                                        <th>Vai trò</th>
                                        <th>Ngày tạo</th>
                                        <th class="text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><strong>#<?= $user['user_id'] ?></strong></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2">
                                                        <i class="fas fa-user-circle fa-2x text-muted"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php
                                                $roleMap = [
                                                    'customer' => ['text' => 'Khách hàng', 'class' => 'success'],
                                                    'guide' => ['text' => 'HDV', 'class' => 'info'],
                                                    'admin' => ['text' => 'Admin', 'class' => 'danger']
                                                ];
                                                $roleInfo = $roleMap[$user['role']] ?? ['text' => $user['role'], 'class' => 'secondary'];
                                                ?>
                                                <span class="badge bg-<?= $roleInfo['class'] ?>">
                                                    <?= $roleInfo['text'] ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="<?= BASE_URL_ADMIN ?>&action=users/detail&id=<?= $user['user_id'] ?>"
                                                        class="btn btn-sm btn-info"
                                                        data-bs-toggle="tooltip"
                                                        title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL_ADMIN ?>&action=users/edit&id=<?= $user['user_id'] ?>"
                                                        class="btn btn-sm btn-warning"
                                                        data-bs-toggle="tooltip"
                                                        title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php
                                                    $canDelete = false;
                                                    if ($currentUserRole === 'admin') {
                                                        $canDelete = ($user['role'] !== 'admin');
                                                    } elseif ($currentUserRole === 'guide') {
                                                        $canDelete = ($user['role'] === 'customer');
                                                    }
                                                    if ($user['user_id'] == $_SESSION['user']['user_id']) {
                                                        $canDelete = false;
                                                    }

                                                    if ($canDelete):
                                                    ?>
                                                        <button class="btn btn-sm btn-danger delete-user-btn"
                                                            data-id="<?= $user['user_id'] ?>"
                                                            data-name="<?= htmlspecialchars($user['full_name']) ?>"
                                                            data-bs-toggle="tooltip"
                                                            title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Không có user nào</h5>
                            <p class="text-muted">Chưa có user nào trong hệ thống</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa user <strong id="delete-user-name"></strong>?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">Xóa</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Delete user functionality
    let deleteUserId = null;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

    document.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            deleteUserId = this.dataset.id;
            document.getElementById('delete-user-name').textContent = this.dataset.name;
            deleteModal.show();
        });
    });

    document.getElementById('confirm-delete-btn').addEventListener('click', function() {
        if (!deleteUserId) return;

        fetch('<?= BASE_URL_ADMIN ?>&action=users/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + deleteUserId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xóa user');
            });
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Client-side filtering functions
    function filterUsers() {
        const searchTerm = document.querySelector('[name="search"]').value.toLowerCase();
        const roleFilter = document.querySelector('[name="role"]').value;

        const tbody = document.querySelector('.table tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));
        let visibleCount = 0;

        rows.forEach(row => {
            const fullName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const roleBadge = row.querySelector('td:nth-child(5) .badge');

            // Get role value from badge class
            let userRole = '';
            if (roleBadge.classList.contains('bg-success')) userRole = 'customer';
            else if (roleBadge.classList.contains('bg-info')) userRole = 'guide';
            else if (roleBadge.classList.contains('bg-danger')) userRole = 'admin';

            // Filter by search term
            const matchesSearch = !searchTerm ||
                fullName.includes(searchTerm) ||
                email.includes(searchTerm);

            // Filter by role
            const matchesRole = !roleFilter || userRole === roleFilter;

            // Show/hide row
            if (matchesSearch && matchesRole) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update count
        const countElement = document.querySelector('.card-title');
        if (countElement) {
            countElement.innerHTML = '<i class="fas fa-list me-2"></i>Danh sách User (' + visibleCount + ')';
        }
    }

    function resetFilters() {
        document.getElementById('user-filters').reset();
        filterUsers();
    }

    // Add event listeners to filter inputs
    document.querySelector('[name="search"]').addEventListener('input', filterUsers);
    document.querySelector('[name="role"]').addEventListener('change', filterUsers);
</script>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>