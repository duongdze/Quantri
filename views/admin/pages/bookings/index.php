<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

// Lấy role của user
$userRole = $_SESSION['user']['role'] ?? 'customer';
$isAdmin = $userRole === 'admin';
$isGuide = $userRole === 'guide';
?>
<main class="dashboard">
    <div class="dashboard-container">
        <!-- Modern Header -->
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
                        <span class="breadcrumb-current">Quản lý Booking</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-calendar-check title-icon"></i>
                            Quản lý Booking
                        </h1>
                        <p class="page-subtitle">Quản lý toàn bộ các booking và đặt tour trong hệ thống</p>
                    </div>
                </div>
                <?php if ($isAdmin): ?>
                    <div class="header-right">
                        <button class="btn btn-modern btn-primary btn-lg" onclick="window.location.href='<?= BASE_URL_ADMIN . '&action=bookings/create' ?>'">
                            <i class="fas fa-plus-circle me-2"></i>
                            Tạo Booking Mới
                        </button>
                    </div>
                <?php endif; ?>
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
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
                        <div class="stat-label">Tổng Booking</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+8%</span>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['pending'] ?? 0) ?></div>
                        <div class="stat-label">Chờ Xác Nhận</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-down"></i>
                        <span>-3%</span>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['deposited'] ?? 0) ?></div>
                        <div class="stat-label">Đã Cọc</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+12%</span>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($stats['completed'] ?? 0) ?></div>
                        <div class="stat-label">Hoàn Tất</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>+15%</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Advanced Filters -->
        <section class="filters-section">
            <div class="filter-card">
                <div class="filter-header">
                    <h3 class="filter-title">
                        <i class="fas fa-filter"></i>
                        Bộ Lọc Nâng Cao
                    </h3>
                    <div class="filter-actions">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                            <i class="fas fa-redo"></i>
                            Reset
                        </button>
                    </div>
                </div>

                <form id="booking-filters" method="GET" action="<?= BASE_URL_ADMIN . '&action=bookings' ?>" class="filter-form">
                    <input type="hidden" name="action" value="bookings">

                    <!-- Basic Filters -->
                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="filter-label">Tìm kiếm</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" name="keyword"
                                    value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>"
                                    placeholder="Tên khách hàng, tour...">
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Trạng thái</label>
                            <select class="form-select" name="status">
                                <option value="">Tất cả</option>
                                <option value="cho_xac_nhan" <?= (($_GET['status'] ?? '') == 'cho_xac_nhan') ? 'selected' : '' ?>>
                                    Chờ Xác Nhận
                                </option>
                                <option value="da_coc" <?= (($_GET['status'] ?? '') == 'da_coc') ? 'selected' : '' ?>>
                                    Đã Cọc
                                </option>
                                <option value="hoan_tat" <?= (($_GET['status'] ?? '') == 'hoan_tat') ? 'selected' : '' ?>>
                                    Hoàn Tất
                                </option>
                                <option value="da_huy" <?= (($_GET['status'] ?? '') == 'da_huy') ? 'selected' : '' ?>>
                                    Đã Hủy
                                </option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Từ ngày</label>
                            <input type="date" class="form-control" name="date_from"
                                value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>" />
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Đến ngày</label>
                            <input type="date" class="form-control" name="date_to"
                                value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>" />
                        </div>
                    </div>

                    <!-- Sort and Display Options -->
                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="filter-label">Sắp xếp</label>
                            <div class="sort-controls">
                                <select class="form-select" name="sort_by">
                                    <option value="">Mặc định</option>
                                    <option value="booking_date" <?= (($_GET['sort_by'] ?? '') == 'booking_date') ? 'selected' : '' ?>>
                                        Ngày đặt
                                    </option>
                                    <option value="total_price" <?= (($_GET['sort_by'] ?? '') == 'total_price') ? 'selected' : '' ?>>
                                        Tổng tiền
                                    </option>
                                    <option value="customer_name" <?= (($_GET['sort_by'] ?? '') == 'customer_name') ? 'selected' : '' ?>>
                                        Tên khách hàng
                                    </option>
                                </select>
                                <select class="form-select" name="sort_dir">
                                    <option value="DESC" <?= (($_GET['sort_dir'] ?? '') == 'DESC') ? 'selected' : '' ?>>
                                        Giảm dần
                                    </option>
                                    <option value="ASC" <?= (($_GET['sort_dir'] ?? '') == 'ASC') ? 'selected' : '' ?>>
                                        Tăng dần
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="filter-group filter-actions-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>
                                Tìm kiếm
                            </button>
                            <a href="<?= BASE_URL_ADMIN . '&action=bookings' ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Xóa lọc
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- Bookings Table Section -->
        <section class="tours-section">
            <div class="tours-header">
                <div class="tours-info">
                    <div class="select-all-wrapper">
                        <i class="fas fa-list"></i>
                        <label class="select-all-label">
                            Danh sách Booking
                        </label>
                    </div>
                    <div class="tours-count">
                        <span class="count-info">
                            <?= count($bookings) ?> booking
                        </span>
                    </div>
                </div>
            </div>

            <div class="tours-container">
                <?php if (!empty($bookings)) : ?>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Mã</th>
                                    <th>Khách hàng</th>
                                    <th>Tour</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stt = 1;
                                foreach ($bookings as $booking) : ?>
                                    <tr>
                                        <td><strong><?= $stt++ ?></strong></td>
                                        <td>
                                            <div class="customer-info">
                                                <i class="fas fa-user-circle me-2 text-primary"></i>
                                                <?= htmlspecialchars($booking['customer_name'] ?? 'N/A') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="tour-info">
                                                <i class="fas fa-map-marked-alt me-2 text-info"></i>
                                                <?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar me-2 text-muted"></i>
                                            <?= date('d/m/Y', strtotime($booking['booking_date'])) ?>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                <?= number_format($booking['final_price'] ?? 0, 0, ',', '.') ?> ₫
                                            </strong>
                                        </td>
                                        <td>
                                            <?php
                                            $statusText = 'Chờ Xác Nhận';
                                            $statusClass = 'warning';
                                            $statusIcon = 'clock';

                                            if ($booking['status'] === 'hoan_tat') {
                                                $statusText = 'Hoàn Tất';
                                                $statusClass = 'success';
                                                $statusIcon = 'check-circle';
                                            } elseif ($booking['status'] === 'da_coc') {
                                                $statusText = 'Đã Cọc';
                                                $statusClass = 'info';
                                                $statusIcon = 'money-bill-wave';
                                            } elseif ($booking['status'] === 'da_huy') {
                                                $statusText = 'Đã Hủy';
                                                $statusClass = 'danger';
                                                $statusIcon = 'times-circle';
                                            }
                                            ?>
                                            <span class="badge badge-modern badge-<?= $statusClass ?>">
                                                <i class="fas fa-<?= $statusIcon ?> me-1"></i>
                                                <?= $statusText ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?= BASE_URL_ADMIN . '&action=bookings/detail&id=' . $booking['id'] ?>"
                                                    class="btn-action btn-view"
                                                    data-bs-toggle="tooltip"
                                                    title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($isAdmin): ?>
                                                    <a href="<?= BASE_URL_ADMIN . '&action=bookings/edit&id=' . $booking['id'] ?>"
                                                        class="btn-action btn-edit"
                                                        data-bs-toggle="tooltip"
                                                        title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button"
                                                        class="btn-action btn-delete delete-booking"
                                                        data-id="<?= $booking['id'] ?>"
                                                        data-name="<?= htmlspecialchars($booking['customer_name']) ?>"
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
                <?php else : ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <h3 class="empty-title">Chưa có booking nào</h3>
                        <p class="empty-description">
                            Bắt đầu tạo booking đầu tiên cho khách hàng của bạn.
                        </p>
                        <button class="btn btn-primary" onclick="window.location.href='<?= BASE_URL_ADMIN . '&action=bookings/create' ?>'">
                            <i class="fas fa-plus me-2"></i>
                            Tạo Booking Mới
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Xác nhận xóa booking
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Bạn có chắc chắn muốn xóa booking của "<span id="delete-booking-name"></span>"?
                </div>
                <p class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Hành động này không thể hoàn tác.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>
                    Hủy
                </button>
                <form id="delete-form" method="POST" style="display: inline;">
                    <input type="hidden" name="id" id="delete-booking-id">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>
                        Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Delete booking functionality
        document.querySelectorAll('.delete-booking').forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.dataset.id;
                const bookingName = this.dataset.name;

                document.getElementById('delete-booking-id').value = bookingId;
                document.getElementById('delete-booking-name').textContent = bookingName;

                const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                modal.show();
            });
        });

        // Handle delete form submission
        document.getElementById('delete-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const bookingId = document.getElementById('delete-booking-id').value;
            window.location.href = '<?= BASE_URL_ADMIN ?>&action=bookings/delete&id=' + bookingId;
        });
    });

    function resetFilters() {
        document.getElementById('booking-filters').reset();
        filterBookings();
    }

    function filterBookings() {
        const keyword = document.querySelector('[name="keyword"]').value.toLowerCase();
        const status = document.querySelector('[name="status"]').value;
        const dateFrom = document.querySelector('[name="date_from"]').value;
        const dateTo = document.querySelector('[name="date_to"]').value;
        const sortBy = document.querySelector('[name="sort_by"]').value;
        const sortDir = document.querySelector('[name="sort_dir"]').value;

        const tbody = document.querySelector('.table-modern tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));

        // Filter rows
        let filteredRows = rows.filter(row => {
            const customerName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const tourName = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const rowStatus = row.querySelector('.badge-modern').textContent.trim();
            const bookingDate = row.querySelector('td:nth-child(4)').textContent.trim();

            // Filter by keyword
            if (keyword && !customerName.includes(keyword) && !tourName.includes(keyword)) {
                return false;
            }

            // Filter by status
            if (status) {
                const statusMap = {
                    'cho_xac_nhan': 'Chờ Xác Nhận',
                    'da_coc': 'Đã Cọc',
                    'hoan_tat': 'Hoàn Tất',
                    'da_huy': 'Đã Hủy'
                };
                if (rowStatus !== statusMap[status]) {
                    return false;
                }
            }

            // Filter by date range
            if (dateFrom || dateTo) {
                const dateParts = bookingDate.split('/');
                if (dateParts.length === 3) {
                    const rowDate = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0];
                    if (dateFrom && rowDate < dateFrom) return false;
                    if (dateTo && rowDate > dateTo) return false;
                }
            }

            return true;
        });

        // Sort rows
        if (sortBy) {
            filteredRows.sort((a, b) => {
                let aVal, bVal;
                if (sortBy === 'booking_date') {
                    const aDate = a.querySelector('td:nth-child(4)').textContent.trim();
                    const bDate = b.querySelector('td:nth-child(4)').textContent.trim();
                    aVal = aDate.split('/').reverse().join('');
                    bVal = bDate.split('/').reverse().join('');
                } else if (sortBy === 'total_price') {
                    aVal = parseInt(a.querySelector('td:nth-child(5)').textContent.replace(/[^\d]/g, ''));
                    bVal = parseInt(b.querySelector('td:nth-child(5)').textContent.replace(/[^\d]/g, ''));
                } else if (sortBy === 'customer_name') {
                    aVal = a.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    bVal = b.querySelector('td:nth-child(2)').textContent.toLowerCase();
                }
                return sortDir === 'ASC' ? (aVal > bVal ? 1 : -1) : (aVal < bVal ? 1 : -1);
            });
        }

        // Hide all rows
        rows.forEach(row => row.style.display = 'none');

        // Show filtered rows
        filteredRows.forEach(row => row.style.display = '');

        // Update count
        const countElement = document.querySelector('.count-info');
        if (countElement) {
            countElement.textContent = filteredRows.length + ' booking';
        }
    }

    // Add event listener to form submit
    document.getElementById('booking-filters').addEventListener('submit', function(e) {
        e.preventDefault();
        filterBookings();
    });
</script>

<?php
include_once PATH_VIEW_ADMIN . 'default/footer.php';
?>