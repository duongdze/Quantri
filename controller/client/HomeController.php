<?php
require_once 'models/Tour.php';
require_once 'models/TourCategory.php';
require_once 'models/Booking.php';

class HomeController
{
    public function index()
    {
        $tourModel = new Tour();
        $categoryModel = new TourCategory();

        // Lấy tour nổi bật (featured = 1, active)
        $featuredTours = $tourModel->getFeaturedTours(6);

        // Lấy tour mới nhất (nếu không đủ featured)
        if (count($featuredTours) < 3) {
            $featuredTours = $tourModel->getActiveTours(6);
        }

        // Danh mục + số lượng tour
        $categories = $categoryModel->getAllCategories();

        // Thống kê nhanh
        try {
            $pdo = BaseModel::getPdo();
            $statsRow = $pdo->query("
                SELECT 
                    (SELECT COUNT(*) FROM tours WHERE status = 'active') as total_tours,
                    (SELECT COUNT(*) FROM bookings) as total_bookings,
                    (SELECT COUNT(*) FROM users WHERE role = 'customer') as total_customers,
                    (SELECT COUNT(DISTINCT tour_id) FROM tour_departures WHERE departure_date >= CURDATE()) as active_departures
            ")->fetch();
        } catch (Exception $e) {
            $statsRow = [
                'total_tours' => 0,
                'total_bookings' => 0,
                'total_customers' => 0,
                'active_departures' => 0,
            ];
        }

        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['success']);

        require_once PATH_VIEW_CLIENT . 'pages/home.php';
    }
}
