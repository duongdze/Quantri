<?php

/**
 * Conversion Report Model - Báo cáo tỷ lệ chuyển đổi booking
 */
class ConversionReport extends BaseModel
{
    protected $table = 'bookings';

    /**
     * Lấy tỷ lệ chuyển đổi booking theo khoảng thời gian
     */
    public function getConversionRate($dateFrom, $dateTo, $filters = [])
    {
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
        if (!empty($filters['source'])) {
            $whereConditions[] = "B.source = :source";
            $params[':source'] = $filters['source'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        // Đếm tổng số inquiries (để tính conversion rate)
        $sqlInquiries = "SELECT COUNT(*) as total_inquiries 
                        FROM booking_inquiries BI 
                        LEFT JOIN tours T ON BI.tour_id = T.id 
                        WHERE BI.created_at BETWEEN :date_from AND :date_to";

        if (!empty($filters['tour_id'])) {
            $sqlInquiries .= " AND BI.tour_id = :tour_id";
        }
        if (!empty($filters['category_id'])) {
            $sqlInquiries .= " AND T.category_id = :category_id";
        }

        try {
            $stmtInquiries = self::$pdo->prepare($sqlInquiries);
            $stmtInquiries->execute($params);
            $inquiries = $stmtInquiries->fetch();
            $totalInquiries = $inquiries['total_inquiries'] ?? 0;
        } catch (PDOException $e) {
            $totalInquiries = 0;
        }

        // Đếm các giai đoạn booking
        $sql = "SELECT 
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN B.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN B.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count,
                    SUM(CASE WHEN B.status = 'deposited' THEN 1 ELSE 0 END) as deposited_count,
                    SUM(CASE WHEN B.status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                    SUM(CASE WHEN B.status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                    SUM(CASE WHEN B.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
                    SUM(B.final_price) as total_value,
                    AVG(B.final_price) as avg_booking_value
                FROM {$this->table} B
                LEFT JOIN tours T ON B.tour_id = T.id
                $whereClause";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetch();

        // Tính tỷ lệ chuyển đổi
        $totalBookings = $data['total_bookings'] ?? 0;
        $conversionRates = [
            'inquiry_to_booking' => $totalInquiries > 0 ? ($totalBookings / $totalInquiries) * 100 : 0,
            'booking_to_confirmation' => $totalBookings > 0 ? (($data['confirmed_count'] ?? 0) / $totalBookings) * 100 : 0,
            'booking_to_deposit' => $totalBookings > 0 ? (($data['deposited_count'] ?? 0) / $totalBookings) * 100 : 0,
            'booking_to_payment' => $totalBookings > 0 ? (($data['paid_count'] ?? 0) / $totalBookings) * 100 : 0,
            'booking_to_completion' => $totalBookings > 0 ? (($data['completed_count'] ?? 0) / $totalBookings) * 100 : 0,
            'confirmation_to_payment' => ($data['confirmed_count'] ?? 0) > 0 ? (($data['paid_count'] ?? 0) / ($data['confirmed_count'] ?? 1)) * 100 : 0,
            'deposit_to_payment' => ($data['deposited_count'] ?? 0) > 0 ? (($data['paid_count'] ?? 0) / ($data['deposited_count'] ?? 1)) * 100 : 0
        ];

        return [
            'total_inquiries' => $totalInquiries,
            'total_bookings' => $totalBookings,
            'total_payments' => ($data['paid_count'] ?? 0) + ($data['completed_count'] ?? 0),
            'conversion_rates' => $conversionRates,
            'stage_counts' => [
                'pending' => $data['pending_count'] ?? 0,
                'confirmed' => $data['confirmed_count'] ?? 0,
                'deposited' => $data['deposited_count'] ?? 0,
                'paid' => $data['paid_count'] ?? 0,
                'completed' => $data['completed_count'] ?? 0,
                'cancelled' => $data['cancelled_count'] ?? 0
            ],
            'total_value' => $data['total_value'] ?? 0,
            'avg_booking_value' => $data['avg_booking_value'] ?? 0
        ];
    }

    /**
     * Lấy tỷ lệ chuyển đổi theo tour
     */
    public function getConversionByTour($dateFrom, $dateTo, $limit = 10)
    {
        $sql = "SELECT 
                    T.id,
                    T.name as tour_name,
                    COUNT(B.id) as total_bookings,
                    SUM(CASE WHEN B.status IN ('paid', 'completed') THEN 1 ELSE 0 END) as successful_bookings,
                    SUM(B.final_price) as total_value,
                    AVG(B.final_price) as avg_value,
                    (SUM(CASE WHEN B.status IN ('paid', 'completed') THEN 1 ELSE 0 END) / COUNT(B.id)) * 100 as conversion_rate
                FROM tours T
                LEFT JOIN {$this->table} B ON T.id = B.tour_id 
                    AND B.booking_date BETWEEN :date_from AND :date_to
                WHERE 1=1
                GROUP BY T.id, T.name
                HAVING total_bookings > 0
                ORDER BY conversion_rate DESC, total_bookings DESC
                LIMIT " . (int)$limit;

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tỷ lệ chuyển đổi theo nguồn
     */
    public function getConversionBySource($dateFrom, $dateTo)
    {
        $sql = "SELECT 
                    B.source,
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN B.status IN ('paid', 'completed') THEN 1 ELSE 0 END) as successful_bookings,
                    SUM(B.final_price) as total_value,
                    AVG(B.final_price) as avg_value,
                    (SUM(CASE WHEN B.status IN ('paid', 'completed') THEN 1 ELSE 0 END) / COUNT(*)) * 100 as conversion_rate
                FROM {$this->table} B
                WHERE B.booking_date BETWEEN :date_from AND :date_to
                    AND B.source IS NOT NULL 
                    AND B.source != ''
                GROUP BY B.source
                ORDER BY conversion_rate DESC";

        try {
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Lấy tỷ lệ chuyển đổi theo danh mục
     */
    public function getConversionByCategory($dateFrom, $dateTo)
    {
        $sql = "SELECT 
                    TC.id,
                    TC.name as category_name,
                    COUNT(B.id) as total_bookings,
                    SUM(CASE WHEN B.status IN ('paid', 'completed') THEN 1 ELSE 0 END) as successful_bookings,
                    SUM(B.final_price) as total_value,
                    AVG(B.final_price) as avg_value,
                    (SUM(CASE WHEN B.status IN ('paid', 'completed') THEN 1 ELSE 0 END) / COUNT(B.id)) * 100 as conversion_rate
                FROM tour_categories TC
                LEFT JOIN tours T ON TC.id = T.category_id
                LEFT JOIN {$this->table} B ON T.id = B.tour_id 
                    AND B.booking_date BETWEEN :date_from AND :date_to
                WHERE 1=1
                GROUP BY TC.id, TC.name
                HAVING total_bookings > 0
                ORDER BY conversion_rate DESC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tỷ lệ chuyển đổi theo tháng
     */
    public function getConversionByMonth($dateFrom, $dateTo)
    {
        $sql = "SELECT 
                    DATE_FORMAT(B.booking_date, '%Y-%m') as month,
                    DATE_FORMAT(B.booking_date, '%m/%Y') as month_label,
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN B.status IN ('paid', 'completed') THEN 1 ELSE 0 END) as successful_bookings,
                    SUM(B.final_price) as total_value,
                    AVG(B.final_price) as avg_value,
                    (SUM(CASE WHEN B.status IN ('paid', 'completed') THEN 1 ELSE 0 END) / COUNT(*)) * 100 as conversion_rate
                FROM {$this->table} B
                WHERE B.booking_date BETWEEN :date_from AND :date_to
                GROUP BY DATE_FORMAT(B.booking_date, '%Y-%m'), DATE_FORMAT(B.booking_date, '%m/%Y')
                ORDER BY month ASC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy funnel analysis - phân tích funnel booking
     */
    public function getFunnelAnalysis($dateFrom, $dateTo, $filters = [])
    {
        $whereConditions = ["created_at BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        // Apply filters
        if (!empty($filters['tour_id'])) {
            $whereConditions[] = "tour_id = :tour_id";
            $params[':tour_id'] = $filters['tour_id'];
        }
        if (!empty($filters['source'])) {
            $whereConditions[] = "source = :source";
            $params[':source'] = $filters['source'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        // Đếm inquiries (đầu funnel)
        $sqlInquiries = "SELECT COUNT(*) as count FROM booking_inquiries $whereClause";
        try {
            $stmt = self::$pdo->prepare($sqlInquiries);
            $stmt->execute($params);
            $inquiries = $stmt->fetch();
        } catch (PDOException $e) {
            $inquiries = ['count' => 0];
        }

        // Đếm bookings theo từng stage
        $sqlBookings = "SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                            SUM(CASE WHEN status = 'deposited' THEN 1 ELSE 0 END) as deposited,
                            SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid,
                            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                        FROM {$this->table} $whereClause";

        $stmt = self::$pdo->prepare($sqlBookings);
        $stmt->execute($params);
        $bookings = $stmt->fetch();

        // Tính conversion rates giữa các stage
        $funnelStages = [
            [
                'stage' => 'Inquiries',
                'count' => $inquiries['count'] ?? 0,
                'conversion_rate' => 100,
                'dropoff_rate' => 0
            ],
            [
                'stage' => 'Bookings',
                'count' => $bookings['total'] ?? 0,
                'conversion_rate' => ($inquiries['count'] ?? 0) > 0 ? (($bookings['total'] ?? 0) / ($inquiries['count'] ?? 1)) * 100 : 0,
                'dropoff_rate' => ($inquiries['count'] ?? 0) > 0 ? ((($inquiries['count'] ?? 0) - ($bookings['total'] ?? 0)) / ($inquiries['count'] ?? 1)) * 100 : 0
            ],
            [
                'stage' => 'Confirmed',
                'count' => $bookings['confirmed'] ?? 0,
                'conversion_rate' => ($bookings['total'] ?? 0) > 0 ? (($bookings['confirmed'] ?? 0) / ($bookings['total'] ?? 1)) * 100 : 0,
                'dropoff_rate' => ($bookings['total'] ?? 0) > 0 ? ((($bookings['total'] ?? 0) - ($bookings['confirmed'] ?? 0)) / ($bookings['total'] ?? 1)) * 100 : 0
            ],
            [
                'stage' => 'Deposited',
                'count' => $bookings['deposited'] ?? 0,
                'conversion_rate' => ($bookings['confirmed'] ?? 0) > 0 ? (($bookings['deposited'] ?? 0) / ($bookings['confirmed'] ?? 1)) * 100 : 0,
                'dropoff_rate' => ($bookings['confirmed'] ?? 0) > 0 ? ((($bookings['confirmed'] ?? 0) - ($bookings['deposited'] ?? 0)) / ($bookings['confirmed'] ?? 1)) * 100 : 0
            ],
            [
                'stage' => 'Paid',
                'count' => $bookings['paid'] ?? 0,
                'conversion_rate' => ($bookings['deposited'] ?? 0) > 0 ? (($bookings['paid'] ?? 0) / ($bookings['deposited'] ?? 1)) * 100 : 0,
                'dropoff_rate' => ($bookings['deposited'] ?? 0) > 0 ? ((($bookings['deposited'] ?? 0) - ($bookings['paid'] ?? 0)) / ($bookings['deposited'] ?? 1)) * 100 : 0
            ],
            [
                'stage' => 'Completed',
                'count' => $bookings['completed'] ?? 0,
                'conversion_rate' => ($bookings['paid'] ?? 0) > 0 ? (($bookings['completed'] ?? 0) / ($bookings['paid'] ?? 1)) * 100 : 0,
                'dropoff_rate' => ($bookings['paid'] ?? 0) > 0 ? ((($bookings['paid'] ?? 0) - ($bookings['completed'] ?? 0)) / ($bookings['paid'] ?? 1)) * 100 : 0
            ]
        ];

        return $funnelStages;
    }

    /**
     * Lấy conversion time analysis - thời gian chuyển đổi
     */
    public function getConversionTimeAnalysis($dateFrom, $dateTo)
    {
        $sql = "SELECT 
                    B.id,
                    B.booking_date,
                    B.status,
                    CASE 
                        WHEN B.status = 'confirmed' AND B.confirmed_at IS NOT NULL 
                            THEN TIMESTAMPDIFF(HOUR, B.booking_date, B.confirmed_at)
                        WHEN B.status = 'deposited' AND B.deposited_at IS NOT NULL 
                            THEN TIMESTAMPDIFF(HOUR, B.booking_date, B.deposited_at)
                        WHEN B.status = 'paid' AND B.paid_at IS NOT NULL 
                            THEN TIMESTAMPDIFF(HOUR, B.booking_date, B.paid_at)
                        WHEN B.status = 'completed' AND B.completed_at IS NOT NULL 
                            THEN TIMESTAMPDIFF(HOUR, B.booking_date, B.completed_at)
                        ELSE NULL
                    END as conversion_hours,
                    CASE 
                        WHEN B.status = 'confirmed' AND B.confirmed_at IS NOT NULL 
                            THEN TIMESTAMPDIFF(DAY, B.booking_date, B.confirmed_at)
                        WHEN B.status = 'deposited' AND B.deposited_at IS NOT NULL 
                            THEN TIMESTAMPDIFF(DAY, B.booking_date, B.deposited_at)
                        WHEN B.status = 'paid' AND B.paid_at IS NOT NULL 
                            THEN TIMESTAMPDIFF(DAY, B.booking_date, B.paid_at)
                        WHEN B.status = 'completed' AND B.completed_at IS NOT NULL 
                            THEN TIMESTAMPDIFF(DAY, B.booking_date, B.completed_at)
                        ELSE NULL
                    END as conversion_days
                FROM {$this->table} B
                WHERE B.booking_date BETWEEN :date_from AND :date_to
                    AND B.status IN ('confirmed', 'deposited', 'paid', 'completed')
                    AND (
                        (B.status = 'confirmed' AND B.confirmed_at IS NOT NULL) OR
                        (B.status = 'deposited' AND B.deposited_at IS NOT NULL) OR
                        (B.status = 'paid' AND B.paid_at IS NOT NULL) OR
                        (B.status = 'completed' AND B.completed_at IS NOT NULL)
                    )";

        try {
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([':date_from' => $dateFrom, ':date_to' => $dateTo]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $results = [];
        }

        // Tính toán thống kê
        $validResults = array_filter($results, function ($row) {
            return $row['conversion_hours'] !== null;
        });

        if (empty($validResults)) {
            return [
                'avg_hours' => 0,
                'avg_days' => 0,
                'median_hours' => 0,
                'median_days' => 0,
                'fastest_hours' => 0,
                'slowest_hours' => 0,
                'distribution' => []
            ];
        }

        $hours = array_column($validResults, 'conversion_hours');
        $days = array_column($validResults, 'conversion_days');

        sort($hours);
        sort($days);

        $count = count($hours);
        $medianHours = $hours[$count / 2] ?? 0;
        $medianDays = $days[$count / 2] ?? 0;

        // Phân phối theo khoảng thời gian
        $distribution = [
            '< 1 hour' => 0,
            '1-6 hours' => 0,
            '6-24 hours' => 0,
            '1-3 days' => 0,
            '3-7 days' => 0,
            '> 7 days' => 0
        ];

        foreach ($hours as $hour) {
            if ($hour < 1) {
                $distribution['< 1 hour']++;
            } elseif ($hour < 6) {
                $distribution['1-6 hours']++;
            } elseif ($hour < 24) {
                $distribution['6-24 hours']++;
            } elseif ($hour < 72) {
                $distribution['1-3 days']++;
            } elseif ($hour < 168) {
                $distribution['3-7 days']++;
            } else {
                $distribution['> 7 days']++;
            }
        }

        return [
            'avg_hours' => array_sum($hours) / $count,
            'avg_days' => array_sum($days) / $count,
            'median_hours' => $medianHours,
            'median_days' => $medianDays,
            'fastest_hours' => min($hours),
            'slowest_hours' => max($hours),
            'distribution' => $distribution,
            'total_converted' => $count
        ];
    }

    /**
     * Lấy conversion rate so với kỳ trước
     */
    public function getPreviousPeriodComparison($dateFrom, $dateTo, $filters = [])
    {
        // Tính khoảng thời gian kỳ trước
        $days = (strtotime($dateTo) - strtotime($dateFrom)) / (60 * 60 * 24) + 1;
        $prevDateTo = date('Y-m-d', strtotime($dateFrom . ' -1 day'));
        $prevDateFrom = date('Y-m-d', strtotime($prevDateTo . ' -' . ($days - 1) . ' days'));

        $currentPeriod = $this->getConversionRate($dateFrom, $dateTo, $filters);
        $previousPeriod = $this->getConversionRate($prevDateFrom, $prevDateTo, $filters);

        // Tính growth
        $growth = [];
        foreach ($currentPeriod['conversion_rates'] as $key => $currentValue) {
            $previousValue = $previousPeriod['conversion_rates'][$key] ?? 0;
            $growth[$key] = $previousValue > 0 ? (($currentValue - $previousValue) / $previousValue) * 100 : ($currentValue > 0 ? 100 : 0);
        }

        return [
            'current_period' => $currentPeriod,
            'previous_period' => $previousPeriod,
            'growth' => $growth,
            'period_info' => [
                'current' => ['from' => $dateFrom, 'to' => $dateTo],
                'previous' => ['from' => $prevDateFrom, 'to' => $prevDateTo]
            ]
        ];
    }
}
