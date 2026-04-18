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
        'status',
        'group_number',
        'departure_id'
    ];
    /**
     * Lấy số ngày đi tour dựa trên số lượng ngày trong lịch trình
     */
    public function getTourDuration($tourId)
    {
        $sql = "SELECT MAX(day_number) as max_day FROM itineraries WHERE tour_id = :tour_id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['tour_id' => $tourId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $days = (int)($result['max_day'] ?? 0);
        return ($days > 0) ? $days : 1; // Mặc định 1 ngày nếu chưa có lịch trình
    }

    /**
     * Kiểm tra HDV có bị trùng lịch vào khoảng thời gian này không
     * @param int $guideId
     * @param string $startDate (Y-m-d)
     * @param string $endDate (Y-m-d)
     * @param int|null $excludeAssignmentId - Bỏ qua ID này khi update
     * @return array|false - Trả về thông tin tour bị trùng hoặc false
     */
    public function isGuideBusy($guideId, $startDate, $endDate, $excludeAssignmentId = null)
    {
        $sql = "SELECT ta.*, t.name as tour_name 
                FROM {$this->table} ta
                JOIN tours t ON ta.tour_id = t.id
                WHERE ta.guide_id = :guide_id 
                AND ta.status = 'active'
                AND (
                    (ta.start_date <= :end_date AND COALESCE(ta.end_date, ta.start_date) >= :start_date)
                )";
        
        $params = [
            'guide_id' => $guideId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if ($excludeAssignmentId) {
            $sql .= " AND ta.id != :exclude_id";
            $params['exclude_id'] = $excludeAssignmentId;
        }

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết assignment theo ID
     */
    public function getById($id)
    {
        $sql = "SELECT ta.*, u.full_name as guide_name, u.phone as guide_phone, t.name as tour_name
                FROM {$this->table} ta
                LEFT JOIN guides g ON ta.guide_id = g.id
                LEFT JOIN users u ON g.user_id = u.user_id
                LEFT JOIN tours t ON ta.tour_id = t.id
                WHERE ta.id = :id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
     * Cập nhật người phụ trách (HDV) cho bản ghi phân công
     */
    public function updateGuide($id, $newGuideId)
    {
        return $this->update(
            ['guide_id' => $newGuideId],
            'id = :id',
            ['id' => $id]
        );
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
     * Lấy danh sách tour chưa có HDV - theo ngày khởi hành
     * @return array
     */
    public function getAvailableTours()
    {
        // 1. Lấy danh sách gốc các tour có bookings và quy mô
        $sql = "SELECT 
            t.id as tour_id, 
            t.name as tour_name, 
            t.category_id, 
            t.description, 
            t.min_participants,
            t.max_participants,
            t.base_price as tour_base_price,
            td.id as departure_id,
            td.max_seats,
            b.departure_date,
            COUNT(DISTINCT b.id) as booking_count,
            COALESCE(SUM(bc_count.total), 0) as total_customers,
            COALESCE(SUM(bc_count.adult_count), 0) as total_adults,
            COALESCE(SUM(bc_count.child_count), 0) as total_children,
            COALESCE(SUM(bc_count.infant_count), 0) as total_infants,
            COALESCE(GROUP_CONCAT(DISTINCT bc_count.special_requests_summary SEPARATOR '; '), '') as special_requests,
            COALESCE(SUM(b.total_price), 0) as total_booking_price,
            GROUP_CONCAT(DISTINCT b.id ORDER BY b.id) as booking_ids
        FROM tours t
        INNER JOIN bookings b ON t.id = b.tour_id 
            AND b.departure_date >= CURDATE()
            AND b.status NOT IN ('hoan_tat', 'da_huy')
        LEFT JOIN tour_departures td ON t.id = td.tour_id 
            AND b.departure_date = td.departure_date
        LEFT JOIN (
            SELECT 
                booking_id, 
                COUNT(*) as total,
                SUM(CASE WHEN passenger_type = 'adult' THEN 1 ELSE 0 END) as adult_count,
                SUM(CASE WHEN passenger_type = 'child' THEN 1 ELSE 0 END) as child_count,
                SUM(CASE WHEN passenger_type = 'infant' THEN 1 ELSE 0 END) as infant_count,
                GROUP_CONCAT(DISTINCT special_request SEPARATOR ', ') as special_requests_summary
            FROM booking_customers 
            GROUP BY booking_id
        ) bc_count ON b.id = bc_count.booking_id
        WHERE t.status = 'active'
        GROUP BY t.id, t.name, t.category_id, t.description, t.min_participants, t.max_participants, t.base_price, b.departure_date, td.id, td.max_seats
        ORDER BY b.departure_date ASC, t.name ASC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();
        $rawTours = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($rawTours as $tour) {
            $maxSeats = (int)($tour['max_seats'] > 0 ? $tour['max_seats'] : 30);
            $totalCustomers = (int)$tour['total_customers'];
            
            // Tính số lượng suất (slots) cần thiết
            $numSlots = ceil($totalCustomers / $maxSeats);
            
            for ($i = 1; $i <= $numSlots; $i++) {
                // Kiểm tra xem suất này (group_number) đã có HDV nhận chưa
                if (!$this->tourHasGuide($tour['tour_id'], $tour['departure_date'], $i)) {
                    $tourSlot = $tour;
                    $tourSlot['group_number'] = $i;
                    $tourSlot['is_split'] = ($numSlots > 1);
                    $results[] = $tourSlot;
                }
            }
        }
        
        return $results;
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
    public function tourHasGuide($tourId, $startDate = null, $groupNumber = 1)
    {
        if ($startDate) {
            // Check theo cả tour_id, start_date và group_number
            $sql = "SELECT COUNT(*) as count 
                FROM tour_assignments 
                WHERE tour_id = :tour_id 
                AND start_date = :start_date
                AND (group_number = :group_number OR (group_number IS NULL AND :group_num_check = 1))
                AND status = 'active'";

            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([
                'tour_id' => $tourId,
                'start_date' => $startDate,
                'group_number' => $groupNumber,
                'group_num_check' => $groupNumber
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

    /**
     * Tự động cập nhật các tour đã kết thúc về trạng thái 'completed'
     * Dựa trên ngày kết thúc (end_date)
     */
    public function autoCompleteStaleAssignments()
    {
        try {
            // Cập nhật các tour active đã quá ngày kết thúc
            $sql = "UPDATE {$this->table} 
                    SET status = 'completed' 
                    WHERE status = 'active' 
                    AND end_date < CURDATE()";
            
            $stmt = self::$pdo->prepare($sql);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log('Error auto-completing stale assignments: ' . $e->getMessage());
            return false;
        }
    }
}
