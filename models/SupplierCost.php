<?php

/**
 * Cost Management Model - Quản lý chi phí dựa trên database diagram
 * Sử dụng các bảng: booking_suppliers_assignment, suppliers, transactions, booking_price_adjustments
 */
class SupplierCost extends BaseModel
{
    protected $table = 'booking_suppliers_assignment';

    /**
     * Lấy tổng chi phí theo khoảng thời gian (dựa trên booking_suppliers_assignment)
     */
    public function getTotalCosts($dateFrom, $dateTo, $filters = [])
    {
        $whereConditions = ["B.booking_date BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        // Apply filters
        if (!empty($filters['tour_id'])) {
            $whereConditions[] = "B.tour_id = :tour_id";
            $params[':tour_id'] = $filters['tour_id'];
        }
        if (!empty($filters['supplier_id'])) {
            $whereConditions[] = "BSA.supplier_id = :supplier_id";
            $params[':supplier_id'] = $filters['supplier_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        // Lấy chi phí thực tế từ booking_suppliers_assignment
        $sql = "SELECT 
                    COALESCE(SUM(BSA.quantity * BSA.price), 0) as total_costs,
                    COUNT(BSA.id) as cost_count,
                    AVG(BSA.quantity * BSA.price) as avg_cost,
                    MIN(BSA.quantity * BSA.price) as min_cost,
                    MAX(BSA.quantity * BSA.price) as max_cost
                FROM {$this->table} BSA
                LEFT JOIN bookings B ON BSA.booking_id = B.id
                LEFT JOIN tours T ON B.tour_id = T.id
                {$whereClause}
                LIMIT 10";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi phí theo loại (dựa trên service_type trong booking_suppliers_assignment)
     */
    public function getCostsByType($dateFrom, $dateTo, $filters = [])
    {
        $whereConditions = ["B.booking_date BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        // Apply filters
        if (!empty($filters['tour_id'])) {
            $whereConditions[] = "B.tour_id = :tour_id";
            $params[':tour_id'] = $filters['tour_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        // Phân loại chi phí theo service_type thực tế
        $sql = "SELECT 
                    BSA.service_type as cost_type,
                    SUM(BSA.quantity * BSA.price) as total_amount,
                    COUNT(BSA.id) as count,
                    AVG(BSA.quantity * BSA.price) as avg_amount
                FROM {$this->table} BSA
                LEFT JOIN bookings B ON BSA.booking_id = B.id
                LEFT JOIN tours T ON B.tour_id = T.id
                {$whereClause}
                GROUP BY BSA.service_type
                ORDER BY total_amount DESC
                LIMIT 10";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi phí theo nhà cung cấp (dựa trên suppliers table)
     */
    public function getCostsBySupplier($dateFrom, $dateTo, $limit = 10)
    {
        $sql = "SELECT 
                    S.id,
                    S.name as supplier_name,
                    S.type as supplier_type,
                    SUM(BSA.quantity * BSA.price) as total_costs,
                    COUNT(BSA.id) as cost_count,
                    AVG(BSA.quantity * BSA.price) as avg_cost
                FROM {$this->table} BSA
                LEFT JOIN bookings B ON BSA.booking_id = B.id
                LEFT JOIN suppliers S ON BSA.supplier_id = S.id
                WHERE B.booking_date BETWEEN :date_from AND :date_to
                    AND S.id IS NOT NULL
                GROUP BY S.id, S.name, S.type
                ORDER BY total_costs DESC
                LIMIT " . min((int)$limit, 20);

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi phí theo tour (dựa trên booking_suppliers_assignment)
     */
    public function getCostsByTour($dateFrom, $dateTo, $limit = 15)
    {
        $sql = "SELECT 
                    T.id,
                    T.name as tour_name,
                    TC.name as category_name,
                    SUM(BSA.quantity * BSA.price) as total_costs,
                    COUNT(BSA.id) as cost_count,
                    AVG(BSA.quantity * BSA.price) as avg_cost,
                    COUNT(DISTINCT BSA.booking_id) as booking_count
                FROM {$this->table} BSA
                LEFT JOIN bookings B ON BSA.booking_id = B.id
                LEFT JOIN tours T ON B.tour_id = T.id
                LEFT JOIN tour_categories TC ON T.category_id = TC.id
                WHERE B.booking_date BETWEEN :date_from AND :date_to
                    AND B.tour_id IS NOT NULL
                GROUP BY T.id, T.name, TC.name
                HAVING total_costs > 0
                ORDER BY total_costs DESC
                LIMIT " . min((int)$limit, 20);

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi phí theo tháng (dựa trên booking_suppliers_assignment)
     */
    public function getMonthlyCosts($year, $filters = [])
    {
        $whereConditions = ["YEAR(B.booking_date) = :year"];
        $params = [':year' => $year];

        // Apply filters
        if (!empty($filters['tour_id'])) {
            $whereConditions[] = "B.tour_id = :tour_id";
            $params[':tour_id'] = $filters['tour_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    MONTH(B.booking_date) as month,
                    SUM(BSA.quantity * BSA.price) as total_costs,
                    COUNT(BSA.id) as cost_count,
                    AVG(BSA.quantity * BSA.price) as avg_cost
                FROM {$this->table} BSA
                LEFT JOIN bookings B ON BSA.booking_id = B.id
                {$whereClause}
                GROUP BY MONTH(B.booking_date)
                ORDER BY month
                LIMIT 12";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi phí theo danh mục tour (dựa trên booking_suppliers_assignment)
     */
    public function getCostsByCategory($dateFrom, $dateTo)
    {
        $sql = "SELECT 
                    TC.id,
                    TC.name as category_name,
                    SUM(BSA.quantity * BSA.price) as total_costs,
                    COUNT(BSA.id) as cost_count,
                    AVG(BSA.quantity * BSA.price) as avg_cost,
                    COUNT(DISTINCT BSA.booking_id) as booking_count
                FROM {$this->table} BSA
                LEFT JOIN bookings B ON BSA.booking_id = B.id
                LEFT JOIN tours T ON B.tour_id = T.id
                LEFT JOIN tour_categories TC ON T.category_id = TC.id
                WHERE B.booking_date BETWEEN :date_from AND :date_to
                    AND B.tour_id IS NOT NULL
                    AND TC.id IS NOT NULL
                GROUP BY TC.id, TC.name
                HAVING total_costs > 0
                ORDER BY total_costs DESC
                LIMIT 5";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi phí không gắn với tour (chi phí chung) - Return empty cho database hiện tại
     */
    public function getGeneralCosts($dateFrom, $dateTo)
    {
        // Database hiện tại không có chi phí chung, trả về mảng rỗng
        return [];
    }

    /**
     * Phân tích xu hướng chi phí (dựa trên booking_suppliers_assignment)
     */
    public function getCostTrends($dateFrom, $dateTo, $groupBy = 'day')
    {
        $dateFormat = match ($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        $sql = "SELECT 
                    DATE_FORMAT(B.booking_date, :date_format) as period,
                    DATE_FORMAT(B.booking_date, '%d/%m/%Y') as period_label,
                    SUM(BSA.quantity * BSA.price) as total_costs,
                    COUNT(BSA.id) as cost_count,
                    AVG(BSA.quantity * BSA.price) as avg_cost
                FROM {$this->table} BSA
                LEFT JOIN bookings B ON BSA.booking_id = B.id
                WHERE B.booking_date BETWEEN :date_from AND :date_to
                GROUP BY DATE_FORMAT(B.booking_date, :date_format2), DATE_FORMAT(B.booking_date, '%d/%m/%Y')
                ORDER BY period ASC
                LIMIT 15";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':date_from' => $dateFrom,
            ':date_to' => $dateTo,
            ':date_format' => $dateFormat,
            ':date_format2' => $dateFormat
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi phí theo trạng thái thanh toán - Return empty cho database hiện tại
     */
    public function getCostsByPaymentStatus($dateFrom, $dateTo)
    {
        // Database hiện tại không có trạng thái thanh toán chi phí, trả về mảng rỗng
        return [];
    }

    /**
     * Lấy chi phí sắp đến hạn thanh toán - Return empty cho database hiện tại
     */
    public function getUpcomingPayments($days = 30, $limit = 20)
    {
        // Database hiện tại không có thông tin thanh toán chi phí, trả về mảng rỗng
        return [];
    }

    /**
     * Lấy chi phí quá hạn thanh toán - Return empty cho database hiện tại
     */
    public function getOverduePayments($limit = 20)
    {
        // Database hiện tại không có thông tin thanh toán chi phí, trả về mảng rỗng
        return [];
    }

    /**
     * So sánh chi phí với doanh thu theo tour (dựa trên booking_suppliers_assignment)
     */
    public function getCostVsRevenueByTour($dateFrom, $dateTo, $limit = 15)
    {
        $sql = "SELECT 
                    t.id,
                    t.name as tour_name,
                    tc.name as category_name,
                    COALESCE(SUM(b.final_price), 0) as total_revenue,
                    COALESCE(SUM(bsa.quantity * bsa.price), 0) as total_costs,
                    COALESCE(SUM(b.final_price), 0) - COALESCE(SUM(bsa.quantity * bsa.price), 0) as net_profit,
                    CASE 
                        WHEN COALESCE(SUM(b.final_price), 0) > 0 
                        THEN ((COALESCE(SUM(b.final_price), 0) - COALESCE(SUM(bsa.quantity * bsa.price), 0)) / COALESCE(SUM(b.final_price), 0)) * 100 
                        ELSE 0 
                    END as profit_margin,
                    COUNT(DISTINCT b.id) as booking_count
                FROM tours t
                LEFT JOIN tour_categories tc ON t.category_id = tc.id
                LEFT JOIN bookings b ON t.id = b.tour_id 
                    AND b.booking_date BETWEEN :date_from AND :date_to
                    AND b.status IN ('paid', 'completed')
                LEFT JOIN booking_suppliers_assignment bsa ON b.id = bsa.booking_id
                WHERE t.is_active = 1
                GROUP BY t.id, t.name, tc.name
                HAVING total_revenue > 0 OR total_costs > 0
                ORDER BY net_profit DESC
                LIMIT " . min((int)$limit, 20);

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':date_from' => $dateFrom,
            ':date_to' => $dateTo
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy báo cáo tổng hợp chi phí
     */
    public function getCostSummary($dateFrom, $dateTo, $filters = [])
    {
        $totalCosts = $this->getTotalCosts($dateFrom, $dateTo, $filters);
        $costsByType = $this->getCostsByType($dateFrom, $dateTo, $filters);
        $costsBySupplier = $this->getCostsBySupplier($dateFrom, $dateTo);
        $costsByTour = $this->getCostsByTour($dateFrom, $dateTo);
        $costsByCategory = $this->getCostsByCategory($dateFrom, $dateTo);
        $generalCosts = $this->getGeneralCosts($dateFrom, $dateTo);
        $costTrends = $this->getCostTrends($dateFrom, $dateTo);
        $paymentStatus = $this->getCostsByPaymentStatus($dateFrom, $dateTo);
        $upcomingPayments = $this->getUpcomingPayments();
        $overduePayments = $this->getOverduePayments();

        return [
            'total_costs' => $totalCosts['total_costs'] ?? 0,
            'cost_count' => $totalCosts['cost_count'] ?? 0,
            'avg_cost' => $totalCosts['avg_cost'] ?? 0,
            'min_cost' => $totalCosts['min_cost'] ?? 0,
            'max_cost' => $totalCosts['max_cost'] ?? 0,
            'costs_by_type' => $costsByType,
            'costs_by_supplier' => $costsBySupplier,
            'costs_by_tour' => $costsByTour,
            'costs_by_category' => $costsByCategory,
            'general_costs' => $generalCosts,
            'cost_trends' => $costTrends,
            'payment_status' => $paymentStatus,
            'upcoming_payments' => $upcomingPayments,
            'overdue_payments' => $overduePayments
        ];
    }

    /**
     * Cập nhật trạng thái thanh toán chi phí - Not supported cho database hiện tại
     */
    public function updatePaymentStatus($costId, $status, $paymentDate = null)
    {
        // Database hiện tại không có bảng chi phí riêng, return false
        return false;
    }

    /**
     * Thêm chi phí mới - Not supported cho database hiện tại
     */
    public function addCost($data)
    {
        // Database hiện tại không có bảng chi phí riêng, return false
        return false;
    }
}
