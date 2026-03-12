<?php
class DashboardController
{
    protected $model;
    protected $tourModel;
    protected $bookingModel;
    protected $userModel;
    protected $transactionModel;
    protected $guideModel;

    public function __construct()
    {
        $this->model = new Booking();
        $this->tourModel = new Tour();
        $this->bookingModel = new Booking();
        $this->userModel = new UserModel();
        $this->transactionModel = new Transaction();
        $this->guideModel = new Guide();
    }

    public function index()
    {
        // Kiểm tra quyền: chỉ admin và hdv được truy cập
        check_role(['admin', 'guide']);

        // Redirect guide về trang schedule
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        if ($userRole === 'guide') {
            header('Location: ' . BASE_URL_ADMIN . '&action=guide/schedule');
            exit;
        }

        $currentMonth = date('m');
        $currentYear = date('Y');
        $today = date('Y-m-d');
        $lastMonth = date('m', strtotime('first day of last month'));
        $lastMonthYear = date('Y', strtotime('first day of last month'));

        // Lấy dữ liệu tổng quan
        $monthlyRevenue = $this->bookingModel->getMonthlyRevenue($currentMonth, $currentYear);
        $lastMonthRevenue = $this->bookingModel->getMonthlyRevenue($lastMonth, $lastMonthYear);
        $lastMonthName = $this->getMonthName($lastMonth);
        $currentMonthName = $this->getMonthName($currentMonth);
        $newBookings = $this->bookingModel->getNewBookingsThisMonth($currentMonth, $currentYear);
        $ongoingTours = $this->tourModel->getOngoingTours();
        $newCustomers = $this->userModel->getNewCustomersThisMonth($currentMonth, $currentYear);

        $data = [
            // Thống kê chính
            'monthlyRevenue' => $monthlyRevenue,
            'lastMonthRevenue' => $lastMonthRevenue,
            'lastMonthName' => $lastMonthName,
            'currentMonthName' => $currentMonthName,
            'ongoingTours' => $ongoingTours,
            'newBookings' => $newBookings,
            'newCustomers' => $newCustomers,
            'totalGuides' => $this->guideModel->getTotalActiveGuides(),

            // Dữ liệu biểu đồ
            'revenueData' => $this->getRevenueLast12Months(),
            'bookingStatusData' => $this->bookingModel->getBookingStatusStats(),
            'topTours' => $this->tourModel->getTopToursByRevenue(5),
            'tourCategories' => $this->tourModel->getTourCategoriesStats(),

            // Danh sách
            'pendingBookings' => $this->bookingModel->getRecentPendingBookings(5),
            'upcomingTours' => $this->tourModel->getUpcomingTours(5),
            'recentTransactions' => $this->transactionModel->getRecentTransactions(5),
            'availableGuides' => $this->guideModel->getAvailableGuides() ?? [],

            // Thông tin thời gian
            'today' => $today
        ];

        // Load view
        require_once PATH_VIEW_ADMIN . 'default/header.php';
        require_once PATH_VIEW_ADMIN . 'default/sidebar.php';
        require_once PATH_VIEW_ADMIN . 'dashboard.php';
        require_once PATH_VIEW_ADMIN . 'default/footer.php';
    }

    private function getRevenueLast12Months()
    {
        $revenueData = [];
        $year = 2025; // Cố định năm 2025

        for ($month = 1; $month <= 12; $month++) {
            $revenue = $this->bookingModel->getMonthlyRevenue($month, $year);
            $revenueData[] = [
                'month' => $this->getMonthName($month) . ' ' . $year,
                'revenue' => $revenue
            ];
        }

        return $revenueData;
    }

    private function getMonthName($monthNumber)
    {
        $months = [
            1 => 'Tháng 1',
            'Tháng 2',
            'Tháng 3',
            'Tháng 4',
            'Tháng 5',
            'Tháng 6',
            'Tháng 7',
            'Tháng 8',
            'Tháng 9',
            'Tháng 10',
            'Tháng 11',
            'Tháng 12'
        ];
        return $months[(int)$monthNumber] ?? '';
    }

    // API endpoint cho dữ liệu biểu đồ
    public function getChartData()
    {
        header('Content-Type: application/json');

        $data = [
            'revenueData' => $this->getRevenueLast12Months(),
            'bookingStatus' => $this->bookingModel->getBookingStatusStats(),
            'tourCategories' => $this->tourModel->getTourCategoriesStats()
        ];

        echo json_encode($data);
        exit;
    }
}
