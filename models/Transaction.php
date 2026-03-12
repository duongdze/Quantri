<?php

class Transaction extends BaseModel
{
    protected $table = 'transactions';
    protected $columns = [
        'id',
        'booking_id',
        'amount',
        'type',
        'method',
        'description',
        'date',
        'created_at',
        'updated_at'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lấy danh sách giao dịch gần đây
     * @param int $limit Số lượng giao dịch cần lấy
     * @return array
     */
    public function getRecentTransactions($limit = 10)
    {
        $sql = "SELECT 
                    t.*,
                    b.id as booking_id,
                    u.full_name as customer_name
                FROM {$this->table} t
                LEFT JOIN bookings b ON t.booking_id = b.id
                LEFT JOIN users u ON b.customer_id = u.user_id
                ORDER BY t.date DESC 
                LIMIT :limit";
                
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tổng doanh thu theo phương thức thanh toán
     * @return array
     */
    public function getRevenueByPaymentMethod()
    {
        $sql = "SELECT 
                    method,
                    COUNT(*) as transaction_count,
                    SUM(amount) as total_amount
                FROM {$this->table}
                WHERE type = 'income'
                GROUP BY method
                ORDER BY total_amount DESC";
                
        $stmt = self::$pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy doanh thu hàng tháng trong 12 tháng gần nhất
     * @return array
     */
    public function getMonthlyRevenue()
    {
        $sql = "SELECT 
                    DATE_FORMAT(date, '%Y-%m') as month,
                    SUM(amount) as total_revenue
                FROM {$this->table}
                WHERE type = 'income'
                AND date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(date, '%Y-%m')
                ORDER BY month ASC";
                
        $stmt = self::$pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}