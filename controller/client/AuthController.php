<?php

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Hiển thị form đăng nhập
     */
    public function showLogin()
    {
        if (!empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        $error   = $_SESSION['error']   ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['error'], $_SESSION['success']);

        require_once PATH_VIEW_CLIENT . 'auth/login.php';
    }

    /**
     * Xử lý POST đăng nhập
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Lỗi bảo mật: CSRF token không hợp lệ!';
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ email và mật khẩu.';
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        $user = $this->userModel->getByEmail($email);

        if (!$user) {
            $_SESSION['error'] = 'Email không tồn tại trong hệ thống.';
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        // Kiểm tra tài khoản bị khóa
        if (!empty($user['lock_until']) && strtotime($user['lock_until']) > time()) {
            $timeLeft = ceil((strtotime($user['lock_until']) - time()) / 60);
            $_SESSION['error'] = "Tài khoản đang bị tạm khóa do nhập sai mật khẩu nhiều lần. Vui lòng thử lại sau $timeLeft phút.";
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        // Kiểm tra mật khẩu (hỗ trợ cả hash và plain text cũ)
        $passwordOk = false;
        if (!empty($user['password_hash'])) {
            $passwordOk = password_verify($password, $user['password_hash']);
        }
        if (!$passwordOk && isset($user['password'])) {
            $passwordOk = ($password === $user['password']);
        }

        if (!$passwordOk) {
            $attempts = ($user['login_attempts'] ?? 0) + 1;
            $updateData = ['login_attempts' => $attempts];
            
            if ($attempts >= 5) {
                $updateData['lock_until'] = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $_SESSION['error'] = 'Bạn đã nhập sai 5 lần. Tài khoản bị khóa trong 15 phút.';
            } else {
                $_SESSION['error'] = 'Mật khẩu không đúng. Bạn còn ' . (5 - $attempts) . ' lần thử.';
            }

            $this->userModel->update($updateData, 'user_id = :uid', ['uid' => $user['user_id']]);
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        // Đăng nhập thành công -> Reset số lần sai
        if (($user['login_attempts'] ?? 0) > 0 || !empty($user['lock_until'])) {
            $this->userModel->update(
                ['login_attempts' => 0, 'lock_until' => null],
                'user_id = :uid',
                ['uid' => $user['user_id']]
            );
        }

        // Lưu session
        $_SESSION['user'] = [
            'user_id'   => $user['user_id'],
            'full_name' => $user['full_name'],
            'email'     => $user['email'],
            'phone'     => $user['phone'] ?? '',
            'address'   => $user['address'] ?? '',
            'avatar'    => $user['avatar'] ?? '',
            'role'      => $user['role'],
        ];

        $_SESSION['success'] = 'Đăng nhập thành công! Chào mừng ' . $user['full_name'];

        // Redirect theo role
        if (in_array($user['role'], ['admin', 'guide'])) {
            header('Location: ' . BASE_URL_ADMIN);
        } else {
            header('Location: ' . BASE_URL);
        }
        exit;
    }

    /**
     * Hiển thị form đăng ký
     */
    public function showRegister()
    {
        if (!empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        require_once PATH_VIEW_CLIENT . 'auth/register.php';
    }

    /**
     * Xử lý POST đăng ký
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?action=register');
            exit;
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Lỗi bảo mật: CSRF token không hợp lệ!';
            header('Location: ' . BASE_URL . '?action=register');
            exit;
        }

        $fullName        = trim($_POST['full_name']        ?? '');
        $email           = trim($_POST['email']            ?? '');
        $phone           = trim($_POST['phone']            ?? '');
        $password        = trim($_POST['password']         ?? '');
        $passwordConfirm = trim($_POST['password_confirm'] ?? '');

        // Validate
        $errors = [];
        if (empty($fullName))  $errors[] = 'Vui lòng nhập họ tên.';
        if (empty($email))     $errors[] = 'Vui lòng nhập email.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
        if (empty($password))  $errors[] = 'Vui lòng nhập mật khẩu.';
        elseif (strlen($password) < 6) $errors[] = 'Mật khẩu phải ít nhất 6 ký tự.';
        if ($password !== $passwordConfirm) $errors[] = 'Xác nhận mật khẩu không khớp.';

        if (!empty($errors)) {
            $_SESSION['error'] = implode(' ', $errors);
            header('Location: ' . BASE_URL . '?action=register');
            exit;
        }

        // Kiểm tra email đã tồn tại
        if ($this->userModel->emailExists($email)) {
            $_SESSION['error'] = 'Email này đã được đăng ký.';
            header('Location: ' . BASE_URL . '?action=register');
            exit;
        }

        // Tạo user mới
        $this->userModel->create([
            'full_name' => $fullName,
            'email'     => $email,
            'phone'     => $phone,
            'password'  => $password,
            'role'      => 'customer',
        ]);

        $_SESSION['success'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
        header('Location: ' . BASE_URL . '?action=login');
        exit;
    }

    /**
     * Đăng xuất
     */
    public function logout()
    {
        unset($_SESSION['user']);
        session_destroy();
        header('Location: ' . BASE_URL);
        exit;
    }

    /**
     * Trang tài khoản của tôi
     */
    public function myAccount()
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }
        $error   = $_SESSION['error']   ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['error'], $_SESSION['success']);

        // Lấy thông tin đầy đủ từ DB
        $user = $this->userModel->getById($_SESSION['user']['user_id']);

        require_once PATH_VIEW_CLIENT . 'pages/account/index.php';
    }

    /**
     * Cập nhật thông tin tài khoản
     */
    public function updateAccount()
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?action=my-account');
            exit;
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Lỗi bảo mật: CSRF token không hợp lệ!';
            header('Location: ' . BASE_URL . '?action=my-account');
            exit;
        }

        $userId   = $_SESSION['user']['user_id'];
        $fullName = trim($_POST['full_name'] ?? '');
        $phone    = trim($_POST['phone']     ?? '');
        $address  = trim($_POST['address']   ?? '');

        if (empty($fullName)) {
            $_SESSION['error'] = 'Họ tên không được để trống.';
            header('Location: ' . BASE_URL . '?action=my-account');
            exit;
        }

        $this->userModel->update(
            ['full_name' => $fullName, 'phone' => $phone, 'address' => $address],
            'user_id = :uid',
            ['uid' => $userId]
        );

        // Cập nhật session
        $_SESSION['user']['full_name'] = $fullName;
        $_SESSION['user']['phone']     = $phone;
        $_SESSION['user']['address']   = $address;

        $_SESSION['success'] = 'Cập nhật thông tin thành công!';
        header('Location: ' . BASE_URL . '?action=my-account');
        exit;
    }

    /**
     * Đổi mật khẩu (client)
     */
    public function changePassword()
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?action=my-account');
            exit;
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Lỗi bảo mật: CSRF token không hợp lệ!';
            header('Location: ' . BASE_URL . '?action=my-account#password');
            exit;
        }

        $userId  = $_SESSION['user']['user_id'];
        $current = trim($_POST['current_password'] ?? '');
        $new     = trim($_POST['new_password']     ?? '');
        $confirm = trim($_POST['confirm_password'] ?? '');

        $user = $this->userModel->getById($userId);

        // Kiểm tra mật khẩu hiện tại
        $ok = false;
        if (!empty($user['password_hash'])) {
            $ok = password_verify($current, $user['password_hash']);
        }
        if (!$ok && isset($user['password'])) {
            $ok = ($current === $user['password']);
        }

        if (!$ok) {
            $_SESSION['error'] = 'Mật khẩu hiện tại không đúng.';
            header('Location: ' . BASE_URL . '?action=my-account#password');
            exit;
        }
        if (strlen($new) < 6) {
            $_SESSION['error'] = 'Mật khẩu mới phải ít nhất 6 ký tự.';
            header('Location: ' . BASE_URL . '?action=my-account#password');
            exit;
        }
        if ($new !== $confirm) {
            $_SESSION['error'] = 'Xác nhận mật khẩu không khớp.';
            header('Location: ' . BASE_URL . '?action=my-account#password');
            exit;
        }

        $this->userModel->update(
            ['password_hash' => password_hash($new, PASSWORD_DEFAULT)],
            'user_id = :uid',
            ['uid' => $userId]
        );

        $_SESSION['success'] = 'Đổi mật khẩu thành công!';
        header('Location: ' . BASE_URL . '?action=my-account');
        exit;
    }

    /**
     * Quên mật khẩu – hiển thị form
     */
    public function showForgotPassword()
    {
        if (!empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        $error   = $_SESSION['error']   ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['error'], $_SESSION['success']);
        require_once PATH_VIEW_CLIENT . 'auth/forgot-password.php';
    }

    /**
     * Quên mật khẩu – xử lý POST
     */
    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?action=forgot-password');
            exit;
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Lỗi bảo mật: CSRF token không hợp lệ!';
            header('Location: ' . BASE_URL . '?action=forgot-password');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        if (empty($email)) {
            $_SESSION['error'] = 'Vui lòng nhập email.';
            header('Location: ' . BASE_URL . '?action=forgot-password');
            exit;
        }

        $user = $this->userModel->getByEmail($email);
        if (!$user) {
            $_SESSION['success'] = 'Nếu email tồn tại, link đặt lại mật khẩu sẽ được gửi. Vui lòng kiểm tra hộp thư.';
            header('Location: ' . BASE_URL . '?action=forgot-password');
            exit;
        }

        $token = bin2hex(random_bytes(32));
        $expires = time() + 3600; // 1 giờ

        $_SESSION['reset_tokens'][$token] = [
            'user_id' => $user['user_id'],
            'expires' => $expires,
        ];

        $resetLink = BASE_URL . '?action=reset-password&token=' . $token;

        // Gửi email thật qua PHPMailer
        require_once PATH_ROOT . 'services/MailService.php';
        $sent = MailService::sendPasswordReset($user, $resetLink);

        if ($sent) {
            $_SESSION['success'] = 'Link đặt lại mật khẩu đã được gửi đến <strong>' . htmlspecialchars($user['email']) . '</strong>. Vui lòng kiểm tra hộp thư (kể cả thư mục Spam).';
        } else {
            // Fallback khi MAIL_ENABLED=false hoặc gửi lỗi: hiển thị link trực tiếp (chỉ dùng khi dev)
            $_SESSION['success'] = 'Link đặt lại mật khẩu của bạn: <a href="' . $resetLink . '">' . $resetLink . '</a>';
        }

        header('Location: ' . BASE_URL . '?action=forgot-password');
        exit;
    }


    /**
     * Đặt lại mật khẩu – hiển thị form
     */
    public function showResetPassword()
    {
        $token = $_GET['token'] ?? '';
        $valid = false;
        $error = null;

        if (!empty($token) && isset($_SESSION['reset_tokens'][$token])) {
            $data = $_SESSION['reset_tokens'][$token];
            if ($data['expires'] > time()) {
                $valid = true;
            } else {
                $error = 'Link đặt lại mật khẩu đã hết hạn.';
                unset($_SESSION['reset_tokens'][$token]);
            }
        } else {
            $error = 'Link không hợp lệ hoặc đã được sử dụng.';
        }

        require_once PATH_VIEW_CLIENT . 'auth/reset-password.php';
    }

    /**
     * Đặt lại mật khẩu – xử lý POST
     */
    public function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?action=forgot-password');
            exit;
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Lỗi bảo mật: CSRF token không hợp lệ!';
            header('Location: ' . BASE_URL . '?action=reset-password');
            exit;
        }

        $token   = $_POST['token']            ?? '';
        $new     = trim($_POST['new_password']     ?? '');
        $confirm = trim($_POST['confirm_password'] ?? '');

        if (empty($token) || !isset($_SESSION['reset_tokens'][$token])) {
            $_SESSION['error'] = 'Link không hợp lệ.';
            header('Location: ' . BASE_URL . '?action=forgot-password');
            exit;
        }

        $data = $_SESSION['reset_tokens'][$token];
        if ($data['expires'] <= time()) {
            unset($_SESSION['reset_tokens'][$token]);
            $_SESSION['error'] = 'Link đã hết hạn. Vui lòng yêu cầu lại.';
            header('Location: ' . BASE_URL . '?action=forgot-password');
            exit;
        }

        if (strlen($new) < 6) {
            $_SESSION['error'] = 'Mật khẩu phải ít nhất 6 ký tự.';
            header('Location: ' . BASE_URL . '?action=reset-password&token=' . $token);
            exit;
        }
        if ($new !== $confirm) {
            $_SESSION['error'] = 'Xác nhận mật khẩu không khớp.';
            header('Location: ' . BASE_URL . '?action=reset-password&token=' . $token);
            exit;
        }

        $this->userModel->update(
            ['password_hash' => password_hash($new, PASSWORD_DEFAULT)],
            'user_id = :uid',
            ['uid' => $data['user_id']]
        );

        unset($_SESSION['reset_tokens'][$token]);
        $_SESSION['success'] = 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập.';
        header('Location: ' . BASE_URL . '?action=login');
        exit;
    }
}
