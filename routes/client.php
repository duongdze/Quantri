<?php
$action = $_GET['action'] ?? '/';

switch ($action) {
    case '/':
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
        
    default:
        http_response_code(404);
        echo "<div style='text-align:center;padding:100px 20px;font-family:Inter,sans-serif'>
                <h1 style='font-size:5rem;color:#1e6fff;margin:0'>404</h1>
                <p style='color:#6b7280'>Trang bạn tìm không tồn tại.</p>
                <a href='" . BASE_URL . "' style='color:#1e6fff;font-weight:600'>← Về trang chủ</a>
              </div>";
        break;
}
