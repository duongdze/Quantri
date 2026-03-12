<?php
require_once 'SupplierCost.php';

/**
 * Financial Report Model - Báo cáo tài chính
 */
class FinancialReport extends BaseModel
{
    protected $table = 'bookings';
    private $supplierCost;

    public function __construct()
    {
        parent::__construct();
        $this->supplierCost = new SupplierCost();
    }

    /**
     * Lấy báo cáo tài chính tổng quan theo khoảng thời gian 
     */
    public function getFinancialSummary($dateFrom = null, $dateTo = null, $filters = [], $skipGrowth = false)
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $whereConditions = ["B.booking_date BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        // Apply filters
        if (!empty($filters['tour_id'])) {
            $whereConditions[] = "B.tour_id = :tour_id";
            $params[':tour_id'] = $filters['tour_id'];
        }
        if (!empty($filters['category_id'])) {
            $whereConditions[] = "T.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        // Doanh thu từ bookings đã hoàn thành
        $sql = "SELECT 
                    SUM(B.final_price) as total_revenue,
                    COUNT(B.id) as total_bookings,
                    AVG(B.final_price) as avg_booking_value
                FROM bookings B 
                LEFT JOIN tours T ON B.tour_id = T.id
                $whereClause 
                AND B.status IN ('completed', 'paid', 'hoan_tat', 'da_thanh_toan', 'da_coc')";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        $revenueData = $stmt->fetch();

        // Chi phí thực tế từ nhà cung cấp
        $costData = $this->supplierCost->getTotalCosts($dateFrom, $dateTo, $filters);
        $totalExpense = $costData['total_costs'] ?? 0;

        // Tính toán lợi nhuận và các chỉ số
        $totalRevenue = $revenueData['total_revenue'] ?? 0;
        $profit = $totalRevenue - $totalExpense;
        $profitMargin = $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0;

        // Lấy dữ liệu kỳ trước để tính growth (chỉ khi không skip)
        $revenueGrowth = 0;
        $expenseGrowth = 0;
        $profitGrowth = 0;

        if (!$skipGrowth) {
            $previousPeriodData = $this->getPreviousPeriodData($dateFrom, $dateTo, $filters);
            $revenueGrowth = $this->calculateGrowth($totalRevenue, $previousPeriodData['total_revenue']);
            $expenseGrowth = $this->calculateGrowth($totalExpense, $previousPeriodData['total_expense']);
            $profitGrowth = $this->calculateGrowth($profit, $previousPeriodData['profit']);
        }

        return [
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'profit' => $profit,
            'profit_margin' => $profitMargin,
            'total_bookings' => $revenueData['total_bookings'] ?? 0,
            'avg_booking_value' => $revenueData['avg_booking_value'] ?? 0,
            'cost_count' => $costData['cost_count'] ?? 0,
            'avg_cost' => $costData['avg_cost'] ?? 0,
            'revenue_growth' => $revenueGrowth,
            'expense_growth' => $expenseGrowth,
            'profit_growth' => $profitGrowth
        ];
    }

    /**
     * Lấy chi phí tổng hợp (dựa trên booking_suppliers_assignment)
     */
    public function getTotalExpenses($dateFrom, $dateTo, $tourId = null)
    {
        $whereConditions = ["B.booking_date BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        if ($tourId) {
            $whereConditions[] = "B.tour_id = :tour_id";
            $params[':tour_id'] = $tourId;
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        // Chi phí thực tế từ booking_suppliers_assignment
        $sql = "SELECT 
                    COALESCE(SUM(BSA.quantity * BSA.price), 0) as total_expense
                FROM booking_suppliers_assignment BSA
                LEFT JOIN bookings B ON BSA.booking_id = B.id 
                {$whereClause} 
                AND B.status IN ('completed', 'paid', 'da_coc')";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total_expense'] ?? 0;
    }

    /**
     * Lấy báo cáo chi tiết theo từng tour
     */
    public function getTourFinancials($dateFrom = null, $dateTo = null, $filters = [])
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $whereConditions = ["B.booking_date BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        // Apply filters
        if (!empty($filters['tour_id'])) {
            $whereConditions[] = "B.tour_id = :tour_id";
            $params[':tour_id'] = $filters['tour_id'];
        }
        if (!empty($filters['category_id'])) {
            $whereConditions[] = "T.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    T.id as tour_id,
                    T.name as tour_name,
                    TC.name as category_name,
                    COUNT(B.id) as booking_count,
                    COALESCE(SUM(B.final_price), 0) as revenue
                FROM tours T
                LEFT JOIN tour_categories TC ON T.category_id = TC.id
                LEFT JOIN bookings B ON T.id = B.tour_id
                {$whereClause}
                AND B.status IN ('completed', 'paid', 'hoan_tat', 'da_thanh_toan', 'da_coc')
                GROUP BY T.id, T.name, TC.name
                HAVING revenue > 0
                ORDER BY revenue DESC
                LIMIT 10";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        $tours = $stmt->fetchAll();

        // Lấy chi phí thực tế cho từng tour
        foreach ($tours as &$tour) {
            $tourFilters = array_merge($filters, ['tour_id' => $tour['tour_id']]);
            $costData = $this->supplierCost->getTotalCosts($dateFrom, $dateTo, $tourFilters);

            $tour['expense'] = $costData['total_costs'] ?? 0;
            $tour['profit'] = $tour['revenue'] - $tour['expense'];
            $tour['profit_margin'] = $tour['revenue'] > 0 ? ($tour['profit'] / $tour['revenue']) * 100 : 0;
            $tour['cost_count'] = $costData['cost_count'] ?? 0;
        }

        // Sắp xếp lại theo lợi nhuận
        usort($tours, function ($a, $b) {
            return $b['profit'] - $a['profit'];
        });

        return $tours;
    }

    /**
     * Lấy dữ liệu theo tháng cho biểu đồ
     */
    public function getMonthlyData($year = null, $filters = [])
    {
        $year = $year ?? date('Y');

        $whereConditions = ["YEAR(B.booking_date) = :year"];
        $params = [':year' => $year];

        // Apply filters
        if (!empty($filters['tour_id'])) {
            $whereConditions[] = "B.tour_id = :tour_id";
            $params[':tour_id'] = $filters['tour_id'];
        }
        if (!empty($filters['category_id'])) {
            $whereConditions[] = "T.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    MONTH(B.booking_date) as month,
                    COALESCE(SUM(B.final_price), 0) as revenue,
                    COALESCE(COUNT(B.id), 0) as bookings
                FROM bookings B 
                LEFT JOIN tours T ON B.tour_id = T.id
                {$whereClause} 
                AND B.status IN ('completed', 'paid', 'hoan_tat', 'da_thanh_toan', 'da_coc')
                GROUP BY MONTH(B.booking_date)
                ORDER BY month
                LIMIT 12";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        $revenueData = $stmt->fetchAll();

        // Lấy chi phí thực tế theo tháng
        $costFilters = $filters;
        $costFilters['year'] = $year;
        $expenseData = $this->supplierCost->getMonthlyCosts($year, $costFilters);

        // Gộp dữ liệu revenue và expense
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $revenue = 0;
            $expense = 0;
            $bookings = 0;

            foreach ($revenueData as $data) {
                if ($data['month'] == $month) {
                    $revenue = $data['revenue'];
                    $bookings = $data['bookings'];
                    break;
                }
            }

            foreach ($expenseData as $data) {
                if ($data['month'] == $month) {
                    $expense = $data['total_costs'];
                    break;
                }
            }

            $profit = $revenue - $expense;
            $profitMargin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            $monthlyData[] = [
                'month' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'revenue' => $revenue,
                'expense' => $expense,
                'profit' => $profit,
                'profit_margin' => $profitMargin,
                'bookings' => $bookings
            ];
        }

        return $monthlyData;
    }

    /**
     * Lấy dữ liệu kỳ trước để so sánh
     */
    private function getPreviousPeriodData($dateFrom, $dateTo, $filters = [])
    {
        // Tính khoảng thời gian kỳ trước
        $days = (strtotime($dateTo) - strtotime($dateFrom)) / (60 * 60 * 24) + 1;
        $prevDateTo = date('Y-m-d', strtotime($dateFrom . ' -1 day'));
        $prevDateFrom = date('Y-m-d', strtotime($prevDateTo . ' -' . ($days - 1) . ' days'));

        // Skip growth calculation để tránh vòng lặp vô hạn
        return $this->getFinancialSummary($prevDateFrom, $prevDateTo, $filters, true);
    }

    /**
     * Tính toán tỷ lệ tăng trưởng
     */
    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return (($current - $previous) / $previous) * 100;
    }
}
