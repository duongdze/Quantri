<?php
class AuthorController
{
    private $user;

    public function __construct()
    {
        $this->user = new UserModel();
    }
    public function login()
    {
        require_once PATH_VIEW_ADMIN . 'auth/login.php';
    }

    public function loginProcess()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Thử đăng nhập bằng phương thức an toàn (với password_verify)
            $user = $this->user->checkLogin($email, $password);

            if ($user) {
                // Đăng nhập thành công với mật khẩu đã được băm
                $_SESSION['user'] = $user;
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_id'] = $user['user_id'];
                // nếu user là HDV, ghép guide_id vào session
                if (!empty($user['role']) && $user['role'] === 'guide') {
                    require_once PATH_MODEL . 'Guide.php';
                    $guideModel = new Guide();
                    $guideRow = $guideModel->find('*', 'user_id = :uid', ['uid' => $user['user_id']]);
                    if ($guideRow) {
                        $_SESSION['guide_id'] = $guideRow['id'];
                    }
                }
                header('Location: ' . BASE_URL_ADMIN); // Chuyển hướng về dashboard
                exit;
            }

            // Nếu đăng nhập an toàn thất bại, kiểm tra trường hợp mật khẩu cũ (văn bản thuần)
            // và tự động cập nhật nếu khớp. Đây là cơ chế "tự sửa lỗi".
            $userData = $this->user->find('*', 'email = :email AND role = :role', [
                'email' => $email,
                'role' => 'admin'
            ]);

            // So sánh mật khẩu văn bản thuần
            if ($userData && $userData['password_hash'] === $password) {
                // Mật khẩu cũ đúng. Mã hóa và cập nhật lại vào DB.
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $this->user->update(['password_hash' => $newHash], 'user_id = :id', ['id' => $userData['user_id']]);

                // Đăng nhập cho người dùng
                $userData['password_hash'] = $newHash; // Cập nhật lại hash trong session
                $_SESSION['user'] = $userData;
                // nếu user là HDV, ghép guide_id vào session
                if (!empty($userData['role']) && $userData['role'] === 'guide') {
                    require_once PATH_MODEL . 'Guide.php';
                    $guideModel = new Guide();
                    $guideRow = $guideModel->find('*', 'user_id = :uid', ['uid' => $userData['user_id']]);
                    if ($guideRow) {
                        $_SESSION['guide_id'] = $guideRow['id'];
                    }
                }
                header('Location: ' . BASE_URL_ADMIN);
                exit;
            }

            // Nếu cả hai cách đều thất bại, báo lỗi.
            $error = "Thông tin đăng nhập không hợp lệ";
            require_once PATH_VIEW_ADMIN . 'auth/login.php';
        }
    }
    public function logout()
    {
        // Hủy session một cách an toàn
        unset($_SESSION['user']);
        unset($_SESSION['guide_id']);
        session_destroy();
        // Chuyển hướng thẳng về trang login
        header('location: ' . BASE_URL_ADMIN . '&action=login');
        exit;
    }

    public function accountInfo()
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            header('Location: ' . BASE_URL_ADMIN . '&action=login');
            exit;
        }

        require_once PATH_VIEW_ADMIN . 'pages/account/index.php';
    }

    /**
     * Cập nhật thông tin profile
     */
    public function updateProfile()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $userId = $_SESSION['user']['user_id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        // Validation
        if (empty($fullName)) {
            echo json_encode(['success' => false, 'message' => 'Tên không được để trống']);
            exit;
        }

        try {
            $result = $this->user->update([
                'full_name' => $fullName,
                'phone' => $phone
            ], 'user_id = :id', ['id' => $userId]);

            if ($result) {
                // Cập nhật session
                $_SESSION['user']['full_name'] = $fullName;
                $_SESSION['user']['phone'] = $phone;

                echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể cập nhật thông tin']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Đổi mật khẩu
     */
    public function changePassword()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $userId = $_SESSION['user']['user_id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu mới không khớp']);
            exit;
        }

        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự']);
            exit;
        }

        try {
            // Lấy thông tin user
            $user = $this->user->find('*', 'user_id = :id', ['id' => $userId]);

            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy user']);
                exit;
            }

            // Verify mật khẩu hiện tại
            if (!password_verify($currentPassword, $user['password_hash'])) {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng']);
                exit;
            }

            // Hash mật khẩu mới
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

            // Cập nhật
            $result = $this->user->update([
                'password_hash' => $newHash
            ], 'user_id = :id', ['id' => $userId]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể đổi mật khẩu']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Cập nhật ảnh đại diện
     */
    public function updateAvatar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $userId = $_SESSION['user']['user_id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit;
        }

        // Check if file was uploaded
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Không có file được upload']);
            exit;
        }

        $file = $_FILES['avatar'];

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)']);
            exit;
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Kích thước ảnh tối đa 5MB']);
            exit;
        }

        try {
            // Create uploads directory if not exists
            $uploadDir = PATH_ASSETS_UPLOADS . 'avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                echo json_encode(['success' => false, 'message' => 'Không thể lưu file']);
                exit;
            }

            // Delete old avatar if exists
            $user = $this->user->find('*', 'user_id = :id', ['id' => $userId]);
            if ($user && !empty($user['avatar'])) {
                $oldFile = PATH_ASSETS_UPLOADS . $user['avatar'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            // Update database
            $result = $this->user->update([
                'avatar' => 'avatars/' . $filename
            ], 'user_id = :id', ['id' => $userId]);

            if ($result) {
                // Update session
                $_SESSION['user']['avatar'] = 'avatars/' . $filename;
                echo json_encode(['success' => true, 'message' => 'Cập nhật ảnh đại diện thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể cập nhật database']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }
}
