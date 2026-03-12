<?php

class UserController
{
    private $model;

    public function __construct()
    {
        $this->model = new User();
    }

    /**
     * Display list of users
     */
    public function index()
    {
        // Check permission
        $currentUserRole = $_SESSION['user']['role'] ?? 'customer';
        if (!in_array($currentUserRole, ['admin', 'guide'])) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
            header('Location: ' . BASE_URL_ADMIN . '&action=/');
            exit;
        }

        // Get filters
        $filters = [
            'search' => $_GET['search'] ?? ''
        ];

        // Force filter to only show customers (admin and guide users are managed separately)
        $filters['role'] = 'customer';

        $users = $this->model->getAll($filters);
        $stats = $this->model->getStats();

        require_once PATH_VIEW_ADMIN . 'pages/users/index.php';
    }

    /**
     * Show create user form
     */
    public function create()
    {
        $currentUserRole = $_SESSION['user']['role'] ?? 'customer';

        if (!in_array($currentUserRole, ['admin', 'guide'])) {
            $_SESSION['error'] = 'Bạn không có quyền tạo người dùng';
            header('Location: ' . BASE_URL_ADMIN . '&action=users');
            exit;
        }

        require_once PATH_VIEW_ADMIN . 'pages/users/create.php';
    }

    /**
     * Store new user
     */
    public function store()
    {
        $currentUserRole = $_SESSION['user']['role'] ?? 'customer';

        if (!in_array($currentUserRole, ['admin', 'guide'])) {
            $_SESSION['error'] = 'Bạn không có quyền tạo người dùng';
            header('Location: ' . BASE_URL_ADMIN . '&action=users');
            exit;
        }

        // Validate input
        $errors = [];

        if (empty($_POST['full_name'])) {
            $errors[] = 'Họ tên không được để trống';
        }

        if (empty($_POST['email'])) {
            $errors[] = 'Email không được để trống';
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        } elseif ($this->model->emailExists($_POST['email'])) {
            $errors[] = 'Email đã tồn tại trong hệ thống';
        }

        if (empty($_POST['password'])) {
            $errors[] = 'Mật khẩu không được để trống';
        } elseif (strlen($_POST['password']) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
        }

        if (empty($_POST['role'])) {
            $errors[] = 'Vui lòng chọn vai trò';
        } elseif (!$this->model->canCreateRole($currentUserRole, $_POST['role'])) {
            $errors[] = 'Bạn không có quyền tạo vai trò này';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ' . BASE_URL_ADMIN . '&action=users/create');
            exit;
        }

        // Create user
        $data = [
            'full_name' => trim($_POST['full_name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone'] ?? ''),
            'password' => $_POST['password'],
            'role' => $_POST['role']
        ];

        $userId = $this->model->create($data);

        if ($userId) {
            $_SESSION['success'] = 'Tạo người dùng thành công';
            header('Location: ' . BASE_URL_ADMIN . '&action=users');
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra khi tạo người dùng';
            header('Location: ' . BASE_URL_ADMIN . '&action=users/create');
        }
        exit;
    }

    /**
     * Show edit user form
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy người dùng';
            header('Location: ' . BASE_URL_ADMIN . '&action=users');
            exit;
        }

        $user = $this->model->getById($id);
        if (!$user) {
            $_SESSION['error'] = 'Không tìm thấy người dùng';
            header('Location: ' . BASE_URL_ADMIN . '&action=users');
            exit;
        }

        $currentUserId = $_SESSION['user']['user_id'] ?? null;
        $currentUserRole = $_SESSION['user']['role'] ?? 'customer';

        // Only allow editing customer users (admin and guide users are managed separately)
        if ($user['role'] !== 'customer') {
            $_SESSION['error'] = 'Không thể chỉnh sửa user này. Vui lòng sử dụng trang quản lý tương ứng.';
            header('Location: ' . BASE_URL_ADMIN . '&action=users');
            exit;
        }

        require_once PATH_VIEW_ADMIN . 'pages/users/edit.php';
    }

    /**
     * Update user
     */
    public function update()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy người dùng';
            header('Location: ' . BASE_URL_ADMIN . '&action=users');
            exit;
        }

        $user = $this->model->getById($id);
        if (!$user) {
            $_SESSION['error'] = 'Không tìm thấy người dùng';
            header('Location: ' . BASE_URL_ADMIN . '&action=users');
            exit;
        }

        $currentUserId = $_SESSION['user']['user_id'] ?? null;
        $currentUserRole = $_SESSION['user']['role'] ?? 'customer';

        // Check permission
        if (!$this->model->canEdit($currentUserId, $currentUserRole, $user['id'], $user['role'])) {
            $_SESSION['error'] = 'Bạn không có quyền chỉnh sửa người dùng này';
            header('Location: ' . BASE_URL_ADMIN . '&action=users');
            exit;
        }

        // Validate input
        $errors = [];

        if (empty($_POST['full_name'])) {
            $errors[] = 'Họ tên không được để trống';
        }

        if (empty($_POST['email'])) {
            $errors[] = 'Email không được để trống';
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        } elseif ($this->model->emailExists($_POST['email'], $id)) {
            $errors[] = 'Email đã tồn tại trong hệ thống';
        }

        // Validate password if provided
        if (!empty($_POST['password']) && strlen($_POST['password']) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
        }

        // Validate role change
        if (isset($_POST['role']) && $_POST['role'] !== $user['role']) {
            if ($currentUserRole !== 'admin') {
                $errors[] = 'Bạn không có quyền thay đổi vai trò';
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ' . BASE_URL_ADMIN . '&action=users/edit&id=' . $id);
            exit;
        }

        // Update user
        $data = [
            'full_name' => trim($_POST['full_name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone'] ?? '')
        ];

        // Update password if provided
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }

        // Update role if admin
        if ($currentUserRole === 'admin' && isset($_POST['role'])) {
            $data['role'] = $_POST['role'];
        }

        if ($this->model->updateUser($id, $data)) {
            $_SESSION['success'] = 'Cập nhật người dùng thành công';
            header('Location: ' . BASE_URL_ADMIN . '&action=users');
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật người dùng';
            header('Location: ' . BASE_URL_ADMIN . '&action=users/edit&id=' . $id);
        }
        exit;
    }

    /**
     * Delete user
     */
    public function delete()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng']);
            exit;
        }

        $currentUserId = $_SESSION['user']['user_id'] ?? null;
        $currentUserRole = $_SESSION['user']['role'] ?? 'customer';

        $user = $this->model->getById($id);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng']);
            exit;
        }

        // Check permission using model
        if (!$this->model->canDelete($currentUserId, $currentUserRole, $user['user_id'], $user['role'])) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa người dùng này']);
            exit;
        }

        if ($this->model->deleteUser($id)) {
            echo json_encode(['success' => true, 'message' => 'Xóa người dùng thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa người dùng']);
        }
        exit;
    }

    /**
     * Show user detail
     */
    public function detail()
    {
        $id = $_GET['id'] ?? null;

        // Debug: Check what ID we received
        error_log("=== USER DETAIL DEBUG ===");
        error_log("Received ID from GET: " . var_export($id, true));
        error_log("Full GET params: " . var_export($_GET, true));

        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy người dùng';
            header('Location: ' . BASE_URL_ADMIN . '&action=users');
            exit;
        }

        $user = $this->model->getById($id);

        // Debug: Check what user was retrieved
        error_log("Retrieved user: " . var_export($user, true));

        if (!$user) {
            $_SESSION['error'] = 'Không tìm thấy người dùng';
            header('Location: ' . BASE_URL_ADMIN . '&action=users');
            exit;
        }

        $currentUserRole = $_SESSION['user']['role'] ?? 'customer';

        // Only allow viewing customer users (admin and guide users are managed separately)
        if ($user['role'] !== 'customer') {
            $_SESSION['error'] = 'Không thể xem thông tin user này. Vui lòng sử dụng trang quản lý tương ứng.';
            header('Location: ' . BASE_URL_ADMIN . '&action=users');
            exit;
        }

        require_once PATH_VIEW_ADMIN . 'pages/users/detail.php';
    }
}
