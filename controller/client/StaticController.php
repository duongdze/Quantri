<?php

class StaticController
{
    public function contact()
    {
        $pageTitle = 'Liên hệ – VietTour';
        $error   = $_SESSION['error']   ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['error'], $_SESSION['success']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Lỗi bảo mật: CSRF token không hợp lệ!';
                header('Location: ' . BASE_URL . '?action=contact');
                exit;
            }
            // Lưu form liên hệ vào session (không có mail server)
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if ($name && $email && $message) {
                // Trong thực tế sẽ gửi email ở đây
                $_SESSION['success'] = 'Cảm ơn bạn! Chúng tôi sẽ liên hệ lại trong 24 giờ.';
            } else {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin.';
            }
            header('Location: ' . BASE_URL . '?action=contact');
            exit;
        }

        require_once PATH_VIEW_CLIENT . 'pages/contact.php';
    }

    public function about()
    {
        $pageTitle = 'Về chúng tôi – VietTour';
        require_once PATH_VIEW_CLIENT . 'pages/about.php';
    }

    public function guide()
    {
        $pageTitle = 'Hướng dẫn đặt tour – VietTour';
        require_once PATH_VIEW_CLIENT . 'pages/guide.php';
    }

    public function faq()
    {
        $pageTitle = 'Câu hỏi thường gặp – VietTour';
        require_once PATH_VIEW_CLIENT . 'pages/faq.php';
    }

    public function refundPolicy()
    {
        $pageTitle = 'Chính sách hoàn tiền – VietTour';
        require_once PATH_VIEW_CLIENT . 'pages/refund-policy.php';
    }
}
