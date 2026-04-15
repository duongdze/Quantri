<?php
require_once 'models/BaseModel.php';

class Review extends BaseModel
{
    protected $table = 'tour_reviews';

    /**
     * Lấy danh sách review theo tour (chỉ hiện approved)
     */
    public function getApprovedByTour($tourId, $limit = 20)
    {
        $sql = "SELECT r.*, u.full_name, u.avatar
                FROM tour_reviews r
                LEFT JOIN users u ON r.user_id = u.user_id
                WHERE r.tour_id = :tid AND r.status = 'approved'
                ORDER BY r.created_at DESC
                LIMIT :lmt";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':tid', $tourId, PDO::PARAM_INT);
        $stmt->bindValue(':lmt', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Thống kê rating của tour
     */
    public function getRatingSummary($tourId)
    {
        $sql = "SELECT
                    COUNT(*) as total,
                    AVG(rating) as avg_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as star5,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as star4,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as star3,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as star2,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as star1
                FROM tour_reviews
                WHERE tour_id = :tid AND status = 'approved'";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':tid' => $tourId]);
        return $stmt->fetch();
    }

    /**
     * Kiểm tra user đã review tour này chưa
     */
    public function hasReviewed($tourId, $userId)
    {
        $stmt = self::$pdo->prepare(
            "SELECT COUNT(*) FROM tour_reviews WHERE tour_id = :tid AND user_id = :uid"
        );
        $stmt->execute([':tid' => $tourId, ':uid' => $userId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Kiểm tra user đã đặt tour này chưa (điều kiện để review)
     */
    public function hasBookedTour($tourId, $userId)
    {
        $stmt = self::$pdo->prepare(
            "SELECT COUNT(*) FROM bookings
             WHERE tour_id = :tid AND customer_id = :uid AND status IN ('confirmed','completed','paid')"
        );
        $stmt->execute([':tid' => $tourId, ':uid' => $userId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Lấy tất cả review cho admin (phân trang + lọc)
     */
    public function getAllForAdmin($page = 1, $perPage = 20, $filters = [])
    {
        $page    = max(1, (int)$page);
        $perPage = max(5, min(50, (int)$perPage));
        $offset  = ($page - 1) * $perPage;

        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'r.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['tour_id'])) {
            $where[] = 'r.tour_id = :tid';
            $params[':tid'] = $filters['tour_id'];
        }
        if (!empty($filters['rating'])) {
            $where[] = 'r.rating = :rating';
            $params[':rating'] = $filters['rating'];
        }

        $whereStr = 'WHERE ' . implode(' AND ', $where);

        $count = self::$pdo->prepare(
            "SELECT COUNT(*) FROM tour_reviews r $whereStr"
        );
        $count->execute($params);
        $total = (int)$count->fetchColumn();

        $sql = "SELECT r.*, u.full_name, t.name as tour_name
                FROM tour_reviews r
                LEFT JOIN users u ON r.user_id = u.user_id
                LEFT JOIN tours t ON r.tour_id = t.id
                $whereStr
                ORDER BY r.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();

        return [
            'data'        => $stmt->fetchAll(),
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int)ceil($total / $perPage),
        ];
    }

    /**
     * Cập nhật trạng thái (admin duyệt/ẩn)
     */
    public function updateStatus($id, $status)
    {
        return $this->update(
            ['status' => $status],
            'id = :id',
            ['id' => $id]
        );
    }
}
