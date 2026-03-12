<?php

/**
 * Staff Report Model - Báo cáo hiệu quả nhân sự (HDV, sales)
 */
class StaffReport extends BaseModel
{
    protected $table = 'users';
    private $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->pdo = self::$pdo;
    }

    /**
     * Lấy báo cáo tổng quan hiệu quả nhân sự
     */
    public function getStaffPerformanceSummary($dateFrom = null, $dateTo = null, $filters = [])
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $whereConditions = ["B.booking_date BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        // Apply filters
        if (!empty($filters['staff_role'])) {
            $whereConditions[] = "U.role = :staff_role";
            $params[':staff_role'] = $filters['staff_role'];
        }
        if (!empty($filters['staff_id'])) {
            $whereConditions[] = "U.user_id = :staff_id";
            $params[':staff_id'] = $filters['staff_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        // Thống kê tổng quan
        $sql = "SELECT 
                    COUNT(DISTINCT U.user_id) as total_staff,
                    COUNT(DISTINCT CASE WHEN U.role = 'guide' THEN U.user_id END) as total_guides,
                    COUNT(DISTINCT CASE WHEN U.role = 'sales' THEN U.user_id END) as total_sales,
                    COUNT(DISTINCT CASE WHEN U.role = 'admin' THEN U.user_id END) as total_admins,
                    COUNT(B.id) as total_bookings,
                    SUM(B.final_price) as total_revenue,
                    AVG(B.final_price) as avg_booking_value,
                    COUNT(DISTINCT B.customer_id) as total_customers
                FROM users U
                LEFT JOIN bookings B ON (
                    (U.role = 'sales' AND B.created_by = U.user_id) OR
                    (U.role = 'guide' AND B.guide_id = U.user_id)
                )
                $whereClause";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $summary = $stmt->fetch();

        return [
            'total_staff' => $summary['total_staff'] ?? 0,
            'total_guides' => $summary['total_guides'] ?? 0,
            'total_sales' => $summary['total_sales'] ?? 0,
            'total_admins' => $summary['total_admins'] ?? 0,
            'total_bookings' => $summary['total_bookings'] ?? 0,
            'total_revenue' => $summary['total_revenue'] ?? 0,
            'avg_booking_value' => round($summary['avg_booking_value'] ?? 0, 2),
            'total_customers' => $summary['total_customers'] ?? 0,
            'revenue_per_staff' => ($summary['total_staff'] ?? 0) > 0 ? round(($summary['total_revenue'] ?? 0) / $summary['total_staff'], 2) : 0,
            'bookings_per_staff' => ($summary['total_staff'] ?? 0) > 0 ? round(($summary['total_bookings'] ?? 0) / $summary['total_staff'], 2) : 0
        ];
    }

    /**
     * Lấy hiệu quả của từng nhân viên
     */
    public function getStaffPerformanceDetails($dateFrom = null, $dateTo = null, $filters = [])
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $whereConditions = ["B.booking_date BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        if (!empty($filters['staff_role'])) {
            $whereConditions[] = "U.role = :staff_role";
            $params[':staff_role'] = $filters['staff_role'];
        }
        if (!empty($filters['staff_id'])) {
            $whereConditions[] = "U.user_id = :staff_id";
            $params[':staff_id'] = $filters['staff_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    U.user_id,
                    U.full_name,
                    U.email,
                    U.phone,
                    U.role,
                    U.status,
                    COUNT(B.id) as booking_count,
                    SUM(B.final_price) as total_revenue,
                    AVG(B.final_price) as avg_booking_value,
                    MIN(B.booking_date) as first_booking_date,
                    MAX(B.booking_date) as last_booking_date,
                    COUNT(DISTINCT B.customer_id) as unique_customers,
                    SUM(CASE WHEN B.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN B.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                    AVG(TF.rating) as avg_rating,
                    COUNT(TF.id) as feedback_count,
                    -- Performance metrics
                    CASE 
                        WHEN U.role = 'sales' THEN 
                            COUNT(B.id) * 0.6 + SUM(B.final_price) / 1000000 * 0.4
                        WHEN U.role = 'guide' THEN 
                            AVG(TF.rating) * 0.7 + COUNT(TF.id) * 0.3
                        ELSE 0
                    END as performance_score
                FROM users U
                LEFT JOIN bookings B ON (
                    (U.role = 'sales' AND B.created_by = U.user_id) OR
                    (U.role = 'guide' AND B.guide_id = U.user_id)
                )
                LEFT JOIN tour_feedbacks TF ON (
                    B.id = TF.booking_id AND TF.user_id = B.customer_id
                )
                $whereClause
                GROUP BY U.user_id, U.full_name, U.email, U.phone, U.role, U.status
                HAVING booking_count > 0 OR feedback_count > 0
                ORDER BY performance_score DESC, total_revenue DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $staffPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tính toán thêm các chỉ số
        foreach ($staffPerformance as &$staff) {
            $bookingCount = $staff['booking_count'];
            $staff['completion_rate'] = $bookingCount > 0 ? round(($staff['completed_bookings'] / $bookingCount) * 100, 2) : 0;
            $staff['cancellation_rate'] = $bookingCount > 0 ? round(($staff['cancelled_bookings'] / $bookingCount) * 100, 2) : 0;
            $staff['revenue_per_booking'] = $bookingCount > 0 ? round($staff['total_revenue'] / $bookingCount, 2) : 0;
            $staff['customers_per_booking'] = $bookingCount > 0 ? round($staff['unique_customers'] / $bookingCount, 2) : 0;
            $staff['performance_score'] = round($staff['performance_score'], 2);
            $staff['avg_rating'] = round($staff['avg_rating'], 2);
        }

        return $staffPerformance;
    }

    /**
     * Lấy hiệu quả của hướng dẫn viên
     */
    public function getGuidePerformance($dateFrom = null, $dateTo = null, $filters = [])
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $whereConditions = ["U.role = 'guide'", "B.booking_date BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        if (!empty($filters['guide_id'])) {
            $whereConditions[] = "U.user_id = :guide_id";
            $params[':guide_id'] = $filters['guide_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    U.user_id,
                    U.full_name,
                    U.email,
                    U.phone,
                    COUNT(B.id) as tour_count,
                    COUNT(DISTINCT B.tour_id) as unique_tours,
                    SUM(B.adults + B.children + B.infants) as total_tourists,
                    AVG(TF.rating) as avg_rating,
                    COUNT(TF.id) as feedback_count,
                    SUM(CASE WHEN TF.rating >= 4 THEN 1 ELSE 0 END) as positive_feedbacks,
                    SUM(CASE WHEN TF.rating <= 2 THEN 1 ELSE 0 END) as negative_feedbacks,
                    -- Tour types handled
                    GROUP_CONCAT(DISTINCT TC.name) as tour_categories,
                    -- Performance metrics
                    CASE 
                        WHEN COUNT(TF.id) > 0 THEN AVG(TF.rating)
                        ELSE 3.5
                    END as guide_score,
                    -- Tourist satisfaction
                    (COUNT(TF.id) * 0.7 + SUM(B.adults + B.children + B.infants) / 100 * 0.3) as satisfaction_score
                FROM users U
                LEFT JOIN bookings B ON U.user_id = B.guide_id
                LEFT JOIN tours T ON B.tour_id = T.id
                LEFT JOIN tour_categories TC ON T.category_id = TC.id
                LEFT JOIN tour_feedbacks TF ON B.id = TF.booking_id
                $whereClause
                GROUP BY U.user_id, U.full_name, U.email, U.phone
                HAVING tour_count > 0
                ORDER BY guide_score DESC, avg_rating DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $guidePerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tính toán thêm các chỉ số
        foreach ($guidePerformance as &$guide) {
            $tourCount = $guide['tour_count'];
            $feedbackCount = $guide['feedback_count'];
            $guide['tourists_per_tour'] = $tourCount > 0 ? round($guide['total_tourists'] / $tourCount, 2) : 0;
            $guide['feedback_rate'] = $tourCount > 0 ? round(($feedbackCount / $tourCount) * 100, 2) : 0;
            $guide['positive_rate'] = $feedbackCount > 0 ? round(($guide['positive_feedbacks'] / $feedbackCount) * 100, 2) : 0;
            $guide['negative_rate'] = $feedbackCount > 0 ? round(($guide['negative_feedbacks'] / $feedbackCount) * 100, 2) : 0;
            $guide['guide_score'] = round($guide['guide_score'], 2);
            $guide['satisfaction_score'] = round($guide['satisfaction_score'], 2);
            $guide['avg_rating'] = round($guide['avg_rating'], 2);
        }

        return $guidePerformance;
    }

    /**
     * Lấy hiệu quả của nhân viên sales
     */
    public function getSalesPerformance($dateFrom = null, $dateTo = null, $filters = [])
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $whereConditions = ["U.role = 'sales'", "B.booking_date BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        if (!empty($filters['sales_id'])) {
            $whereConditions[] = "U.user_id = :sales_id";
            $params[':sales_id'] = $filters['sales_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    U.user_id,
                    U.full_name,
                    U.email,
                    U.phone,
                    COUNT(B.id) as booking_count,
                    SUM(B.final_price) as total_revenue,
                    AVG(B.final_price) as avg_booking_value,
                    MIN(B.final_price) as min_booking_value,
                    MAX(B.final_price) as max_booking_value,
                    COUNT(DISTINCT B.customer_id) as unique_customers,
                    COUNT(DISTINCT B.tour_id) as unique_tours,
                    SUM(CASE WHEN B.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN B.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                    SUM(CASE WHEN B.status = 'paid' THEN 1 ELSE 0 END) as paid_bookings,
                    -- Conversion metrics
                    COUNT(CASE WHEN B.status IN ('completed', 'paid') THEN 1 END) / COUNT(*) * 100 as conversion_rate,
                    -- Revenue metrics
                    SUM(B.final_price) / COUNT(DISTINCT B.customer_id) as revenue_per_customer,
                    -- Performance score
                    (COUNT(B.id) * 0.4 + SUM(B.final_price) / 1000000 * 0.6) as sales_score
                FROM users U
                LEFT JOIN bookings B ON U.user_id = B.created_by
                $whereClause
                GROUP BY U.user_id, U.full_name, U.email, U.phone
                HAVING booking_count > 0
                ORDER BY sales_score DESC, total_revenue DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $salesPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tính toán thêm các chỉ số
        foreach ($salesPerformance as &$sales) {
            $bookingCount = $sales['booking_count'];
            $sales['completion_rate'] = $bookingCount > 0 ? round(($sales['completed_bookings'] / $bookingCount) * 100, 2) : 0;
            $sales['cancellation_rate'] = $bookingCount > 0 ? round(($sales['cancelled_bookings'] / $bookingCount) * 100, 2) : 0;
            $sales['payment_rate'] = $bookingCount > 0 ? round(($sales['paid_bookings'] / $bookingCount) * 100, 2) : 0;
            $sales['revenue_per_booking'] = $bookingCount > 0 ? round($sales['total_revenue'] / $bookingCount, 2) : 0;
            $sales['customers_per_booking'] = $bookingCount > 0 ? round($sales['unique_customers'] / $bookingCount, 2) : 0;
            $sales['conversion_rate'] = round($sales['conversion_rate'], 2);
            $sales['revenue_per_customer'] = round($sales['revenue_per_customer'], 2);
            $sales['sales_score'] = round($sales['sales_score'], 2);
            $sales['avg_booking_value'] = round($sales['avg_booking_value'], 2);
        }

        return $salesPerformance;
    }

    /**
     * Lấy báo cáo hiệu quả theo thời gian (tháng)
     */
    public function getStaffPerformanceTrends($year = null, $filters = [])
    {
        $year = $year ?? date('Y');

        $whereConditions = ["YEAR(B.booking_date) = :year"];
        $params = [':year' => $year];

        if (!empty($filters['staff_role'])) {
            $whereConditions[] = "U.role = :staff_role";
            $params[':staff_role'] = $filters['staff_role'];
        }
        if (!empty($filters['staff_id'])) {
            $whereConditions[] = "U.user_id = :staff_id";
            $params[':staff_id'] = $filters['staff_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    MONTH(B.booking_date) as month,
                    DATE_FORMAT(B.booking_date, '%m/%Y') as month_label,
                    COUNT(DISTINCT U.user_id) as active_staff,
                    COUNT(B.id) as total_bookings,
                    SUM(B.final_price) as total_revenue,
                    AVG(B.final_price) as avg_booking_value,
                    COUNT(DISTINCT B.customer_id) as total_customers,
                    AVG(TF.rating) as avg_rating,
                    COUNT(TF.id) as total_feedbacks,
                    -- Performance by role
                    COUNT(DISTINCT CASE WHEN U.role = 'guide' THEN U.user_id END) as active_guides,
                    COUNT(DISTINCT CASE WHEN U.role = 'sales' THEN U.user_id END) as active_sales,
                    SUM(CASE WHEN U.role = 'guide' THEN 1 ELSE 0 END) as guide_bookings,
                    SUM(CASE WHEN U.role = 'sales' THEN 1 ELSE 0 END) as sales_bookings,
                    SUM(CASE WHEN U.role = 'guide' THEN B.final_price ELSE 0 END) as guide_revenue,
                    SUM(CASE WHEN U.role = 'sales' THEN B.final_price ELSE 0 END) as sales_revenue
                FROM users U
                LEFT JOIN bookings B ON (
                    (U.role = 'sales' AND B.created_by = U.user_id) OR
                    (U.role = 'guide' AND B.guide_id = U.user_id)
                )
                LEFT JOIN tour_feedbacks TF ON B.id = TF.booking_id
                $whereClause
                GROUP BY MONTH(B.booking_date), DATE_FORMAT(B.booking_date, '%m/%Y')
                ORDER BY month ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Đảm bảo có đủ 12 tháng
        $result = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthData = [
                'month' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'active_staff' => 0,
                'total_bookings' => 0,
                'total_revenue' => 0,
                'avg_booking_value' => 0,
                'total_customers' => 0,
                'avg_rating' => 0,
                'total_feedbacks' => 0,
                'active_guides' => 0,
                'active_sales' => 0,
                'guide_bookings' => 0,
                'sales_bookings' => 0,
                'guide_revenue' => 0,
                'sales_revenue' => 0,
                'revenue_per_staff' => 0,
                'bookings_per_staff' => 0
            ];

            foreach ($monthlyData as $data) {
                if ($data['month'] == $month) {
                    $monthData = array_merge($monthData, $data);
                    $monthData['revenue_per_staff'] = $data['active_staff'] > 0 ? round($data['total_revenue'] / $data['active_staff'], 2) : 0;
                    $monthData['bookings_per_staff'] = $data['active_staff'] > 0 ? round($data['total_bookings'] / $data['active_staff'], 2) : 0;
                    $monthData['avg_booking_value'] = round($monthData['avg_booking_value'], 2);
                    $monthData['avg_rating'] = round($monthData['avg_rating'], 2);
                    break;
                }
            }

            $result[] = $monthData;
        }

        return $result;
    }

    /**
     * Lấy top nhân viên hiệu quả nhất
     */
    public function getTopPerformers($dateFrom = null, $dateTo = null, $limit = 10, $role = null)
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $whereConditions = ["B.booking_date BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        if ($role) {
            $whereConditions[] = "U.role = :role";
            $params[':role'] = $role;
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    U.user_id,
                    U.full_name,
                    U.email,
                    U.role,
                    COUNT(B.id) as booking_count,
                    SUM(B.final_price) as total_revenue,
                    AVG(TF.rating) as avg_rating,
                    COUNT(TF.id) as feedback_count,
                    -- Performance score based on role
                    CASE 
                        WHEN U.role = 'sales' THEN 
                            (COUNT(B.id) * 0.4 + SUM(B.final_price) / 1000000 * 0.6)
                        WHEN U.role = 'guide' THEN 
                            (AVG(TF.rating) * 0.7 + COUNT(TF.id) * 0.3)
                        ELSE 0
                    END as performance_score
                FROM users U
                LEFT JOIN bookings B ON (
                    (U.role = 'sales' AND B.created_by = U.user_id) OR
                    (U.role = 'guide' AND B.guide_id = U.user_id)
                )
                LEFT JOIN tour_feedbacks TF ON B.id = TF.booking_id
                $whereClause
                GROUP BY U.user_id, U.full_name, U.email, U.role
                HAVING booking_count > 0 OR feedback_count > 0
                ORDER BY performance_score DESC
                LIMIT :limit";

        $params[':limit'] = $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $topPerformers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format performance score
        foreach ($topPerformers as &$performer) {
            $performer['performance_score'] = round($performer['performance_score'], 2);
            $performer['avg_rating'] = round($performer['avg_rating'], 2);
        }

        return $topPerformers;
    }

    /**
     * Lấy phân tích chi phí nhân sự
     */
    public function getStaffCostAnalysis($dateFrom = null, $dateTo = null, $filters = [])
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        // Trong thực tế, cần có bảng staff_costs hoặc salaries để tính toán chi phí
        // Đây là phiên bản giả định với chi phí ước tính

        $staffPerformance = $this->getStaffPerformanceDetails($dateFrom, $dateTo, $filters);

        $costAnalysis = [];
        foreach ($staffPerformance as $staff) {
            // Ước tính chi phí dựa trên vai trò và hiệu suất
            $estimatedSalary = 0;
            $estimatedCommission = 0;

            if ($staff['role'] === 'sales') {
                $estimatedSalary = 8000000; // 8 triệu/tháng
                $estimatedCommission = $staff['total_revenue'] * 0.02; // 2% commission
            } elseif ($staff['role'] === 'guide') {
                $estimatedSalary = 6000000; // 6 triệu/tháng
                $estimatedCommission = $staff['booking_count'] * 200000; // 200k/tour
            } else {
                $estimatedSalary = 10000000; // 10 triệu/tháng cho admin/other
                $estimatedCommission = 0;
            }

            $totalCost = $estimatedSalary + $estimatedCommission;
            $roi = $staff['total_revenue'] > 0 ? round((($staff['total_revenue'] - $totalCost) / $totalCost) * 100, 2) : 0;

            $costAnalysis[] = [
                'staff_id' => $staff['user_id'],
                'staff_name' => $staff['full_name'],
                'role' => $staff['role'],
                'estimated_salary' => $estimatedSalary,
                'estimated_commission' => round($estimatedCommission, 2),
                'total_cost' => round($totalCost, 2),
                'generated_revenue' => $staff['total_revenue'],
                'net_profit' => round($staff['total_revenue'] - $totalCost, 2),
                'roi_percentage' => $roi,
                'cost_per_booking' => $staff['booking_count'] > 0 ? round($totalCost / $staff['booking_count'], 2) : 0
            ];
        }

        // Sort by ROI
        usort($costAnalysis, function ($a, $b) {
            return $b['roi_percentage'] - $a['roi_percentage'];
        });

        return $costAnalysis;
    }

    /**
     * Lấy danh sách nhân viên cho filter
     */
    public function getStaffList($role = null)
    {
        $whereConditions = ["1=1"];
        $params = [];

        if ($role) {
            $whereConditions[] = "role = :role";
            $params[':role'] = $role;
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT user_id, full_name, email, role, status 
                FROM users 
                $whereClause 
                ORDER BY full_name ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thống kê theo vai trò
     */
    public function getRoleStatistics($dateFrom = null, $dateTo = null)
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $sql = "SELECT 
                    U.role,
                    COUNT(DISTINCT U.user_id) as total_staff,
                    COUNT(B.id) as total_bookings,
                    SUM(B.final_price) as total_revenue,
                    AVG(B.final_price) as avg_booking_value,
                    COUNT(DISTINCT B.customer_id) as total_customers,
                    AVG(TF.rating) as avg_rating,
                    COUNT(TF.id) as total_feedbacks,
                    -- Role-specific metrics
                    CASE 
                        WHEN U.role = 'sales' THEN COUNT(B.id)
                        WHEN U.role = 'guide' THEN COUNT(B.id)
                        ELSE 0
                    END as role_bookings,
                    CASE 
                        WHEN U.role = 'sales' THEN SUM(B.final_price)
                        WHEN U.role = 'guide' THEN SUM(B.final_price)
                        ELSE 0
                    END as role_revenue
                FROM users U
                LEFT JOIN bookings B ON (
                    (U.role = 'sales' AND B.created_by = U.user_id) OR
                    (U.role = 'guide' AND B.guide_id = U.user_id)
                )
                LEFT JOIN tour_feedbacks TF ON B.id = TF.booking_id
                WHERE B.booking_date BETWEEN :date_from AND :date_to
                GROUP BY U.role
                ORDER BY total_revenue DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
        $roleStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format data
        foreach ($roleStats as &$stat) {
            $stat['avg_booking_value'] = round($stat['avg_booking_value'], 2);
            $stat['avg_rating'] = round($stat['avg_rating'], 2);
            $stat['revenue_per_staff'] = $stat['total_staff'] > 0 ? round($stat['total_revenue'] / $stat['total_staff'], 2) : 0;
            $stat['bookings_per_staff'] = $stat['total_staff'] > 0 ? round($stat['total_bookings'] / $stat['total_staff'], 2) : 0;
        }

        return $roleStats;
    }
}
