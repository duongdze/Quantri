<?php

if (!function_exists('debug')) {
    function debug($data)
    {
        echo '<pre>';
        print_r($data);
        die;
    }
}

if (!function_exists('upload_file')) {
    function upload_file($folder, $file)
    {
        $targetFile = $folder . '/' . time() . '-' . $file["name"];

        if (move_uploaded_file($file["tmp_name"], PATH_ASSETS_UPLOADS . $targetFile)) {
            return $targetFile;
        }

        throw new Exception('Upload file không thành công!');
    }
}

if (!function_exists('auth_check')) {
    function auth_check()
    {
        return isset($_SESSION['user']);
    }
}

if (!function_exists('is_admin')) {
    function is_admin()
    {
        return auth_check() && $_SESSION['user']['role'] === 'admin';
    }
}

if (!function_exists('is_hdv')) {
    function is_hdv()
    {
        return auth_check() && $_SESSION['user']['role'] === 'guide';
    }
}

if (!function_exists('check_role')) {
    /**
     * Kiểm tra quyền truy cập theo danh sách role
     * Nếu chưa đăng nhập sẽ chuyển tới trang login
     * Nếu đã đăng nhập nhưng không có role phù hợp sẽ gửi 403
     *
     * @param array $roles
     * @return void
     */
    function check_role(array $roles = [])
    {
        // Nếu chưa đăng nhập -> chuyển tới login
        if (!auth_check()) {
            header('Location: ' . BASE_URL_ADMIN . '&action=login');
            exit;
        }

        // Nếu role không có trong danh sách cho phép -> 403
        $userRole = $_SESSION['user']['role'] ?? null;
        if (!in_array($userRole, $roles)) {
            header('HTTP/1.1 403 Forbidden');
            echo '<h1>403 Forbidden</h1><p>Bạn không có quyền truy cập trang này.</p>';
            exit;
        }

        // Trả về true khi được phép
        return true;
    }
}

if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
    }
}
if (!function_exists('send_mail_log')) {
    function send_mail_log($to, $subject, $content) {
        $logDir = PATH_ASSETS_UPLOADS . 'emails';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/mail_log.txt';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] TO: $to | SUBJECT: $subject\n";
        $logEntry .= "CONTENT:\n$content\n";
        $logEntry .= str_repeat('-', 50) . "\n";
        
        return file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
