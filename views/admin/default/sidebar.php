<?php
// Hàm helper để kiểm tra active
function isActive($action)
{
    $currentAction = $_GET['action'] ?? '';
    return ($currentAction == $action) ? 'active' : '';
}

// Hàm kiểm tra menu cha có active không (kiểm tra các menu con)
function isParentActive($actions)
{
    $currentAction = $_GET['action'] ?? '';

    // Nếu $actions là string, chuyển thành array
    if (!is_array($actions)) {
        $actions = [$actions];
    }

    // Check nếu current action nằm trong danh sách hoặc bắt đầu bằng một trong các prefix
    foreach ($actions as $action) {
        // Exact match
        if ($currentAction === $action) {
            return 'show';
        }

        // Match with slash separator (e.g., "tours/edit" matches "tours" but "tours_logs" does not)
        if (strpos($currentAction, $action . '/') === 0) {
            return 'show';
        }
    }

    return '';
}

// Lấy action hiện tại và role
$currentAction = $_GET['action'] ?? '';
$userRole = $_SESSION['user']['role'] ?? 'customer';
$isAdmin = $userRole === 'admin';
$isGuide = $userRole === 'guide';
?>

<aside class="sidebar vh-100 border-end bg-light overflow-y-auto">
    <div class="p-3">
        <h4 class="fw-bold text-center">Trang Quản Trị</h4>
    </div>
    <ul class="nav flex-column">
        <?php if ($isAdmin): ?>
            <!-- Dashboard - Chỉ Admin -->
            <li class="nav-item">
                <a class="nav-link <?= $currentAction === 'dashboard' || empty($currentAction) ? 'active' : '' ?>" href="<?= BASE_URL_ADMIN ?>&action=/">
                    <i class="fas fa-tachometer-alt fa-fw me-2"></i> Tổng quan
                </a>
            </li>

            <!-- Phần Tour - Chỉ Admin -->
            <li class="nav-item">
                <a class="nav-link dropdown-toggles d-flex justify-content-between align-items-center <?= isParentActive(['tours', 'policies', 'suppliers', 'tours_categories', 'tours_versions', 'tours_history']) ? 'active' : '' ?>"
                    href="#" role="button"
                    aria-expanded="<?= isParentActive(['tours', 'policies', 'suppliers', 'tours_categories', 'tours_versions', 'tours_history']) ? 'true' : 'false' ?>"
                    aria-controls="tourMenu"
                    data-menu-key="tours"
                    data-collapse-id="tourMenu">
                    <span><i class="fas fa-map-signs fa-fw me-2"></i>Tour</span>
                    <i class="fas fa-chevron-down fa-xs"></i>
                </a>
                <div class="collapse <?= isParentActive(['tours', 'policies', 'suppliers', 'tours_categories', 'tours_versions', 'tours_history']) ?>" id="tourMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('tours') ?>" href="<?= BASE_URL_ADMIN ?>&action=tours">
                                <i class="fas fa-list fa-fw me-2"></i> Danh sách Tour
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('tours_categories') ?>" href="<?= BASE_URL_ADMIN ?>&action=tours_categories">
                                <i class="fas fa-th-list fa-fw me-2"></i> Danh mục Tour
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('tours_versions') ?>" href="<?= BASE_URL_ADMIN ?>&action=tours_versions">
                                <i class="fas fa-code-branch fa-fw me-2"></i> Phiên bản Tour
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('policies') ?>" href="<?= BASE_URL_ADMIN ?>&action=policies">
                                <i class="fas fa-file-contract fa-fw me-2"></i> Chính sách
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('suppliers') ?>" href="<?= BASE_URL_ADMIN ?>&action=suppliers">
                                <i class="fas fa-handshake fa-fw me-2"></i> Nhà cung cấp
                            </a>
                        </li>
                        <!-- <li class="nav-item">
                            <a class="nav-link <?= isActive('tours_history') ?>" href="<?= BASE_URL_ADMIN ?>&action=tours_history">
                                <i class="fas fa-history fa-fw me-2"></i> Lịch sử Tour
                            </a>
                        </li> -->
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <!-- Phần Booking - Admin và Guide -->
        <li class="nav-item">
            <a class="nav-link dropdown-toggles d-flex justify-content-between align-items-center <?= isParentActive(['bookings', 'guides/available-tours']) ? 'active' : '' ?>"
                href="#" role="button"
                aria-expanded="<?= (isParentActive('bookings') || isActive('guides/available-tours')) ? 'true' : 'false' ?>"
                aria-controls="bookingMenu"
                data-menu-key="bookings"
                data-collapse-id="bookingMenu">
                <span><i class="fas fa-calendar-check fa-fw me-2"></i>Booking</span>
                <i class="fas fa-chevron-down fa-xs"></i>
            </a>
            <div class="collapse <?= (isParentActive('bookings') || isActive('guides/available-tours')) ? 'show' : '' ?>" id="bookingMenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a class="nav-link <?= isActive('bookings') ?>" href="<?= BASE_URL_ADMIN ?>&action=bookings">
                            <i class="fas fa-list fa-fw me-2"></i> Quản lý Booking
                        </a>
                    </li>
                    <?php if (in_array($_SESSION['user']['role'] ?? '', ['guide', 'admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('available-tours') ?>" href="<?= BASE_URL_ADMIN ?>&action=available-tours">
                                <i class="fas fa-hand-paper fa-fw me-2"></i> Tour Khả Dụng
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </li>

        <?php if ($isAdmin): ?>
            <!-- Phần Quản Lý Nhân Sự - Chỉ Admin -->
            <li class="nav-item">
                <a class="nav-link dropdown-toggles d-flex justify-content-between align-items-center <?= (isActive('guides') || isParentActive('bus-companies')) ? 'active' : '' ?>"
                    href="#" role="button"
                    aria-expanded="<?= (isActive('guides') || isParentActive('bus-companies')) ? 'true' : 'false' ?>"
                    aria-controls="staffMenu"
                    data-menu-key="staff"
                    data-collapse-id="staffMenu">
                    <span><i class="fas fa-users fa-fw me-2"></i>Quản Lý Nhân Sự</span>
                    <i class="fas fa-chevron-down fa-xs"></i>
                </a>
                <div class="collapse <?= (isActive('guides') || isParentActive('bus-companies')) ? 'show' : '' ?>" id="staffMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('guides') ?>" href="<?= BASE_URL_ADMIN ?>&action=guides">
                                <i class="fas fa-user-tie fa-fw me-2"></i> Quản lý HDV
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('bus-companies') ?>" href="<?= BASE_URL_ADMIN ?>&action=bus-companies">
                                <i class="fas fa-bus fa-fw me-2"></i> Quản lý Nhà Xe
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Users - Chỉ Admin -->
            <li class="nav-item">
                <a class="nav-link <?= isActive('users') ?>" href="<?= BASE_URL_ADMIN ?>&action=users">
                    <i class="fas fa-user fa-fw me-2"></i> User
                </a>
            </li>
        <?php endif; ?>

        <!-- Quản lý công việc - Admin và Guide -->
        <li class="nav-item">
            <a class="nav-link dropdown-toggles d-flex justify-content-between align-items-center <?= isParentActive(['tours_logs', 'guide/schedule']) ? 'active' : '' ?>"
                href="#" role="button"
                aria-expanded="<?= (isActive('tours_logs') || isActive('guide/schedule')) ? 'true' : 'false' ?>"
                aria-controls="workManagementMenu"
                data-menu-key="work-management"
                data-collapse-id="workManagementMenu">
                <span><i class="fas fa-briefcase fa-fw me-2"></i>Quản lý công việc</span>
                <i class="fas fa-chevron-down fa-xs"></i>
            </a>
            <div class="collapse <?= isParentActive(['tours_logs', 'guide/schedule']) ? 'show' : '' ?>" id="workManagementMenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a class="nav-link <?= isActive('tours_logs') ?>" href="<?= BASE_URL_ADMIN ?>&action=tours_logs">
                            <i class="fas fa-book fa-fw me-2"></i> Nhật ký Tour
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= isActive('guide/schedule') ?>" href="<?= BASE_URL_ADMIN ?>&action=guide/schedule">
                            <i class="fas fa-calendar-day fa-fw me-2"></i> Lịch làm việc HDV
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <?php if ($isAdmin): ?>
            <!-- Phần Báo cáo - Chỉ Admin -->
            <li class="nav-item">
                <a class="nav-link dropdown-toggles d-flex justify-content-between align-items-center <?= isParentActive('reports') ? 'active' : '' ?>"
                    href="#" role="button"
                    aria-expanded="<?= isParentActive('reports') ? 'true' : 'false' ?>"
                    aria-controls="reportsMenu"
                    data-menu-key="reports"
                    data-collapse-id="reportsMenu">
                    <span><i class="fas fa-chart-line fa-fw me-2"></i>Báo cáo</span>
                    <i class="fas fa-chevron-down fa-xs"></i>
                </a>
                <div class="collapse <?= isParentActive('reports') ?>" id="reportsMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('reports/financial') ?>" href="<?= BASE_URL_ADMIN ?>&action=reports/financial">
                                <i class="fas fa-dollar-sign fa-fw me-2"></i> Báo cáo tài chính
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= isActive('reports/bookings') ?>" href="<?= BASE_URL_ADMIN ?>&action=reports/bookings">
                                <i class="fas fa-calendar-check fa-fw me-2"></i> Báo cáo booking
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link <?= isActive('reports/feedback') ?>" href="<?= BASE_URL_ADMIN ?>&action=reports/feedback">
                                <i class="fas fa-comments fa-fw me-2"></i> Báo cáo phản hồi
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>
    </ul>
</aside>