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

        // Kiểm tra mật khẩu (hỗ trợ cả hash và plain text cũ)
        $passwordOk = false;
        if (!empty($user['password_hash'])) {
            $passwordOk = password_verify($password, $user['password_hash']);
        }
        if (!$passwordOk && isset($user['password'])) {
            $passwordOk = ($password === $user['password']);
        }

        if (!$passwordOk) {
            $_SESSION['error'] = 'Mật khẩu không đúng.';
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        // Lưu session
        $_SESSION['user'] = [
            'user_id'   => $user['user_id'],
            'full_name' => $user['full_name'],
            'email'     => $user['email'],
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
}
