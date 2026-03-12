<?php

/**
 * Filter Service - Xử lý bộ lọc nâng cao cho báo cáo
 */
class FilterService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = BaseModel::getPdo();
    }

    /**
     * Lấy danh sách tours với filter
     */
    public function getFilteredTours($filters = [])
    {
        $whereConditions = [];
        $params = [];

        // Filter theo category
        if (!empty($filters['category_id'])) {
            $whereConditions[] = "T.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        // Filter theo price range
        if (!empty($filters['price_min'])) {
            $whereConditions[] = "T.base_price >= :price_min";
            $params[':price_min'] = $filters['price_min'];
        }
        if (!empty($filters['price_max'])) {
            $whereConditions[] = "T.base_price <= :price_max";
            $params[':price_max'] = $filters['price_max'];
        }

        // Filter theo keyword
        if (!empty($filters['keyword'])) {
            $whereConditions[] = "(T.name LIKE :keyword OR T.description LIKE :keyword)";
            $params[':keyword'] = '%' . $filters['keyword'] . '%';
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $sql = "SELECT 
                    T.id,
                    T.name,
                    T.category_id,
                    TC.name as category_name,
                    T.base_price,
                    T.description
                FROM tours T
                LEFT JOIN tour_categories TC ON T.category_id = TC.id
                $whereClause
                ORDER BY T.name ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách categories
     */
    public function getCategories()
    {
        $sql = "SELECT id, name FROM tour_categories ORDER BY name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách departure locations
     * Note: Column không tồn tại trong database hiện tại
     */
    public function getDepartureLocations()
    {
        // Database hiện tại không có departure_location column
        return [];
    }

    /**
     * Lấy price ranges cho filter
     */
    public function getPriceRanges()
    {
        $sql = "SELECT 
                    MIN(base_price) as min_price,
                    MAX(base_price) as max_price,
                    AVG(base_price) as avg_price
                FROM tours";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();

        if (!$result || $result['min_price'] === null) {
            return [
                'min' => 0,
                'max' => 10000000,
                'ranges' => [
                    ['min' => 0, 'max' => 1000000, 'label' => 'Dưới 1 triệu'],
                    ['min' => 1000000, 'max' => 3000000, 'label' => '1-3 triệu'],
                    ['min' => 3000000, 'max' => 5000000, 'label' => '3-5 triệu'],
                    ['min' => 5000000, 'max' => 10000000, 'label' => '5-10 triệu'],
                    ['min' => 10000000, 'max' => 999999999, 'label' => 'Trên 10 triệu']
                ]
            ];
        }

        $min = $result['min_price'];
        $max = $result['max_price'];
        $range = ($max - $min) / 5;

        $ranges = [];
        for ($i = 0; $i < 5; $i++) {
            $rangeMin = $min + ($range * $i);
            $rangeMax = $i == 4 ? $max : $min + ($range * ($i + 1));

            $ranges[] = [
                'min' => $rangeMin,
                'max' => $rangeMax,
                'label' => number_format($rangeMin) . ' - ' . number_format($rangeMax) . ' VNĐ'
            ];
        }

        return [
            'min' => $min,
            'max' => $max,
            'avg' => $result['avg_price'],
            'ranges' => $ranges
        ];
    }

    /**
     * Lấy duration ranges cho filter
     */
    public function getDurationRanges()
    {
        // Database hiện tại không có duration column, trả về giá trị mặc định
        return [
            'min' => 1,
            'max' => 30,
            'ranges' => [
                ['min' => 1, 'max' => 3, 'label' => '1-3 ngày'],
                ['min' => 4, 'max' => 7, 'label' => '4-7 ngày'],
                ['min' => 8, 'max' => 14, 'label' => '8-14 ngày'],
                ['min' => 15, 'max' => 30, 'label' => '15-30 ngày']
            ],
            'avg' => 7
        ];
    }

    /**
     * Lấy booking sources cho filter
     */
    public function getBookingSources()
    {
        $sql = "SELECT DISTINCT source 
                FROM bookings 
                WHERE source IS NOT NULL 
                AND source != ''
                ORDER BY source ASC";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $sources = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            $sources = [];
        }

        // Thêm các sources mặc định nếu không có data
        if (empty($sources)) {
            return [
                ['value' => 'website', 'label' => 'Website'],
                ['value' => 'zalo', 'label' => 'Zalo'],
                ['value' => 'phone', 'label' => 'Điện thoại'],
                ['value' => 'walk_in', 'label' => 'Walk-in'],
                ['value' => 'facebook', 'label' => 'Facebook'],
                ['value' => 'referral', 'label' => 'Giới thiệu']
            ];
        }

        $result = [];
        foreach ($sources as $source) {
            $result[] = [
                'value' => $source,
                'label' => ucfirst(str_replace('_', ' ', $source))
            ];
        }

        return $result;
    }

    /**
     * Lấy booking statuses cho filter
     */
    public function getBookingStatuses()
    {
        return [
            ['value' => 'pending', 'label' => 'Chờ xác nhận', 'color' => 'warning'],
            ['value' => 'confirmed', 'label' => 'Đã xác nhận', 'color' => 'info'],
            ['value' => 'deposited', 'label' => 'Đã đặt cọc', 'color' => 'primary'],
            ['value' => 'paid', 'label' => 'Đã thanh toán', 'color' => 'success'],
            ['value' => 'completed', 'label' => 'Hoàn thành', 'color' => 'success'],
            ['value' => 'cancelled', 'label' => 'Đã hủy', 'color' => 'danger']
        ];
    }

    /**
     * Lấy feedback types cho filter
     */
    public function getFeedbackTypes()
    {
        return [
            ['value' => 'tour', 'label' => 'Tour', 'icon' => 'fa-map-marked-alt'],
            ['value' => 'supplier', 'label' => 'Nhà cung cấp', 'icon' => 'fa-truck'],
            ['value' => 'guide', 'label' => 'Hướng dẫn viên', 'icon' => 'fa-user-tie']
        ];
    }

    /**
     * Lấy sentiment options cho filter
     */
    public function getSentimentOptions()
    {
        return [
            ['value' => 'positive', 'label' => 'Tích cực', 'color' => 'success', 'icon' => 'fa-smile'],
            ['value' => 'neutral', 'label' => 'Trung lập', 'color' => 'warning', 'icon' => 'fa-meh'],
            ['value' => 'negative', 'label' => 'Tiêu cực', 'color' => 'danger', 'icon' => 'fa-frown']
        ];
    }

    /**
     * Lấy rating options cho filter
     */
    public function getRatingOptions()
    {
        return [
            ['value' => 5, 'label' => '5 sao', 'stars' => 5],
            ['value' => 4, 'label' => '4 sao trở lên', 'stars' => 4],
            ['value' => 3, 'label' => '3 sao trở lên', 'stars' => 3],
            ['value' => 2, 'label' => '2 sao trở lên', 'stars' => 2],
            ['value' => 1, 'label' => '1 sao', 'stars' => 1]
        ];
    }

    /**
     * Lấy date range presets
     */
    public function getDateRangePresets()
    {
        return [
            ['key' => 'today', 'label' => 'Hôm nay', 'start' => date('Y-m-d'), 'end' => date('Y-m-d')],
            ['key' => 'yesterday', 'label' => 'Hôm qua', 'start' => date('Y-m-d', strtotime('-1 day')), 'end' => date('Y-m-d', strtotime('-1 day'))],
            ['key' => 'this_week', 'label' => 'Tuần này', 'start' => date('Y-m-d', strtotime('monday this week')), 'end' => date('Y-m-d')],
            ['key' => 'last_week', 'label' => 'Tuần trước', 'start' => date('Y-m-d', strtotime('monday last week')), 'end' => date('Y-m-d', strtotime('sunday last week'))],
            ['key' => 'this_month', 'label' => 'Tháng này', 'start' => date('Y-m-01'), 'end' => date('Y-m-d')],
            ['key' => 'last_month', 'label' => 'Tháng trước', 'start' => date('Y-m-01', strtotime('-1 month')), 'end' => date('Y-m-t', strtotime('-1 month'))],
            ['key' => 'this_quarter', 'label' => 'Quý này', 'start' => date('Y-m-d', strtotime('first day of this quarter')), 'end' => date('Y-m-d')],
            ['key' => 'last_quarter', 'label' => 'Quý trước', 'start' => date('Y-m-d', strtotime('first day of previous quarter')), 'end' => date('Y-m-d', strtotime('last day of previous quarter'))],
            ['key' => 'this_year', 'label' => 'Năm nay', 'start' => date('Y-01-01'), 'end' => date('Y-m-d')],
            ['key' => 'last_year', 'label' => 'Năm trước', 'start' => date('Y-01-01', strtotime('-1 year')), 'end' => date('Y-12-31', strtotime('-1 year'))],
            ['key' => 'last_7_days', 'label' => '7 ngày qua', 'start' => date('Y-m-d', strtotime('-6 days')), 'end' => date('Y-m-d')],
            ['key' => 'last_30_days', 'label' => '30 ngày qua', 'start' => date('Y-m-d', strtotime('-29 days')), 'end' => date('Y-m-d')],
            ['key' => 'last_90_days', 'label' => '90 ngày qua', 'start' => date('Y-m-d', strtotime('-89 days')), 'end' => date('Y-m-d')]
        ];
    }

    /**
     * Xây dựng WHERE clause từ filters
     */
    public function buildWhereClause($filters, $prefix = '')
    {
        $whereConditions = [];
        $params = [];

        foreach ($filters as $key => $value) {
            if (empty($value)) continue;

            $field = $prefix . $key;

            switch ($key) {
                case 'date_from':
                    $whereConditions[] = "$field >= :$key";
                    $params[":$key"] = $value;
                    break;

                case 'date_to':
                    $whereConditions[] = "$field <= :$key";
                    $params[":$key"] = $value;
                    break;

                case 'tour_id':
                    if (is_array($value)) {
                        $placeholders = str_repeat('?,', count($value) - 1) . '?';
                        $whereConditions[] = "$field IN ($placeholders)";
                        foreach ($value as $i => $id) {
                            $params[":{$key}_$i"] = $id;
                        }
                    } else {
                        $whereConditions[] = "$field = :$key";
                        $params[":$key"] = $value;
                    }
                    break;

                case 'status':
                    if (is_array($value)) {
                        $placeholders = str_repeat('?,', count($value) - 1) . '?';
                        $whereConditions[] = "$field IN ($placeholders)";
                        foreach ($value as $i => $status) {
                            $params[":{$key}_$i"] = $status;
                        }
                    } else {
                        $whereConditions[] = "$field = :$key";
                        $params[":$key"] = $value;
                    }
                    break;

                case 'price_min':
                    $whereConditions[] = "$field >= :$key";
                    $params[":$key"] = $value;
                    break;

                case 'price_max':
                    $whereConditions[] = "$field <= :$key";
                    $params[":$key"] = $value;
                    break;

                case 'keyword':
                    $whereConditions[] = "($field LIKE :{$key}_name OR $field LIKE :{$key}_desc)";
                    $params[":{$key}_name"] = "%$value%";
                    $params[":{$key}_desc"] = "%$value%";
                    break;

                default:
                    if (is_array($value)) {
                        $placeholders = str_repeat('?,', count($value) - 1) . '?';
                        $whereConditions[] = "$field IN ($placeholders)";
                        foreach ($value as $i => $val) {
                            $params[":{$key}_$i"] = $val;
                        }
                    } else {
                        $whereConditions[] = "$field = :$key";
                        $params[":$key"] = $value;
                    }
                    break;
            }
        }

        return [
            'where' => !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '',
            'params' => $params
        ];
    }

    /**
     * Validate filter parameters
     */
    public function validateFilters($filters, $type = 'general')
    {
        $errors = [];

        // Validate date range
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            if (strtotime($filters['date_from']) > strtotime($filters['date_to'])) {
                $errors['date_range'] = 'Ngày bắt đầu không thể lớn hơn ngày kết thúc';
            }
        }

        // Validate price range
        if (!empty($filters['price_min']) && !empty($filters['price_max'])) {
            if ($filters['price_min'] > $filters['price_max']) {
                $errors['price_range'] = 'Giá tối thiểu không thể lớn hơn giá tối đa';
            }
        }

        // Type-specific validations
        switch ($type) {
            case 'financial':
                if (!empty($filters['profit_margin_min']) && !empty($filters['profit_margin_max'])) {
                    if ($filters['profit_margin_min'] > $filters['profit_margin_max']) {
                        $errors['profit_margin_range'] = 'Tỷ suất lợi nhuận tối thiểu không thể lớn hơn tỷ suất tối đa';
                    }
                }
                break;

            case 'booking':
                if (!empty($filters['customer_count_min']) && !empty($filters['customer_count_max'])) {
                    if ($filters['customer_count_min'] > $filters['customer_count_max']) {
                        $errors['customer_count_range'] = 'Số khách tối thiểu không thể lớn hơn số khách tối đa';
                    }
                }
                break;
        }

        return $errors;
    }

    /**
     * Get filter summary for display
     */
    public function getFilterSummary($filters, $type = 'general')
    {
        $summary = [];

        foreach ($filters as $key => $value) {
            if (empty($value)) continue;

            switch ($key) {
                case 'date_from':
                case 'date_to':
                    $summary[$key] = [
                        'label' => $key === 'date_from' ? 'Từ ngày' : 'Đến ngày',
                        'value' => date('d/m/Y', strtotime($value)),
                        'type' => 'date'
                    ];
                    break;

                case 'tour_id':
                    if (is_array($value)) {
                        $summary[$key] = [
                            'label' => 'Tours',
                            'value' => count($value) . ' tours được chọn',
                            'type' => 'array'
                        ];
                    } else {
                        // Get tour name
                        $sql = "SELECT name FROM tours WHERE id = :id";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute([':id' => $value]);
                        $tourName = $stmt->fetchColumn();

                        $summary[$key] = [
                            'label' => 'Tour',
                            'value' => $tourName ?: 'Tour #' . $value,
                            'type' => 'tour'
                        ];
                    }
                    break;

                case 'status':
                    if (is_array($value)) {
                        $summary[$key] = [
                            'label' => 'Trạng thái',
                            'value' => implode(', ', array_map('ucfirst', $value)),
                            'type' => 'array'
                        ];
                    } else {
                        $statusLabels = [
                            'pending' => 'Chờ xác nhận',
                            'confirmed' => 'Đã xác nhận',
                            'deposited' => 'Đã đặt cọc',
                            'paid' => 'Đã thanh toán',
                            'completed' => 'Hoàn thành',
                            'cancelled' => 'Đã hủy'
                        ];

                        $summary[$key] = [
                            'label' => 'Trạng thái',
                            'value' => $statusLabels[$value] ?? ucfirst($value),
                            'type' => 'status'
                        ];
                    }
                    break;

                case 'rating':
                    $summary[$key] = [
                        'label' => 'Đánh giá',
                        'value' => $value . ' sao',
                        'type' => 'rating'
                    ];
                    break;

                case 'price_min':
                case 'price_max':
                    $label = $key === 'price_min' ? 'Giá từ' : 'Giá đến';
                    $summary[$key] = [
                        'label' => $label,
                        'value' => number_format($value) . ' VNĐ',
                        'type' => 'price'
                    ];
                    break;

                case 'keyword':
                    $summary[$key] = [
                        'label' => 'Từ khóa',
                        'value' => $value,
                        'type' => 'keyword'
                    ];
                    break;

                default:
                    $summary[$key] = [
                        'label' => ucfirst(str_replace('_', ' ', $key)),
                        'value' => is_array($value) ? implode(', ', $value) : $value,
                        'type' => 'text'
                    ];
                    break;
            }
        }

        return $summary;
    }
}
