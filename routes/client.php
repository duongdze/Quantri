<?php
$action = $_GET['action'] ?? '/';

switch ($action) {
    // ─── Trang chủ ───
    case '/':
    case 'home':
        require_once 'controller/client/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;

    // ─── Danh sách & chi tiết tour ───
    case 'tour-list':
        require_once 'controller/client/TourController.php';
        $controller = new ClientTourController();
        $controller->index();
        break;

    case 'tour-detail':
        require_once 'controller/client/TourController.php';
        $controller = new ClientTourController();
        $controller->detail();
        break;

    // ─── Đặt tour ───
    case 'booking-create':
        require_once 'controller/client/BookingController.php';
        $controller = new ClientBookingController();
        $controller->create();
        break;

    case 'booking-store':
        require_once 'controller/client/BookingController.php';
        $controller = new ClientBookingController();
        $controller->store();
        break;

    case 'booking-payment':
        require_once 'controller/client/BookingController.php';
        $controller = new ClientBookingController();
        $controller->payment();
        break;

    case 'booking-success':
        require_once 'controller/client/BookingController.php';
        $controller = new ClientBookingController();
        $controller->success();
        break;

    // ─── Đơn hàng của tôi ───
    case 'my-bookings':
        require_once 'controller/client/BookingController.php';
        $controller = new ClientBookingController();
        $controller->myBookings();
        break;

    case 'booking-detail':
        require_once 'controller/client/BookingController.php';
        $controller = new ClientBookingController();
        $controller->myBookingDetail();
        break;

    // ─── Auth ───
    case 'login':
        require_once 'controller/client/AuthController.php';
        $controller = new AuthController();
        $controller->showLogin();
        break;

    case 'login-post':
        require_once 'controller/client/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;

    case 'register':
        require_once 'controller/client/AuthController.php';
        $controller = new AuthController();
        $controller->showRegister();
        break;

    case 'register-post':
        require_once 'controller/client/AuthController.php';
        $controller = new AuthController();
        $controller->register();
        break;

    case 'logout':
        require_once 'controller/client/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    // ─── Tài khoản ───
    case 'my-account':
        require_once 'controller/client/AuthController.php';
        $controller = new AuthController();
        $controller->myAccount();
        break;

    case 'my-account-update':
        require_once 'controller/client/AuthController.php';
        $controller = new AuthController();
        $controller->updateAccount();
        break;

    case 'my-account-password':
        require_once 'controller/client/AuthController.php';
        $controller = new AuthController();
        $controller->changePassword();
        break;

    // ─── Quên mật khẩu ───
    case 'forgot-password':
        require_once 'controller/client/AuthController.php';
        $controller = new AuthController();
        $controller->showForgotPassword();
        break;

    case 'forgot-password-post':
        require_once 'controller/client/AuthController.php';
        $controller = new AuthController();
        $controller->forgotPassword();
        break;

    case 'reset-password':
        require_once 'controller/client/AuthController.php';
        $controller = new AuthController();
        $controller->showResetPassword();
        break;

    case 'reset-password-post':
        require_once 'controller/client/AuthController.php';
        $controller = new AuthController();
        $controller->resetPassword();
        break;

    // ─── Trang tĩnh ───
    case 'contact':
        require_once 'controller/client/StaticController.php';
        (new StaticController())->contact();
        break;

    case 'about':
        require_once 'controller/client/StaticController.php';
        (new StaticController())->about();
        break;

    case 'guide-booking':
        require_once 'controller/client/StaticController.php';
        (new StaticController())->guide();
        break;

    case 'faq':
        require_once 'controller/client/StaticController.php';
        (new StaticController())->faq();
        break;

    case 'refund-policy':
        require_once 'controller/client/StaticController.php';
        (new StaticController())->refundPolicy();
        break;

    // ─── 404 ───
    default:
        http_response_code(404);
        $pageTitle = '404 – Không tìm thấy trang';
        require_once 'configs/env.php';
        echo "<!DOCTYPE html><html lang='vi'><head><meta charset='UTF-8'><title>404</title>
              <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
              </head><body class='bg-light'>
              <div style='text-align:center;padding:10vh 20px;font-family:Inter,sans-serif'>
                <h1 style='font-size:5rem;color:#1e6fff;margin:0;font-weight:800'>404</h1>
                <p style='color:#6b7280;font-size:1.1rem;margin:16px 0'>Trang bạn tìm không tồn tại.</p>
                <a href='" . BASE_URL . "' style='display:inline-block;background:#1e6fff;color:#fff;padding:12px 28px;border-radius:50px;text-decoration:none;font-weight:600'>← Về trang chủ</a>
              </div></body></html>";
        break;
}
