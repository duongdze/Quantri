<?php
require_once 'services/ExportService.php';
require_once 'services/FilterService.php';
require_once 'models/SupplierCost.php';
require_once 'models/ConversionReport.php';

class ReportController
{
    protected $financialReport;
    protected $tourModel;
    protected $bookingModel;
    protected $conversionReport;
    protected $exportService;
    protected $filterService;
    private $pdo;

    public function __construct()
    {
        $this->financialReport = new FinancialReport();
        $this->tourModel = new Tour();
        $this->bookingModel = new Booking();
        $this->conversionReport = new ConversionReport();
        $this->exportService = new ExportService();
        $this->filterService = new FilterService();

        // Get database connection
        $this->pdo = BaseModel::getPdo();
    }

    public function index()
    {
        require_once PATH_VIEW_ADMIN . 'pages/reports/index.php';
    }

    public function dashboard()
    {
        // Lấy tham số lọc
        $period = $_GET['period'] ?? '30'; // Default 30 days

        // Tính ngày bắt đầu và kết thúc dựa trên period
        $dateRange = $this->getDateRangeFromPeriod($period);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        // Lấy dữ liệu tổng quan từ tất cả các báo cáo
        $dashboardData = [
            'financial' => $this->financialReport->getFinancialSummary($dateFrom, $dateTo),
            'bookings' => $this->bookingModel->getBookingStats($dateFrom, $dateTo),
            'conversion' => $this->conversionReport->getConversionRate($dateFrom, $dateTo),
            'feedback' => $this->getFeedbackStats($dateFrom, $dateTo),
            'tours' => $this->getTourStats(),
            'top_revenue_tours' => $this->financialReport->getTourFinancials($dateFrom, $dateTo),
            'top_rated_tours' => $this->getTopRatedTours($dateFrom, $dateTo, 10),
            'trend_data' => $this->getTrendData($dateFrom, $dateTo),
            'revenue_distribution' => $this->getRevenueDistribution($dateFrom, $dateTo),
            'conversion_by_source' => $this->conversionReport->getConversionBySource($dateFrom, $dateTo),
            'rating_distribution' => $this->getRatingDistribution($dateFrom, $dateTo),
            'recent_activities' => $this->getRecentActivities(),
            'alerts' => $this->getDashboardAlerts($dateFrom, $dateTo)
        ];

        // Tính toán growth metrics
        $dashboardData = $this->calculateGrowthMetrics($dashboardData, $dateFrom, $dateTo, $period);

        // Xử lý AJAX requests cho dynamic updates
        if (isset($_GET['dashboard_data'])) {
            header('Content-Type: application/json');
            echo json_encode($this->getDashboardData($_GET));
            exit;
        }

        // Xử lý export requests
        if (isset($_GET['export_dashboard'])) {
            $this->exportDashboard($_GET['format'], $_GET['period'], $dashboardData);
            return;
        }

        // Truyền dữ liệu sang view
        $data = [
            'dashboardData' => $dashboardData,
            'period' => $period,
            'dateRange' => $dateRange
        ];

        require_once PATH_VIEW_ADMIN . 'pages/reports/dashboard.php';
    }

    public function financial()
    {
        // Lấy tham số lọc nâng cao
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $tourId = $_GET['tour_id'] ?? null;
        $categoryId = $_GET['category_id'] ?? null;
        $priceMin = $_GET['price_min'] ?? null;
        $priceMax = $_GET['price_max'] ?? null;
        $durationMin = $_GET['duration_min'] ?? null;
        $durationMax = $_GET['duration_max'] ?? null;
        $departureLocation = $_GET['departure_location'] ?? null;
        $keyword = $_GET['keyword'] ?? null;
        $reportType = $_GET['report_type'] ?? 'summary';

        // Build filters array
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'tour_id' => $tourId,
            'category_id' => $categoryId,
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'duration_min' => $durationMin,
            'duration_max' => $durationMax,
            'departure_location' => $departureLocation,
            'keyword' => $keyword
        ];

        // Validate filters
        $filterErrors = $this->filterService->validateFilters($filters, 'financial');
        if (!empty($filterErrors)) {
            $_SESSION['filter_errors'] = $filterErrors;
        }

        // Lấy dữ liệu báo cáo tài chính với filters
        $financialData = $this->financialReport->getFinancialSummary($dateFrom, $dateTo, $filters);
        $tourFinancials = $this->financialReport->getTourFinancials($dateFrom, $dateTo, $filters);
        $monthlyData = $this->financialReport->getMonthlyData(date('Y'), $filters);

        // Chuẩn bị dữ liệu cho biểu đồ
        $monthlyLabels = array_column($monthlyData, 'month_name');
        $monthlyRevenue = array_column($monthlyData, 'revenue');
        $monthlyExpense = array_column($monthlyData, 'expense');
        $monthlyProfit = array_column($monthlyData, 'profit');

        // Dữ liệu cho biểu đồ tròn lợi nhuận theo tour
        $topTours = array_slice($tourFinancials, 0, 5); // Top 5 tours
        $tourNames = array_column($topTours, 'tour_name');
        $tourProfits = array_column($topTours, 'profit');

