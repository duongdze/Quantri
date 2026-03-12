<?php

/**
 * Feedback Report Model - Báo cáo phản hồi và đánh giá chất lượng
 */
class FeedbackReport extends BaseModel
{
    protected $table = 'tour_feedbacks';
    private $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->pdo = self::$pdo;
    }

    /**
     * Lấy báo cáo tổng quan về phản hồi
     */
    public function getFeedbackSummary($dateFrom = null, $dateTo = null, $filters = [])
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $whereConditions = ["TF.created_at BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        // Apply filters
        if (!empty($filters['tour_id'])) {
            $whereConditions[] = "TF.tour_id = :tour_id";
            $params[':tour_id'] = $filters['tour_id'];
        }
        if (!empty($filters['rating'])) {
            $whereConditions[] = "TF.rating = :rating";
            $params[':rating'] = $filters['rating'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        // Thống kê tổng quan
        $sql = "SELECT 
                    COUNT(TF.id) as total_feedbacks,
                    AVG(TF.rating) as avg_rating,
                    MIN(TF.rating) as min_rating,
                    MAX(TF.rating) as max_rating,
                    SUM(CASE WHEN TF.rating >= 4 THEN 1 ELSE 0 END) as positive_feedbacks,
                    SUM(CASE WHEN TF.rating <= 2 THEN 1 ELSE 0 END) as negative_feedbacks,
                    SUM(CASE WHEN TF.rating = 3 THEN 1 ELSE 0 END) as neutral_feedbacks,
                    COUNT(DISTINCT TF.tour_id) as tours_with_feedback,
                    COUNT(DISTINCT TF.user_id) as unique_customers
                FROM tour_feedbacks TF
                $whereClause";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $summary = $stmt->fetch();

        // Tính toán các chỉ số
        $totalFeedbacks = $summary['total_feedbacks'] ?? 0;
        $positiveFeedbacks = $summary['positive_feedbacks'] ?? 0;
        $negativeFeedbacks = $summary['negative_feedbacks'] ?? 0;
        $neutralFeedbacks = $summary['neutral_feedbacks'] ?? 0;

        return [
            'total_feedbacks' => $totalFeedbacks,
            'avg_rating' => round($summary['avg_rating'] ?? 0, 2),
            'min_rating' => $summary['min_rating'] ?? 0,
            'max_rating' => $summary['max_rating'] ?? 0,
            'positive_feedbacks' => $positiveFeedbacks,
            'negative_feedbacks' => $negativeFeedbacks,
            'neutral_feedbacks' => $neutralFeedbacks,
            'positive_rate' => $totalFeedbacks > 0 ? round(($positiveFeedbacks / $totalFeedbacks) * 100, 2) : 0,
            'negative_rate' => $totalFeedbacks > 0 ? round(($negativeFeedbacks / $totalFeedbacks) * 100, 2) : 0,
            'neutral_rate' => $totalFeedbacks > 0 ? round(($neutralFeedbacks / $totalFeedbacks) * 100, 2) : 0,
            'tours_with_feedback' => $summary['tours_with_feedback'] ?? 0,
            'unique_customers' => $summary['unique_customers'] ?? 0
        ];
    }

    /**
     * Lấy phản hồi chi tiết theo tour
     */
    public function getTourFeedbackDetails($dateFrom = null, $dateTo = null, $filters = [])
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $whereConditions = ["TF.created_at BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        if (!empty($filters['tour_id'])) {
            $whereConditions[] = "TF.tour_id = :tour_id";
            $params[':tour_id'] = $filters['tour_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    T.id as tour_id,
                    T.name as tour_name,
                    TC.name as category_name,
                    COUNT(TF.id) as feedback_count,
                    AVG(TF.rating) as avg_rating,
                    MIN(TF.rating) as min_rating,
                    MAX(TF.rating) as max_rating,
                    SUM(CASE WHEN TF.rating >= 4 THEN 1 ELSE 0 END) as positive_count,
                    SUM(CASE WHEN TF.rating <= 2 THEN 1 ELSE 0 END) as negative_count,
                    GROUP_CONCAT(TF.comment SEPARATOR ' | ') as recent_comments,
                    MAX(TF.created_at) as last_feedback_date
                FROM tours T
                LEFT JOIN tour_categories TC ON T.category_id = TC.id
                LEFT JOIN tour_feedbacks TF ON T.id = TF.tour_id
                $whereClause
                GROUP BY T.id, T.name, TC.name
                HAVING feedback_count > 0
                ORDER BY avg_rating DESC, feedback_count DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $tourFeedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tính toán thêm các chỉ số
        foreach ($tourFeedbacks as &$feedback) {
            $feedbackCount = $feedback['feedback_count'];
            $feedback['positive_rate'] = $feedbackCount > 0 ? round(($feedback['positive_count'] / $feedbackCount) * 100, 2) : 0;
            $feedback['negative_rate'] = $feedbackCount > 0 ? round(($feedback['negative_count'] / $feedbackCount) * 100, 2) : 0;
            $feedback['rating_distribution'] = $this->getTourRatingDistribution($feedback['tour_id'], $dateFrom, $dateTo);
        }

        return $tourFeedbacks;
    }

    /**
     * Lấy phân phối đánh giá cho một tour cụ thể
     */
    private function getTourRatingDistribution($tourId, $dateFrom, $dateTo)
    {
        $sql = "SELECT 
                    rating,
                    COUNT(*) as count
                FROM tour_feedbacks 
                WHERE tour_id = :tour_id 
                AND created_at BETWEEN :date_from AND :date_to
                GROUP BY rating
                ORDER BY rating";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tour_id' => $tourId, ':date_from' => $dateFrom, ':date_to' => $dateTo]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ensure all ratings from 1-5 are included
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $count = 0;
            foreach ($data as $item) {
                if ($item['rating'] == $i) {
                    $count = $item['count'];
                    break;
                }
            }
            $distribution[$i] = $count;
        }

        return $distribution;
    }

    /**
     * Lấy phản hồi theo danh mục
     */
    public function getCategoryFeedbackDetails($dateFrom = null, $dateTo = null, $filters = [])
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $whereConditions = ["TF.created_at BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        if (!empty($filters['category_id'])) {
            $whereConditions[] = "TC.id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    TC.id as category_id,
                    TC.name as category_name,
                    COUNT(DISTINCT T.id) as tour_count,
                    COUNT(TF.id) as feedback_count,
                    AVG(TF.rating) as avg_rating,
                    MIN(TF.rating) as min_rating,
                    MAX(TF.rating) as max_rating,
                    SUM(CASE WHEN TF.rating >= 4 THEN 1 ELSE 0 END) as positive_count,
                    SUM(CASE WHEN TF.rating <= 2 THEN 1 ELSE 0 END) as negative_count,
                    COUNT(DISTINCT TF.user_id) as unique_customers
                FROM tour_categories TC
                LEFT JOIN tours T ON TC.id = T.category_id
                LEFT JOIN tour_feedbacks TF ON T.id = TF.tour_id
                $whereClause
                GROUP BY TC.id, TC.name
                HAVING feedback_count > 0
                ORDER BY avg_rating DESC, feedback_count DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $categoryFeedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tính toán các chỉ số bổ sung
        foreach ($categoryFeedbacks as &$feedback) {
            $feedbackCount = $feedback['feedback_count'];
            $feedback['positive_rate'] = $feedbackCount > 0 ? round(($feedback['positive_count'] / $feedbackCount) * 100, 2) : 0;
            $feedback['negative_rate'] = $feedbackCount > 0 ? round(($feedback['negative_count'] / $feedbackCount) * 100, 2) : 0;
            $feedback['feedbacks_per_tour'] = $feedback['tour_count'] > 0 ? round($feedbackCount / $feedback['tour_count'], 2) : 0;
        }

        return $categoryFeedbacks;
    }

    /**
     * Phân tích từ khóa trong phản hồi
     */
    public function getKeywordAnalysis($dateFrom = null, $dateTo = null, $filters = [])
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $whereConditions = ["TF.created_at BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        if (!empty($filters['tour_id'])) {
            $whereConditions[] = "TF.tour_id = :tour_id";
            $params[':tour_id'] = $filters['tour_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        // Lấy tất cả comments có rating
        $sql = "SELECT TF.comment, TF.rating 
                FROM tour_feedbacks TF 
                $whereClause 
                AND TF.comment IS NOT NULL AND TF.comment != ''";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Từ khóa tích cực và tiêu cực
        $positiveWords = [
            'tốt',
            'hay',
            'hài lòng',
            'tuyệt vời',
            'xuất sắc',
            'thích',
            'đẹp',
            'chuyên nghiệp',
            'tuyệt',
            'hoàn hảo',
            'tuyệt vời',
            'rất tốt',
            'ấn tượng',
            'thú vị',
            'thân thiện',
            'nhiệt tình',
            'chăm sóc',
            'tận tâm',
            'qualité',
            'excellent',
            'amazing'
        ];

        $negativeWords = [
            'tệ',
            'kém',
            'không hài lòng',
            'thất vọng',
            'chậm',
            'dở',
            'kém',
            'tồi',
            'quá tệ',
            'không tốt',
            'rất kém',
            'thất vọng',
            'khuyên',
            'tránh',
            'không nên',
            'problem',
            'issue',
            'bad',
            'poor',
            'terrible',
            'disappointing'
        ];

        $serviceWords = [
            'hướng dẫn viên',
            'hdv',
            'guide',
            'khách sạn',
            'hotel',
            'xe',
            'transport',
            'ăn uống',
            'food',
            'dịch vụ',
            'service',
            'phòng',
            'room',
            'tour',
            'chuyến đi'
        ];

        $keywords = [
            'positive' => [],
            'negative' => [],
            'service' => []
        ];

        foreach ($feedbacks as $feedback) {
            $comment = strtolower($feedback['comment']);
            $rating = $feedback['rating'];

            // Đếm từ khóa tích cực
            foreach ($positiveWords as $word) {
                if (strpos($comment, $word) !== false) {
                    $keywords['positive'][$word] = ($keywords['positive'][$word] ?? 0) + 1;
                }
            }

            // Đếm từ khóa tiêu cực
            foreach ($negativeWords as $word) {
                if (strpos($comment, $word) !== false) {
                    $keywords['negative'][$word] = ($keywords['negative'][$word] ?? 0) + 1;
                }
            }

            // Đếm từ khóa dịch vụ
            foreach ($serviceWords as $word) {
                if (strpos($comment, $word) !== false) {
                    $keywords['service'][$word] = ($keywords['service'][$word] ?? 0) + 1;
                }
            }
        }

        // Sắp xếp theo tần suất
        arsort($keywords['positive']);
        arsort($keywords['negative']);
        arsort($keywords['service']);

        // Format kết quả
        $result = [];
        foreach ($keywords as $type => $words) {
            foreach ($words as $word => $count) {
                $result[] = [
                    'keyword' => $word,
                    'count' => $count,
                    'type' => $type,
                    'sentiment' => $type === 'positive' ? 'positive' : ($type === 'negative' ? 'negative' : 'neutral')
                ];
            }
        }

        // Lấy top 20 từ khóa phổ biến nhất
        usort($result, function ($a, $b) {
            return $b['count'] - $a['count'];
        });

        return array_slice($result, 0, 20);
    }

    /**
     * Lấy phản hồi theo thời gian (theo tháng)
     */
    public function getFeedbackTrends($year = null, $filters = [])
    {
        $year = $year ?? date('Y');

        $whereConditions = ["YEAR(TF.created_at) = :year"];
        $params = [':year' => $year];

        if (!empty($filters['tour_id'])) {
            $whereConditions[] = "TF.tour_id = :tour_id";
            $params[':tour_id'] = $filters['tour_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    MONTH(TF.created_at) as month,
                    DATE_FORMAT(TF.created_at, '%m/%Y') as month_label,
                    COUNT(*) as feedback_count,
                    AVG(TF.rating) as avg_rating,
                    MIN(TF.rating) as min_rating,
                    MAX(TF.rating) as max_rating,
                    SUM(CASE WHEN TF.rating >= 4 THEN 1 ELSE 0 END) as positive_count,
                    SUM(CASE WHEN TF.rating <= 2 THEN 1 ELSE 0 END) as negative_count,
                    COUNT(DISTINCT TF.tour_id) as tours_with_feedback,
                    COUNT(DISTINCT TF.user_id) as unique_customers
                FROM tour_feedbacks TF
                $whereClause
                GROUP BY MONTH(TF.created_at), DATE_FORMAT(TF.created_at, '%m/%Y')
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
                'feedback_count' => 0,
                'avg_rating' => 0,
                'min_rating' => 0,
                'max_rating' => 0,
                'positive_count' => 0,
                'negative_count' => 0,
                'positive_rate' => 0,
                'negative_rate' => 0,
                'tours_with_feedback' => 0,
                'unique_customers' => 0
            ];

            foreach ($monthlyData as $data) {
                if ($data['month'] == $month) {
                    $monthData = array_merge($monthData, $data);
                    $monthData['positive_rate'] = $data['feedback_count'] > 0 ? round(($data['positive_count'] / $data['feedback_count']) * 100, 2) : 0;
                    $monthData['negative_rate'] = $data['feedback_count'] > 0 ? round(($data['negative_count'] / $data['feedback_count']) * 100, 2) : 0;
                    break;
                }
            }

            $result[] = $monthData;
        }

        return $result;
    }

    /**
     * Lấy top tours có đánh giá cao nhất và thấp nhất
     */
    public function getTopRatedTours($dateFrom = null, $dateTo = null, $limit = 10)
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo, ':limit' => $limit];

        $sql = "SELECT 
                    T.id,
                    T.name as tour_name,
                    TC.name as category_name,
                    COUNT(TF.id) AS feedback_count,
                    AVG(TF.rating) AS avg_rating,
                    MIN(TF.rating) AS min_rating,
                    MAX(TF.rating) AS max_rating
                FROM tours T
                LEFT JOIN tour_categories TC ON T.category_id = TC.id
                LEFT JOIN tour_feedbacks TF ON T.id = TF.tour_id
                WHERE TF.created_at BETWEEN :date_from AND :date_to
                GROUP BY T.id, T.name, TC.name
                HAVING feedback_count > 0
                ORDER BY avg_rating DESC, feedback_count DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $topRated = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy tours có đánh giá thấp nhất
        $sql = "SELECT 
                    T.id,
                    T.name as tour_name,
                    TC.name as category_name,
                    COUNT(TF.id) AS feedback_count,
                    AVG(TF.rating) AS avg_rating,
                    MIN(TF.rating) AS min_rating,
                    MAX(TF.rating) AS max_rating
                FROM tours T
                LEFT JOIN tour_categories TC ON T.category_id = TC.id
                LEFT JOIN tour_feedbacks TF ON T.id = TF.tour_id
                WHERE TF.created_at BETWEEN :date_from AND :date_to
                GROUP BY T.id, T.name, TC.name
                HAVING feedback_count > 0
                ORDER BY avg_rating ASC, feedback_count DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $bottomRated = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'top_rated' => $topRated,
            'bottom_rated' => $bottomRated
        ];
    }

    /**
     * Lấy phân phối đánh giá tổng thể
     */
    public function getRatingDistribution($dateFrom = null, $dateTo = null, $filters = [])
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-d');

        $whereConditions = ["TF.created_at BETWEEN :date_from AND :date_to"];
        $params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

        if (!empty($filters['tour_id'])) {
            $whereConditions[] = "TF.tour_id = :tour_id";
            $params[':tour_id'] = $filters['tour_id'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    rating,
                    COUNT(*) as count,
                    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM tour_feedbacks $whereClause), 2) as percentage
                FROM tour_feedbacks TF
                $whereClause
                GROUP BY rating
                ORDER BY rating";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ensure all ratings from 1-5 are included
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $count = 0;
            $percentage = 0;
            foreach ($data as $item) {
                if ($item['rating'] == $i) {
                    $count = $item['count'];
                    $percentage = $item['percentage'];
                    break;
                }
            }
            $distribution[] = [
                'rating' => $i,
                'count' => $count,
                'percentage' => $percentage
            ];
        }

        return $distribution;
    }

    /**
     * Lấy chi tiết các phản hồi gần đây
     */
    public function getRecentFeedbacks($limit = 20, $filters = [])
    {
        $whereConditions = ["1=1"];
        $params = [];

        if (!empty($filters['tour_id'])) {
            $whereConditions[] = "TF.tour_id = :tour_id";
            $params[':tour_id'] = $filters['tour_id'];
        }
        if (!empty($filters['rating'])) {
            $whereConditions[] = "TF.rating = :rating";
            $params[':rating'] = $filters['rating'];
        }

        $whereClause = "WHERE " . implode(' AND ', $whereConditions);

        $sql = "SELECT 
                    TF.*,
                    T.name as tour_name,
                    TC.name as category_name,
                    U.full_name as customer_name,
                    U.email as customer_email,
                    CASE 
                        WHEN TF.rating >= 4 THEN 'positive'
                        WHEN TF.rating <= 2 THEN 'negative'
                        ELSE 'neutral'
                    END as sentiment
                FROM tour_feedbacks TF
                LEFT JOIN tours T ON TF.tour_id = T.id
                LEFT JOIN tour_categories TC ON T.category_id = TC.id
                LEFT JOIN users U ON TF.user_id = U.user_id
                $whereClause
                ORDER BY TF.created_at DESC
                LIMIT :limit";

        $params[':limit'] = $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
