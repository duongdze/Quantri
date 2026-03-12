<?php
$action = $_GET['action'] ?? '/';

switch ($action) {
    case '/':
        // Homepage placeholder
        echo "<h1>Trang chủ (Đang cập nhật)</h1>";
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
        
    default:
        // 404 placeholder
        echo "<h1>404 Not Found</h1>";
        break;
}