        // Lấy options cho filters
        $filterOptions = [
            'tours' => $this->filterService->getFilteredTours([]),
            'categories' => $this->filterService->getCategories(),
            'departureLocations' => $this->filterService->getDepartureLocations(),
            'priceRanges' => $this->filterService->getPriceRanges(),
            'durationRanges' => $this->filterService->getDurationRanges(),
            'datePresets' => $this->filterService->getDateRangePresets()
        ];

        // Xử lý export nếu có yêu cầu
        if (isset($_GET['export'])) {
            $this->exportFinancialReport($_GET['export'], $financialData, $tourFinancials);
            return;
        }

        // Truyền dữ liệu sang view
        $data = [
            'financialData' => $financialData,
            'tourFinancials' => $tourFinancials,
            'monthlyLabels' => $monthlyLabels,
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyExpense' => $monthlyExpense,
            'monthlyProfit' => $monthlyProfit,
            'tourNames' => $tourNames,
            'tourProfits' => $tourProfits,
            'filterOptions' => $filterOptions,
            'filters' => array_merge($filters, ['report_type' => $reportType]),
            'filterSummary' => $this->filterService->getFilterSummary($filters, 'financial')
        ];

        require_once PATH_VIEW_ADMIN . 'pages/reports/financial.php';
    }

    public function bookings()
    {
        // Lấy tham số lọc
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $tourId = $_GET['tour_id'] ?? null;
        $status = $_GET['status'] ?? null;
        $source = $_GET['source'] ?? null;
        $reportType = $_GET['report_type'] ?? 'summary';

        // Lấy dữ liệu thống kê booking
        $bookingStats = $this->bookingModel->getBookingStats($dateFrom, $dateTo, $tourId, $status, $source);
        $bookings = $this->bookingModel->getBookingReport($dateFrom, $dateTo, $tourId, $status, $source);
        $topTours = $this->bookingModel->getTopBookedTours($dateFrom, $dateTo, 10);
        $sourceAnalysis = $this->bookingModel->getSourceAnalysis($dateFrom, $dateTo);

        // Chuẩn bị dữ liệu cho biểu đồ
        $monthlyData = $this->bookingModel->getMonthlyBookingData(date('Y'), $tourId);
        $monthlyLabels = array_column($monthlyData, 'month_name');
        $monthlyBookings = array_column($monthlyData, 'total_bookings');
        $monthlySuccessfulBookings = array_column($monthlyData, 'successful_bookings');

        // Dữ liệu cho biểu đồ tròn theo nguồn
        $sourceNames = array_column($sourceAnalysis, 'source');
        $sourceCounts = array_column($sourceAnalysis, 'booking_count');

        // Lấy danh sách tours cho dropdown filter
        $tours = $this->tourModel->select('id, name', '1=1', [], 'name ASC');

        // Xử lý export nếu có yêu cầu
        if (isset($_GET['export'])) {
            $this->exportBookingReport($_GET['export'], $bookingStats, $bookings);
            return;
        }

        // Truyền dữ liệu sang view
        $data = [
            'bookingStats' => $bookingStats,
            'bookings' => $bookings,
            'topTours' => $topTours,
            'sourceAnalysis' => $sourceAnalysis,
            'monthlyLabels' => $monthlyLabels,
            'monthlyBookings' => $monthlyBookings,
            'monthlySuccessfulBookings' => $monthlySuccessfulBookings,
            'sourceNames' => $sourceNames,
            'sourceCounts' => $sourceCounts,
            'tours' => $tours,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'tour_id' => $tourId,
                'status' => $status,
                'source' => $source,
                'report_type' => $reportType
            ]
        ];

        require_once PATH_VIEW_ADMIN . 'pages/reports/bookings.php';
    }

    public function feedback()
    {
        // Lấy tham số lọc
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $tourId = $_GET['tour_id'] ?? null;
        $feedbackType = $_GET['feedback_type'] ?? null;
        $rating = $_GET['rating'] ?? null;
        $sentiment = $_GET['sentiment'] ?? null;

        // Lấy dữ liệu thống kê feedback
        $feedbackStats = $this->getFeedbackStats($dateFrom, $dateTo, $tourId, $feedbackType, $rating, $sentiment);
        $feedbacks = $this->getFeedbackReport($dateFrom, $dateTo, $tourId, $feedbackType, $rating, $sentiment);
        $topRatedTours = $this->getTopRatedTours($dateFrom, $dateTo, 10);
        $keywordAnalysis = $this->getKeywordAnalysis($dateFrom, $dateTo);

        // Chuẩn bị dữ liệu cho biểu đồ
        $ratingDistribution = $this->getRatingDistribution($dateFrom, $dateTo);
        $feedbackTypeData = $this->getFeedbackTypeDistribution($dateFrom, $dateTo);

        // Lấy danh sách tours cho dropdown filter
        $tours = $this->tourModel->select('id, name', '1=1', [], 'name ASC');

        // Xử lý export nếu có yêu cầu
        if (isset($_GET['export'])) {
            $this->exportFeedbackReport($_GET['export'], $feedbackStats, $feedbacks);
            return;
        }

        // Truyền dữ liệu sang view
        $data = [
            'feedbackStats' => $feedbackStats,
            'feedbacks' => $feedbacks,
            'topRatedTours' => $topRatedTours,
            'keywordAnalysis' => $keywordAnalysis,
            'ratingDistribution' => $ratingDistribution,
            'feedbackTypeLabels' => array_column($feedbackTypeData, 'type'),
            'feedbackTypeCounts' => array_column($feedbackTypeData, 'count'),
            'tours' => $tours,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'tour_id' => $tourId,
                'feedback_type' => $feedbackType,
                'rating' => $rating,
                'sentiment' => $sentiment
            ]
        ];

        require_once PATH_VIEW_ADMIN . 'pages/reports/feedback.php';
    }

    /**
     * Export báo cáo feedback
     */
    private function exportFeedbackReport($exportType, $feedbackStats, $feedbacks)
    {
        if ($exportType === 'excel') {
            // Export toàn bộ báo cáo feedback ra Excel
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="bao-cao-phan-hoi-' . date('Y-m-d') . '.xls"');

            echo "Báo cáo Phản hồi - " . date('d/m/Y') . "\n\n";
            echo "Tổng quan\n";
            echo "Đánh giá trung bình: " . number_format($feedbackStats['avg_rating'], 1) . "/5.0\n";
            echo "Tổng phản hồi: " . number_format($feedbackStats['total_feedbacks']) . "\n";
            echo "Phản hồi tích cực: " . number_format($feedbackStats['positive_feedbacks']) . "\n";
            echo "Phản hồi tiêu cực: " . number_format($feedbackStats['negative_feedbacks']) . "\n";
            echo "Tỷ lệ phản hồi: " . number_format($feedbackStats['feedback_rate'], 1) . "%\n\n";

            echo "Chi tiết Phản hồi\n";
            echo "Mã PH\tLoại\tTour/Đối tượng\tKhách hàng\tĐánh giá\tNội dung\tNgày\tTình cảm\n";

            foreach ($feedbacks as $feedback) {
                echo "#" . str_pad($feedback['id'], 6, '0', STR_PAD_LEFT) . "\t";
                echo ($feedback['feedback_type'] ?? 'tour') . "\t";
                echo ($feedback['target_name'] ?? 'N/A') . "\t";
                echo ($feedback['customer_name'] ?? 'N/A') . "\t";
                echo ($feedback['rating'] ?? 0) . "/5\t";
                echo substr($feedback['comment'] ?? '', 0, 50) . "\t";
                echo date('d/m/Y', strtotime($feedback['created_at'])) . "\t";
                echo ($feedback['sentiment'] ?? 'neutral') . "\n";
            }

            exit;
        }
    }

    /**
     * Lấy thống kê feedback
     */
    private function getFeedbackStats($dateFrom, $dateTo, $tourId = null, $feedbackType = null, $rating = null, $sentiment = null, $includeGrowth = true)
    {
        $whereConditions = ["F.created_at BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        if ($tourId) {
            $whereConditions[] = "F.tour_id = :tour_id";
            $params[':tour_id'] = $tourId;
        }

        if ($feedbackType) {
            $whereConditions[] = "F.feedback_type = :feedback_type";
            $params[':feedback_type'] = $feedbackType;
        }

        if ($rating) {
            $whereConditions[] = "F.rating = :rating";
            $params[':rating'] = $rating;
        }

        if ($sentiment) {
            $whereConditions[] = "F.sentiment = :sentiment";
            $params[':sentiment'] = $sentiment;
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    COUNT(F.id) as total_feedbacks,
                    AVG(F.rating) as avg_rating,
                    SUM(CASE WHEN F.rating >= 4 THEN 1 ELSE 0 END) as positive_feedbacks,
                    SUM(CASE WHEN F.rating <= 2 THEN 1 ELSE 0 END) as negative_feedbacks,
                    SUM(CASE WHEN F.rating = 3 THEN 1 ELSE 0 END) as neutral_feedbacks
                FROM tour_feedbacks F 
                $whereClause";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $stats = $stmt->fetch();
        } catch (PDOException $e) {
            $stats = [];
        }

        $totalFeedbacks = $stats['total_feedbacks'] ?? 0;
        $positiveFeedbacks = $stats['positive_feedbacks'] ?? 0;
        $negativeFeedbacks = $stats['negative_feedbacks'] ?? 0;

        // Tính tỷ lệ
        $positiveRate = $totalFeedbacks > 0 ? ($positiveFeedbacks / $totalFeedbacks) * 100 : 0;
        $negativeRate = $totalFeedbacks > 0 ? ($negativeFeedbacks / $totalFeedbacks) * 100 : 0;

        // Tính tỷ lệ phản hồi (so với tổng bookings)
        $totalBookings = $this->bookingModel->count(
            "booking_date BETWEEN :date_from AND :date_to",
            [':date_from' => $dateFrom, ':date_to' => $dateTo]
        );
        $feedbackRate = $totalBookings > 0 ? ($totalFeedbacks / $totalBookings) * 100 : 0;

        // Lấy dữ liệu kỳ trước để tính growth
        $ratingGrowth = 0;
        if ($includeGrowth) {
            $previousStats = $this->getPreviousFeedbackStats($dateFrom, $dateTo, $tourId, $feedbackType, $rating, $sentiment);
            $ratingGrowth = $this->calculateGrowth($stats['avg_rating'] ?? 0, $previousStats['avg_rating'] ?? 0);
        }

        return [
            'total_feedbacks' => $totalFeedbacks,
            'avg_rating' => $stats['avg_rating'] ?? 0,
            'positive_feedbacks' => $positiveFeedbacks,
            'negative_feedbacks' => $negativeFeedbacks,
            'neutral_feedbacks' => $stats['neutral_feedbacks'] ?? 0,
            'positive_rate' => $positiveRate,
            'negative_rate' => $negativeRate,
            'feedback_rate' => $feedbackRate,
            'rating_growth' => $ratingGrowth
        ];
    }

    /**
     * Lấy báo cáo feedback chi tiết
     */
    private function getFeedbackReport($dateFrom, $dateTo, $tourId = null, $feedbackType = null, $rating = null, $sentiment = null)
    {
        $whereConditions = ["F.created_at BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        if ($tourId) {
            $whereConditions[] = "F.tour_id = :tour_id";
            $params[':tour_id'] = $tourId;
        }

        if ($feedbackType) {
            $whereConditions[] = "F.feedback_type = :feedback_type";
            $params[':feedback_type'] = $feedbackType;
        }

        if ($rating) {
            $whereConditions[] = "F.rating = :rating";
            $params[':rating'] = $rating;
        }

        if ($sentiment) {
            $whereConditions[] = "F.sentiment = :sentiment";
            $params[':sentiment'] = $sentiment;
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    F.*,
                    T.name AS tour_name,
                    TC.name AS category_name,
                    U.full_name AS customer_name,
                    U.email AS customer_email,
                    T.name AS target_name,
                    'tour' as feedback_type,
                    CASE 
                        WHEN F.rating >= 4 THEN 'positive'
                        WHEN F.rating <= 2 THEN 'negative'
                        ELSE 'neutral'
                    END AS sentiment
                FROM tour_feedbacks F
                LEFT JOIN tours T ON F.tour_id = T.id
                LEFT JOIN tour_categories TC ON T.category_id = TC.id
                LEFT JOIN users U ON F.user_id = U.user_id
                $whereClause
                ORDER BY F.created_at DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Lấy top tours được đánh giá cao nhất
     */
    private function getTopRatedTours($dateFrom, $dateTo, $limit = 10)
    {
        $sql = "SELECT 
                    T.id,
                    T.name AS tour_name,
                    COUNT(F.id) AS feedback_count,
                    AVG(F.rating) AS avg_rating
                FROM tours T
                LEFT JOIN tour_feedbacks F ON T.id = F.tour_id
                WHERE F.created_at BETWEEN :date_from AND :date_to
                GROUP BY T.id, T.name
                HAVING feedback_count > 0
                ORDER BY avg_rating DESC, feedback_count DESC
                LIMIT " . (int)$limit;

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Phân tích từ khóa trong feedback
     */
    private function getKeywordAnalysis($dateFrom, $dateTo)
    {
        // Simplified keyword analysis - trong thực tế có thể dùng NLP library
        $sql = "SELECT 
                    comment,
                    CASE 
                        WHEN rating >= 4 THEN 'positive'
                        WHEN rating <= 2 THEN 'negative'
                        ELSE 'neutral'
                    END as sentiment 
                FROM tour_feedbacks 
                WHERE created_at BETWEEN :date_from AND :date_to
                AND comment IS NOT NULL AND comment != ''";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
            $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $feedbacks = [];
        }

        $keywords = [];
        $positiveWords = ['tốt', 'hay', 'hài lòng', 'tuyệt vời', 'xuất sắc', 'thích', 'đẹp', 'chuyên nghiệp'];
        $negativeWords = ['tệ', 'kém', 'không hài lòng', 'thất vọng', 'chậm', 'dở', 'kém'];

        foreach ($feedbacks as $feedback) {
            $comment = strtolower($feedback['comment']);

            foreach ($positiveWords as $word) {
                if (strpos($comment, $word) !== false) {
                    $keywords[$word] = ($keywords[$word] ?? 0) + 1;
                }
            }

            foreach ($negativeWords as $word) {
                if (strpos($comment, $word) !== false) {
                    $keywords[$word] = ($keywords[$word] ?? 0) + 1;
                }
            }
        }

        arsort($keywords);

        $result = [];
        foreach ($keywords as $keyword => $count) {
            $sentiment = in_array($keyword, $positiveWords) ? 'positive' : 'negative';
            $result[] = [
                'keyword' => $keyword,
                'count' => $count,
                'sentiment' => $sentiment
            ];
        }

        return array_slice($result, 0, 20);
    }

    /**
     * Lấy phân loại feedback theo loại
     */
    private function getFeedbackTypeDistribution($dateFrom, $dateTo)
    {
        $sql = "SELECT 
                    'Tour' as type,
                    COUNT(F.id) as count
                FROM tour_feedbacks F 
                WHERE F.created_at BETWEEN :date_from AND :date_to";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $data = [];
        }

        // Map loại feedback sang tên tiếng Việt
        $typeLabels = [
            'tour' => 'Tour',
            'supplier' => 'Nhà cung cấp',
            'guide' => 'HDV'
        ];

        foreach ($data as &$item) {
            $item['type'] = $typeLabels[$item['type']] ?? $item['type'];
        }

        return $data;
    }

    /**
     * Lấy thống kê feedback kỳ trước
     */
    private function getPreviousFeedbackStats($dateFrom, $dateTo, $tourId = null, $feedbackType = null, $rating = null, $sentiment = null)
    {
        // Tính khoảng thời gian kỳ trước
        $days = (strtotime($dateTo) - strtotime($dateFrom)) / (60 * 60 * 24) + 1;
        $prevDateTo = date('Y-m-d', strtotime($dateFrom . ' -1 day'));
        $prevDateFrom = date('Y-m-d', strtotime($prevDateTo . ' -' . ($days - 1) . ' days'));

        return $this->getFeedbackStats($prevDateFrom, $prevDateTo, $tourId, $feedbackType, $rating, $sentiment, false);
    }

    private function exportBookingReport($exportType, $bookingStats, $bookings)
    {
        if ($exportType === 'excel') {
            // Export toàn bộ báo cáo booking ra Excel
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="bao-cao-booking-' . date('Y-m-d') . '.xls"');

            echo "Báo cáo Booking - " . date('d/m/Y') . "\n\n";
            echo "Tổng quan\n";
            echo "Tổng booking: " . number_format($bookingStats['total_bookings']) . "\n";
            echo "Booking thành công: " . number_format($bookingStats['successful_bookings']) . "\n";
            echo "Tỷ lệ thành công: " . number_format($bookingStats['success_rate'], 1) . "%\n";
            echo "Tỷ lệ chuyển đổi: " . number_format($bookingStats['conversion_rate'], 1) . "%\n";
            echo "Tổng khách hàng: " . number_format($bookingStats['total_customers']) . "\n\n";

            echo "Chi tiết Booking\n";
            echo "Mã BK\tKhách hàng\tTour\tNgày đi\tSố khách\tGiá trị\tTrạng thái\tNguồn\n";

            foreach ($bookings as $booking) {
                echo "#" . str_pad($booking['id'], 6, '0', STR_PAD_LEFT) . "\t";
                echo ($booking['customer_name'] ?? 'N/A') . "\t";
                echo $booking['tour_name'] . "\t";
                echo date('d/m/Y', strtotime($booking['departure_date'])) . "\t";
                echo ($booking['adults'] + $booking['children'] + $booking['infants']) . "\t";
                echo number_format($booking['final_price']) . "\t";
                echo $booking['status'] . "\t";
                echo ($booking['source'] ?? '') . "\n";
            }

            exit;
        } elseif ($exportType === 'invoice' && isset($_GET['booking_id'])) {
            // Export invoice cho một booking cụ thể
            $bookingId = $_GET['booking_id'];
            $bookingData = array_filter($bookings, function ($booking) use ($bookingId) {
                return $booking['id'] == $bookingId;
            });

            if (!empty($bookingData)) {
                $booking = reset($bookingData);
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment; filename="invoice-' . $booking['id'] . '-' . date('Y-m-d') . '.xls"');

                echo "HÓA ĐƠN/BIÊN NHẬN\n\n";
                echo "Mã Booking: #" . str_pad($booking['id'], 6, '0', STR_PAD_LEFT) . "\n";
                echo "Khách hàng: " . ($booking['customer_name'] ?? 'N/A') . "\n";
                echo "Tour: " . $booking['tour_name'] . "\n";
                echo "Ngày đi: " . date('d/m/Y', strtotime($booking['departure_date'])) . "\n";
                echo "Số khách: " . ($booking['adults'] + $booking['children'] + $booking['infants']) . "\n";
                echo "Giá trị: " . number_format($booking['final_price']) . " VNĐ\n";
                echo "Trạng thái: " . $booking['status'] . "\n";
                echo "Ngày tạo: " . date('d/m/Y', strtotime($booking['booking_date'])) . "\n";

                exit;
            }
        }
    }

    public function conversion()
    {
        // Lấy tham số lọc
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $tourId = $_GET['tour_id'] ?? null;
        $categoryId = $_GET['category_id'] ?? null;
        $source = $_GET['source'] ?? null;
        $keyword = $_GET['keyword'] ?? null;

        // Build filters array
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'tour_id' => $tourId,
            'category_id' => $categoryId,
            'source' => $source,
            'keyword' => $keyword
        ];

        // Validate filters
        $filterErrors = $this->filterService->validateFilters($filters, 'conversion');
        if (!empty($filterErrors)) {
            $_SESSION['filter_errors'] = $filterErrors;
        }

        // Lấy dữ liệu báo cáo chuyển đổi
        $conversionData = $this->conversionReport->getConversionRate($dateFrom, $dateTo, $filters);
        $topTours = $this->conversionReport->getConversionByTour($dateFrom, $dateTo, 10);
        $sourceConversion = $this->conversionReport->getConversionBySource($dateFrom, $dateTo);
        $categoryConversion = $this->conversionReport->getConversionByCategory($dateFrom, $dateTo);
        $monthlyConversion = $this->conversionReport->getConversionByMonth($dateFrom, $dateTo);
        $funnelAnalysis = $this->conversionReport->getFunnelAnalysis($dateFrom, $dateTo, $filters);
        $timeAnalysis = $this->conversionReport->getConversionTimeAnalysis($dateFrom, $dateTo);
        $growthData = $this->conversionReport->getPreviousPeriodComparison($dateFrom, $dateTo, $filters);

        // Lấy options cho filters
        $filterOptions = [
            'tours' => $this->filterService->getFilteredTours([]),
            'categories' => $this->filterService->getCategories(),
            'sources' => $this->filterService->getBookingSources(),
            'datePresets' => $this->filterService->getDateRangePresets()
        ];

        // Xử lý export nếu có yêu cầu
        if (isset($_GET['export'])) {
            $this->exportConversionReport($_GET['export'], $conversionData, $topTours, $sourceConversion, $categoryConversion);
            return;
        }

        // Truyền dữ liệu sang view
        $data = [
            'conversionData' => $conversionData,
            'topTours' => $topTours,
            'sourceConversion' => $sourceConversion,
            'categoryConversion' => $categoryConversion,
            'monthlyConversion' => $monthlyConversion,
            'funnelAnalysis' => $funnelAnalysis,
            'timeAnalysis' => $timeAnalysis,
            'growthData' => $growthData,
            'filterOptions' => $filterOptions,
            'filters' => $filters,
            'filterSummary' => $this->filterService->getFilterSummary($filters, 'conversion')
        ];

        require_once PATH_VIEW_ADMIN . 'pages/reports/conversion.php';
    }

    /**
     * Export báo cáo chuyển đổi
     */
    private function exportConversionReport($exportType, $conversionData, $topTours, $sourceConversion, $categoryConversion)
    {
        if ($exportType === 'excel') {
            $this->exportService->exportConversionReport($conversionData, $topTours, $sourceConversion, $categoryConversion);
        } elseif ($exportType === 'pdf') {
            $data = [
                'conversion_rates' => $conversionData['conversion_rates'],
                'total_inquiries' => $conversionData['total_inquiries'],
                'total_bookings' => $conversionData['total_bookings'],
                'stage_counts' => $conversionData['stage_counts'],
                'topTours' => $topTours,
                'sourceConversion' => $sourceConversion,
                'categoryConversion' => $categoryConversion
            ];
            $this->exportService->exportToPDF($data, 'conversion');
        }
    }

    private function exportFinancialReport($exportType, $financialData, $tourFinancials)
    {
        if ($exportType === 'excel') {
            $this->exportService->exportFinancialReport($financialData, $tourFinancials);
        } elseif ($exportType === 'pdf') {
            $data = [
                'total_revenue' => $financialData['total_revenue'],
                'total_expense' => $financialData['total_expense'],
                'profit' => $financialData['profit'],
                'profit_margin' => $financialData['profit_margin'],
                'tourFinancials' => $tourFinancials
            ];
            $this->exportService->exportToPDF($data, 'financial');
        } elseif ($exportType === 'tour' && isset($_GET['tour_id'])) {
            // Export báo cáo cho một tour cụ thể
            $tourId = $_GET['tour_id'];
            $tourData = array_filter($tourFinancials, function ($tour) use ($tourId) {
                return $tour['tour_id'] == $tourId;
            });

            if (!empty($tourData)) {
                $tour = reset($tourData);
                $filename = 'bao-cao-tour-' . $tour['tour_id'] . '-' . date('Y-m-d') . '.xlsx';
                $this->exportService->exportFinancialReport($tour, [$tour], $filename);
            }
        }
    }

    /**
     * Helper methods cho Dashboard
     */
    private function getDateRangeFromPeriod($period)
    {
        $today = date('Y-m-d');

        switch ($period) {
            case '7':
                return ['from' => date('Y-m-d', strtotime('-6 days')), 'to' => $today];
            case '30':
                return ['from' => date('Y-m-d', strtotime('-29 days')), 'to' => $today];
            case '90':
                return ['from' => date('Y-m-d', strtotime('-89 days')), 'to' => $today];
            case 'this_month':
                return ['from' => date('Y-m-01'), 'to' => $today];
            case 'last_month':
                return ['from' => date('Y-m-01', strtotime('-1 month')), 'to' => date('Y-m-t', strtotime('-1 month'))];
            case 'this_quarter':
                $quarter = ceil(date('n') / 3);
                $startMonth = ($quarter - 1) * 3 + 1;
                return ['from' => date('Y-' . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . '-01'), 'to' => $today];
            case 'this_year':
                return ['from' => date('Y-01-01'), 'to' => $today];
            default:
                return ['from' => date('Y-m-01'), 'to' => $today];
        }
    }

    private function getTourStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_tours,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_tours,
                    COUNT(DISTINCT category_id) as total_categories
                FROM tours";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getTrendData($dateFrom, $dateTo)
    {
        // Revenue trend
        $sqlRevenue = "SELECT 
                          DATE(booking_date) as date,
                          DATE_FORMAT(booking_date, '%d/%m') as label,
                          SUM(final_price) as revenue
                       FROM bookings
                       WHERE booking_date BETWEEN :date_from AND :date_to
                       GROUP BY DATE(booking_date)
                       ORDER BY date ASC";

        $stmt = $this->pdo->prepare($sqlRevenue);
        $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
        $revenueData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Bookings trend
        $sqlBookings = "SELECT 
                           DATE(booking_date) as date,
                           COUNT(*) as bookings
                        FROM bookings
                        WHERE booking_date BETWEEN :date_from AND :date_to
                        GROUP BY DATE(booking_date)
                        ORDER BY date ASC";

        $stmt = $this->pdo->prepare($sqlBookings);
        $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
        $bookingsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Profit trend (simplified)
        $profitData = [];
        foreach ($revenueData as $item) {
            $profitData[] = [
                'date' => $item['date'],
                'profit' => $item['revenue'] * 0.3 // Simplified profit calculation
            ];
        }

        return [
            'labels' => array_column($revenueData, 'label'),
            'revenue' => array_column($revenueData, 'revenue'),
            'bookings' => array_column($bookingsData, 'bookings'),
            'profit' => array_column($profitData, 'profit')
        ];
    }

    private function getRevenueDistribution($dateFrom, $dateTo)
    {
        $sql = "SELECT 
                    TC.name as category_name,
                    SUM(B.final_price) as total_revenue
                FROM bookings B
                LEFT JOIN tours T ON B.tour_id = T.id
                LEFT JOIN tour_categories TC ON T.category_id = TC.id
                WHERE B.booking_date BETWEEN :date_from AND :date_to
                GROUP BY TC.id, TC.name
                ORDER BY total_revenue DESC
                LIMIT 5";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'labels' => array_column($data, 'category_name'),
            'data' => array_column($data, 'total_revenue')
        ];
    }

    private function getRatingDistribution($dateFrom, $dateTo)
    {
        $sql = "SELECT 
                    rating,
                    COUNT(*) as count
                FROM feedbacks
                WHERE created_at BETWEEN :date_from AND :date_to
                GROUP BY rating
                ORDER BY rating DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $data = [];
        }

        // Initialize with 0 for all ratings
        $distribution = [0, 0, 0, 0, 0]; // 5,4,3,2,1 stars

        foreach ($data as $item) {
            $index = 5 - $item['rating']; // 5 stars = index 0, 1 star = index 4
            if ($index >= 0 && $index < 5) {
                $distribution[$index] = $item['count'];
            }
        }

        return $distribution;
    }

    private function getRecentActivities()
    {
        $activities = [];

        // Recent bookings
        $sql = "SELECT 
                   B.id,
                   B.customer_name,
                   T.name as tour_name,
                   B.booking_date,
                   'booking' as type,
                   'fa-calendar-check' as icon,
                   'success' as color
                FROM bookings B
                LEFT JOIN tours T ON B.tour_id = T.id
                ORDER BY B.booking_date DESC
                LIMIT 3";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($bookings as $booking) {
            $activities[] = [
                'title' => 'Booking mới',
                'description' => $booking['customer_name'] . ' đã đặt tour ' . $booking['tour_name'],
                'time' => $this->timeAgo($booking['booking_date']),
                'icon' => $booking['icon'],
                'color' => $booking['color']
            ];
        }

        // Recent feedbacks
        $sql = "SELECT 
                   F.id,
                   F.customer_name,
                   T.name as tour_name,
                   F.rating,
                   F.created_at,
                   'feedback' as type,
                   'fa-star' as icon,
                   'warning' as color
                FROM feedbacks F
                LEFT JOIN tours T ON F.tour_id = T.id
                ORDER BY F.created_at DESC
                LIMIT 2";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($feedbacks as $feedback) {
            $activities[] = [
                'title' => 'Phản hồi mới',
                'description' => $feedback['customer_name'] . ' đánh giá ' . $feedback['rating'] . ' sao',
                'time' => $this->timeAgo($feedback['created_at']),
                'icon' => $feedback['icon'],
                'color' => $feedback['color']
            ];
        }

        return $activities;
    }

    private function getDashboardAlerts($dateFrom, $dateTo)
    {
        $alerts = [];

        // Low conversion rate alert
        $conversionData = $this->conversionReport->getConversionRate($dateFrom, $dateTo);
        if ($conversionData['conversion_rates']['booking_to_payment'] < 20) {
            $alerts[] = [
                'title' => 'Tỷ lệ chuyển đổi thấp',
                'message' => 'Tỷ lệ chuyển đổi booking → payment chỉ ' . number_format($conversionData['conversion_rates']['booking_to_payment'], 1) . '%',
                'type' => 'warning',
                'icon' => 'exclamation-triangle',
                'time' => 'Vừa xong'
            ];
        }

        // Low rating alert
        $feedbackStats = $this->getFeedbackStats($dateFrom, $dateTo);
        if ($feedbackStats['avg_rating'] < 3.5) {
            $alerts[] = [
                'title' => 'Đánh giá thấp',
                'message' => 'Điểm đánh giá trung bình chỉ ' . number_format($feedbackStats['avg_rating'], 1) . '/5.0',
                'type' => 'danger',
                'icon' => 'thumbs-down',
                'time' => 'Vừa xong'
            ];
        }

        // High cancellation rate
        $bookingStats = $this->bookingModel->getBookingStats($dateFrom, $dateTo);
        if ($bookingStats['cancellation_rate'] > 10) {
            $alerts[] = [
                'title' => 'Tỷ lệ hủy cao',
                'message' => 'Tỷ lệ hủy booking là ' . number_format($bookingStats['cancellation_rate'], 1) . '%',
                'type' => 'warning',
                'icon' => 'times-circle',
                'time' => 'Vừa xong'
            ];
        }

        return $alerts;
    }

    private function calculateGrowthMetrics($dashboardData, $dateFrom, $dateTo, $period)
    {
        // Get previous period data for comparison
        $previousRange = $this->getPreviousPeriodRange($dateFrom, $dateTo, $period);

        // Calculate growth for financial metrics
        $previousFinancial = $this->financialReport->getFinancialSummary($previousRange['from'], $previousRange['to']);
        $dashboardData['financial']['revenue_growth'] = $this->calculateGrowth($previousFinancial['total_revenue'], $dashboardData['financial']['total_revenue']);
        $dashboardData['financial']['profit_growth'] = $this->calculateGrowth($previousFinancial['profit'], $dashboardData['financial']['profit']);

        // Calculate growth for booking metrics
        $previousBookings = $this->bookingModel->getBookingStats($previousRange['from'], $previousRange['to']);
        $dashboardData['bookings']['booking_growth'] = $this->calculateGrowth($previousBookings['total_bookings'], $dashboardData['bookings']['total_bookings']);

        // Calculate growth for conversion metrics
        $previousConversion = $this->conversionReport->getConversionRate($previousRange['from'], $previousRange['to']);
        $dashboardData['conversion']['conversion_growth'] = $this->calculateGrowth(
            $previousConversion['conversion_rates']['booking_to_payment'],
            $dashboardData['conversion']['conversion_rates']['booking_to_payment']
        );

        // Calculate growth for feedback metrics
        $previousFeedback = $this->getFeedbackStats($previousRange['from'], $previousRange['to']);
        $dashboardData['feedback']['rating_growth'] = $this->calculateGrowth($previousFeedback['avg_rating'], $dashboardData['feedback']['avg_rating']);

        return $dashboardData;
    }

    private function getPreviousPeriodRange($dateFrom, $dateTo, $period)
    {
        $days = (strtotime($dateTo) - strtotime($dateFrom)) / (60 * 60 * 24) + 1;
        $prevDateTo = date('Y-m-d', strtotime($dateFrom . ' -1 day'));
        $prevDateFrom = date('Y-m-d', strtotime($prevDateTo . ' -' . ($days - 1) . ' days'));

        return ['from' => $prevDateFrom, 'to' => $prevDateTo];
    }

    private function calculateGrowth($previous, $current)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return (($current - $previous) / $previous) * 100;
    }

    private function timeAgo($datetime)
    {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return 'Vừa xong';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' phút trước';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' giờ trước';
        } elseif ($diff < 2592000) {
            return floor($diff / 86400) . ' ngày trước';
        } else {
            return date('d/m/Y', $time);
        }
    }

    private function getDashboardData($params)
    {
        $period = $params['period'] ?? '30';
        $trendType = $params['trend_type'] ?? 'revenue';

        $dateRange = $this->getDateRangeFromPeriod($period);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        $trendData = $this->getTrendData($dateFrom, $dateTo);

        return [
            'trend_data' => $trendData,
            'revenue_distribution' => $this->getRevenueDistribution($dateFrom, $dateTo),
            'conversion_by_source' => $this->conversionReport->getConversionBySource($dateFrom, $dateTo),
            'rating_distribution' => $this->getRatingDistribution($dateFrom, $dateTo)
        ];
    }

    private function exportDashboard($format, $period, $dashboardData)
    {
        $filename = 'dashboard-' . $period . '-' . date('Y-m-d');

        if ($format === 'excel') {
            $this->exportService->exportDashboardReport($dashboardData, $filename . '.xlsx');
        } elseif ($format === 'pdf') {
            $this->exportService->exportToPDF($dashboardData, 'dashboard', $filename . '.pdf');
        }
    }
}
