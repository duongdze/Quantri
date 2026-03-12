<?php

/**
 * Debt Report Model - Báo cáo công nợ khách hàng và nhà cung cấp
 */
class DebtReport extends BaseModel
{
    protected $table = 'bookings';
    private $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->pdo = self::$pdo;
    }

    /**
     * Lấy báo cáo công nợ khách hàng
     */
    public function getCustomerDebtReport($dateFrom = null, $dateTo = null, $filters = [])
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
        if (!empty($filters['customer_id'])) {
            $whereConditions[] = "B.customer_id = :customer_id";
            $params[':customer_id'] = $filters['customer_id'];
        }
        if (!empty($filters['status'])) {
            $whereConditions[] = "B.status = :status";
            $params[':status'] = $filters['status'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        // Lấy công nợ theo từng khách hàng
        $sql = "SELECT 
                    COALESCE(B.customer_id, 0) as customer_id,
                    COALESCE(U.full_name, 'Khách vãng lai') as customer_name,
                    COALESCE(U.email, '') as customer_email,
                    COALESCE(U.phone, '') as customer_phone,
                    COUNT(B.id) as total_bookings,
                    SUM(B.final_price) as total_amount,
                    SUM(CASE WHEN B.status IN ('paid', 'completed') THEN B.final_price ELSE 0 END) as paid_amount,
                    SUM(CASE WHEN B.status NOT IN ('paid', 'completed') THEN B.final_price ELSE 0 END) as debt_amount,
                    AVG(B.final_price) as avg_booking_value,
                    MAX(B.booking_date) as last_booking_date
                FROM bookings B
                LEFT JOIN users U ON B.customer_id = U.user_id
                $whereClause
                GROUP BY B.customer_id, U.full_name, U.email, U.phone
                HAVING debt_amount > 0 OR total_amount > 0
                ORDER BY debt_amount DESC, total_amount DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $customerDebts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tính toán các chỉ số tổng quan
        $totalCustomers = count($customerDebts);
        $totalDebtAmount = array_sum(array_column($customerDebts, 'debt_amount'));
        $totalPaidAmount = array_sum(array_column($customerDebts, 'paid_amount'));
        $totalAmount = array_sum(array_column($customerDebts, 'total_amount'));
        $avgDebtPerCustomer = $totalCustomers > 0 ? $totalDebtAmount / $totalCustomers : 0;

        // Phân loại công nợ
        $debtCategories = [
            'under_1m' => 0,
            '1m_5m' => 0,
            '5m_10m' => 0,
            '10m_20m' => 0,
            'over_20m' => 0
        ];

        foreach ($customerDebts as $debt) {
            $amount = $debt['debt_amount'];
            if ($amount < 1000000) {
                $debtCategories['under_1m']++;
            } elseif ($amount < 5000000) {
                $debtCategories['1m_5m']++;
            } elseif ($amount < 10000000) {
                $debtCategories['5m_10m']++;
            } elseif ($amount < 20000000) {
                $debtCategories['10m_20m']++;
            } else {
                $debtCategories['over_20m']++;
            }
        }

        return [
            'summary' => [
                'total_customers' => $totalCustomers,
                'total_debt_amount' => $totalDebtAmount,
                'total_paid_amount' => $totalPaidAmount,
                'total_amount' => $totalAmount,
                'avg_debt_per_customer' => $avgDebtPerCustomer,
                'debt_collection_rate' => $totalAmount > 0 ? (($totalPaidAmount / $totalAmount) * 100) : 0
            ],
            'customer_debts' => $customerDebts,
            'debt_categories' => $debtCategories,
            'overdue_analysis' => $this->getOverdueAnalysis($dateFrom, $dateTo, $filters)
        ];
    }

    /**
     * Lấy báo cáo công nợ nhà cung cấp
     */
    public function getSupplierDebtReport($dateFrom = null, $dateTo = null, $filters = [])
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $whereConditions = ["B.booking_date BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        // Apply filters
        if (!empty($filters['supplier_id'])) {
            $whereConditions[] = "BSA.supplier_id = :supplier_id";
            $params[':supplier_id'] = $filters['supplier_id'];
        }
        if (!empty($filters['service_type'])) {
            $whereConditions[] = "BSA.service_type = :service_type";
            $params[':service_type'] = $filters['service_type'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        // Lấy công nợ theo từng nhà cung cấp
        $sql = "SELECT 
                    S.id as supplier_id,
                    S.name as supplier_name,
                    S.type as supplier_type,
                    S.contact_person,
                    S.phone,
                    S.email,
                    COUNT(DISTINCT BSA.booking_id) as total_bookings,
                    SUM(BSA.quantity * BSA.price) as total_amount,
                    SUM(BSA.quantity * BSA.price) as debt_amount,
                    AVG(BSA.price) as avg_service_price,
                    MAX(B.booking_date) as last_booking_date,
                    GROUP_CONCAT(DISTINCT BSA.service_type) as service_types
                FROM suppliers S
                LEFT JOIN booking_suppliers_assignment BSA ON S.id = BSA.supplier_id
                LEFT JOIN bookings B ON BSA.booking_id = B.id
                $whereClause
                GROUP BY S.id, S.name, S.type, S.contact_person, S.phone, S.email
                HAVING total_amount > 0
                ORDER BY debt_amount DESC, total_amount DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $supplierDebts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tính toán các chỉ số tổng quan
        $totalSuppliers = count($supplierDebts);
        $totalDebtAmount = array_sum(array_column($supplierDebts, 'debt_amount'));
        $totalAmount = array_sum(array_column($supplierDebts, 'total_amount'));
        $avgDebtPerSupplier = $totalSuppliers > 0 ? $totalDebtAmount / $totalSuppliers : 0;

        // Phân loại theo loại nhà cung cấp
        $supplierTypes = [];
        foreach ($supplierDebts as $debt) {
            $type = $debt['supplier_type'] ?: 'other';
            if (!isset($supplierTypes[$type])) {
                $supplierTypes[$type] = [
                    'count' => 0,
                    'total_debt' => 0,
                    'avg_debt' => 0
                ];
            }
            $supplierTypes[$type]['count']++;
            $supplierTypes[$type]['total_debt'] += $debt['debt_amount'];
        }

        // Tính avg debt cho mỗi type
        foreach ($supplierTypes as $type => &$data) {
            $data['avg_debt'] = $data['count'] > 0 ? $data['total_debt'] / $data['count'] : 0;
        }

        return [
            'summary' => [
                'total_suppliers' => $totalSuppliers,
                'total_debt_amount' => $totalDebtAmount,
                'total_amount' => $totalAmount,
                'avg_debt_per_supplier' => $avgDebtPerSupplier
            ],
            'supplier_debts' => $supplierDebts,
            'supplier_types' => $supplierTypes,
            'service_type_analysis' => $this->getServiceTypeAnalysis($dateFrom, $dateTo, $filters)
        ];
    }

    /**
     * Phân tích công nợ quá hạn
     */
    private function getOverdueAnalysis($dateFrom, $dateTo, $filters = [])
    {
        // Database hiện tại không có due_date column cho payments
        // Sử dụng booking_date + 30 ngày làm due date mặc định
        $sql = "SELECT 
                    COUNT(*) as overdue_bookings,
                    SUM(B.final_price) as overdue_amount,
                    AVG(DATEDIFF(NOW(), B.booking_date)) as avg_days_overdue
                FROM bookings B
                WHERE B.booking_date BETWEEN :date_from AND :date_to
                AND B.status NOT IN ('paid', 'completed', 'cancelled')
                AND DATEDIFF(NOW(), B.booking_date) > 30";

        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        if (!empty($filters['customer_id'])) {
            $sql .= " AND B.customer_id = :customer_id";
            $params[':customer_id'] = $filters['customer_id'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Phân tích theo loại dịch vụ
     */
    private function getServiceTypeAnalysis($dateFrom, $dateTo, $filters = [])
    {
        $whereConditions = ["B.booking_date BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        if (!empty($filters['supplier_id'])) {
            $whereConditions[] = "BSA.supplier_id = :supplier_id";
            $params[':supplier_id'] = $filters['supplier_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    BSA.service_type,
                    COUNT(DISTINCT BSA.supplier_id) as supplier_count,
                    COUNT(DISTINCT BSA.booking_id) as booking_count,
                    SUM(BSA.quantity * BSA.price) as total_amount,
                    AVG(BSA.price) as avg_price
                FROM booking_suppliers_assignment BSA
                LEFT JOIN bookings B ON BSA.booking_id = B.id
                $whereClause
                GROUP BY BSA.service_type
                ORDER BY total_amount DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết công nợ theo khách hàng
     */
    public function getCustomerDebtDetails($customerId, $dateFrom = null, $dateTo = null)
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $sql = "SELECT 
                    B.id,
                    B.booking_date,
                    B.departure_date,
                    B.final_price,
                    B.status,
                    B.notes,
                    T.name as tour_name,
                    B.adults + B.children + B.infants as total_customers,
                    DATEDIFF(NOW(), B.booking_date) as days_overdue,
                    CASE 
                        WHEN B.status IN ('paid', 'completed') THEN 'Đã thanh toán'
                        WHEN DATEDIFF(NOW(), B.booking_date) > 30 THEN 'Quá hạn'
                        ELSE 'Chưa đến hạn'
                    END as payment_status
                FROM bookings B
                LEFT JOIN tours T ON B.tour_id = T.id
                WHERE B.customer_id = :customer_id
                AND B.booking_date BETWEEN :date_from AND :date_to
                ORDER BY B.booking_date DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':customer_id' => $customerId,
            ':date_from' => $dateFrom,
            ':date_to' => $dateTo
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết công nợ theo nhà cung cấp
     */
    public function getSupplierDebtDetails($supplierId, $dateFrom = null, $dateTo = null)
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $sql = "SELECT 
                    BSA.id,
                    BSA.booking_id,
                    BSA.service_type,
                    BSA.quantity,
                    BSA.price,
                    BSA.notes,
                    BSA.quantity * BSA.price as total_amount,
                    B.booking_date,
                    B.departure_date,
                    B.status as booking_status,
                    T.name as tour_name
                FROM booking_suppliers_assignment BSA
                LEFT JOIN bookings B ON BSA.booking_id = B.id
                LEFT JOIN tours T ON B.tour_id = T.id
                WHERE BSA.supplier_id = :supplier_id
                AND B.booking_date BETWEEN :date_from AND :date_to
                ORDER BY B.booking_date DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':supplier_id' => $supplierId,
            ':date_from' => $dateFrom,
            ':date_to' => $dateTo
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy báo cáo thanh toán theo khoảng thời gian
     */
    public function getPaymentReport($dateFrom = null, $dateTo = null, $type = 'customer')
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        if ($type === 'customer') {
            // Báo cáo thanh toán từ khách hàng
            $sql = "SELECT 
                        DATE(B.updated_at) as payment_date,
                        COUNT(*) as payment_count,
                        SUM(B.final_price) as total_amount,
                        AVG(B.final_price) as avg_amount
                    FROM bookings B
                    WHERE B.updated_at BETWEEN :date_from AND :date_to
                    AND B.status IN ('paid', 'completed')
                    GROUP BY DATE(B.updated_at)
                    ORDER BY payment_date DESC";
        } else {
            // Báo cáo thanh toán cho nhà cung cấp (dựa trên booking_suppliers_assignment)
            $sql = "SELECT 
                        DATE(BSA.created_at) as payment_date,
                        COUNT(*) as payment_count,
                        SUM(BSA.quantity * BSA.price) as total_amount,
                        AVG(BSA.price) as avg_amount
                    FROM booking_suppliers_assignment BSA
                    WHERE BSA.created_at BETWEEN :date_from AND :date_to
                    GROUP BY DATE(BSA.created_at)
                    ORDER BY payment_date DESC";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
