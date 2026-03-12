<?php
require_once 'models/BaseModel.php';

/**
 * Model quản lý phân công tour cho hướng dẫn viên
 * Sử dụng bảng tour_assignments có sẵn
 */
class TourAssignment extends BaseModel
{
    protected $table = 'tour_assignments';
    protected $columns = [
        'id',
        'tour_id',
        'guide_id',
        'driver_name',
        'start_date',
        'end_date',
        'status'
    ];

    /**
     * Lấy chi tiết assignment theo ID
     */
    public function getById($id)
    {
        return $this->find('*', 'id = :id', ['id' => $id]);
    }

    /**
     * Phân công tour cho HDV
     * @param int $guideId
     * @param int $tourId
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string $status
     * @return int|false - ID của assignment hoặc false nếu thất bại
     */
    public function assignTourToGuide($guideId, $tourId, $startDate = null, $endDate = null, $status = 'active')
    {
        try {
            return $this->insert([
                'guide_id' => $guideId,
                'tour_id' => $tourId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
                'driver_name' => null
            ]);
        } catch (Exception $e) {
            error_log('Error assigning tour to guide: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách tour của một HDV (đang active)
     * @param int $guideId
     * @return array
     */
    public function getToursByGuide($guideId)
    {
        $sql = "SELECT 
                    ta.*,
                    t.id as tour_id,
                    t.name as tour_name,
                    t.base_price,
                    t.description
                FROM {$this->table} AS ta
                LEFT JOIN tours AS t ON ta.tour_id = t.id
                WHERE ta.guide_id = :guide_id
                ORDER BY ta.start_date DESC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['guide_id' => $guideId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách HDV của một tour
     * @param int $tourId
     * @return array
     */
    public function getGuidesByTour($tourId)
    {
        $sql = "SELECT 
                    ta.*,
                    g.id as guide_id,
                    u.full_name as guide_name,
                    u.email as guide_email,
                    u.phone as guide_phone,
                    g.languages,
                    g.experience_years
                FROM {$this->table} AS ta
                LEFT JOIN guides AS g ON ta.guide_id = g.id
                LEFT JOIN users AS u ON g.user_id = u.user_id
                WHERE ta.tour_id = :tour_id
                ORDER BY ta.start_date DESC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['tour_id' => $tourId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Hủy phân công tour cho HDV
     * @param int $id - ID của tour_assignment
     * @return bool
     */
    public function removeAssignment($id)
    {
        try {
            return $this->delete('id = :id', ['id' => $id]);
        } catch (Exception $e) {
            error_log('Error removing tour assignment: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra HDV có phụ trách tour không (bất kỳ thời điểm nào)
     * @param int $guideId
     * @param int $tourId
     * @return bool
     */
    public function isGuideAssignedToTour($guideId, $tourId)
    {
        $result = $this->find('id', 'guide_id = :guide_id AND tour_id = :tour_id', [
            'guide_id' => $guideId,
            'tour_id' => $tourId
        ]);
        return !empty($result);
    }

    /**
     * Lấy tất cả phân công với thông tin chi tiết
     * @return array
     */
    public function getAllAssignments()
    {
        $sql = "SELECT 
                    ta.*,
                    g.id as guide_id,
                    u.full_name as guide_name,
                    u.email as guide_email,
                    t.id as tour_id,
                    t.name as tour_name
                FROM {$this->table} AS ta
                LEFT JOIN guides AS g ON ta.guide_id = g.id
                LEFT JOIN users AS u ON g.user_id = u.user_id
                LEFT JOIN tours AS t ON ta.tour_id = t.id
                ORDER BY ta.start_date DESC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật trạng thái assignment
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status)
    {
        try {
            return $this->update(
                ['status' => $status],
                'id = :id',
                ['id' => $id]
            );
        } catch (Exception $e) {
            error_log('Error updating assignment status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy các tour assignments đang active của HDV
     * @param int $guideId
     * @return array
     */
    public function getActiveAssignmentsByGuide($guideId)
    {
        $sql = "SELECT 
                    ta.*,
                    t.id as tour_id,
                    t.name as tour_name,
                    t.base_price
                FROM {$this->table} AS ta
                LEFT JOIN tours AS t ON ta.tour_id = t.id
                WHERE ta.guide_id = :guide_id 
                AND (ta.status = 'active' OR ta.status IS NULL)
                AND (ta.end_date IS NULL OR ta.end_date >= CURDATE())
                ORDER BY ta.start_date DESC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['guide_id' => $guideId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách tour chưa có HDV - theo ngày booking (gộp các booking cùng ngày)
     * @return array
     */
    public function getAvailableTours()
    {
        $sql = "SELECT 
            t.id as tour_id, 
            t.name as tour_name, 
            t.category_id, 
            t.description, 
            t.base_price as tour_base_price,
            DATE(b.booking_date) as departure_date,
            COUNT(DISTINCT b.id) as booking_count,
            COALESCE(SUM(CASE WHEN bc_count.total IS NOT NULL THEN bc_count.total ELSE 0 END), 0) + COUNT(DISTINCT b.id) as total_customers,
            COALESCE(SUM(b.total_price), 0) as total_booking_price,
            GROUP_CONCAT(DISTINCT b.id ORDER BY b.id) as booking_ids
        FROM tours t
        INNER JOIN bookings b ON t.id = b.tour_id 
            AND DATE(b.booking_date) >= CURDATE()
            AND b.status NOT IN ('hoan_tat', 'da_huy')
        LEFT JOIN (
            SELECT booking_id, COUNT(*) as total 
            FROM booking_customers 
            GROUP BY booking_id
        ) bc_count ON b.id = bc_count.booking_id
        WHERE NOT EXISTS (
            SELECT 1 
            FROM tour_assignments ta
            WHERE ta.tour_id = t.id 
            AND ta.start_date = DATE(b.booking_date)
            AND ta.status = 'active'
        )
        AND t.status = 'active'
        GROUP BY t.id, t.name, t.category_id, t.description, t.base_price, DATE(b.booking_date)
        ORDER BY DATE(b.booking_date) ASC, t.name ASC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách ngày khởi hành của tour
     * @param int $tourId
     * @return array
     */
    public function getTourDepartureDates($tourId)
    {
        $sql = "SELECT 
                id,
                departure_date,
                max_seats,
                booked_seats,
                (max_seats - booked_seats) as available_seats,
                status
            FROM tour_departures
            WHERE tour_id = :tour_id
                AND status = 'open'
                AND departure_date >= CURDATE()
            ORDER BY departure_date ASC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['tour_id' => $tourId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kiểm tra tour đã có HDV chưa (cho ngày cụ thể)
     * @param int $tourId
     * @param string|null $startDate - Ngày khởi hành cụ thể
     * @return bool
     */
    public function tourHasGuide($tourId, $startDate = null)
    {
        if ($startDate) {
            // Check theo cả tour_id và start_date
            $sql = "SELECT COUNT(*) as count 
                FROM tour_assignments 
                WHERE tour_id = :tour_id 
                AND start_date = :start_date
                AND status = 'active'";

            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([
                'tour_id' => $tourId,
                'start_date' => $startDate
            ]);
        } else {
            // Check chỉ theo tour_id (backward compatibility)
            $sql = "SELECT COUNT(*) as count 
                FROM tour_assignments 
                WHERE tour_id = :tour_id 
                AND status = 'active'";

            $stmt = self::$pdo->prepare($sql);
            $stmt->execute(['tour_id' => $tourId]);
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Lấy danh sách assignment nào chưa có start_date
     * @return array
     */
    public function getAssignmentsMissingDates()
    {
        $sql = "SELECT * FROM {$this->table} WHERE start_date IS NULL OR start_date = ''";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật start_date/end_date cho assignment
     * @param int $id
     * @param string|null $startDate
     * @param string|null $endDate
     * @return bool|int
     */
    public function updateAssignmentDates($id, $startDate = null, $endDate = null)
    {
        $data = ['start_date' => $startDate, 'end_date' => $endDate];
        return $this->update($data, 'id = :id', ['id' => $id]);
    }

    /**
     * Lấy chi tiết phân bổ khách theo tour version
     * @param int $tourId
     * @return array
     */
    public function getTourVersionBreakdown($tourId)
    {
        $sql = "SELECT 
                    COALESCE(tv.name, 'Mặc định') as version_name,
                    b.version_id,
                    COUNT(DISTINCT b.id) as booking_count,
                    COALESCE(SUM(bc_count.total), 0) + COUNT(DISTINCT b.id) as customer_count
                FROM bookings b
                LEFT JOIN tour_versions tv ON b.version_id = tv.id
                LEFT JOIN (
                    SELECT booking_id, COUNT(*) as total 
                    FROM booking_customers 
                    GROUP BY booking_id
                ) bc_count ON b.id = bc_count.booking_id
                WHERE b.tour_id = :tour_id 
                    AND b.status NOT IN ('hoan_tat', 'da_huy')
                GROUP BY b.version_id, tv.name
                ORDER BY customer_count DESC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['tour_id' => $tourId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
