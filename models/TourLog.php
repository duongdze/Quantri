<?php
// models/TourLog.php
require_once 'models/BaseModel.php';

class TourLog extends BaseModel
{
    protected $table = 'tour_logs';

    public function all(): array
    {
        $stmt = self::$pdo->query("
            SELECT tl.*, t.name AS tour_name, u.full_name AS guide_name
            FROM tour_logs tl
            JOIN tours t ON t.id = tl.tour_id
            JOIN guides g ON g.id = tl.guide_id
            JOIN users u ON u.user_id = g.user_id
            ORDER BY tl.date DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id): ?array
    {
        $stmt = self::$pdo->prepare("SELECT * FROM tour_logs WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO tour_logs 
        (tour_id, assignment_id, guide_id, date, description, issue, solution, customer_feedback, weather, incident, health_status, special_activity, handling_notes, guide_rating) 
        VALUES 
        (:tour_id, :assignment_id, :guide_id, :date, :description, :issue, :solution, :customer_feedback, :weather, :incident, :health_status, :special_activity, :handling_notes, :guide_rating)";

        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute($data);
    }


    public function updateLog($id, array $data): bool
    {
        $sql = "UPDATE tour_logs SET
            tour_id = :tour_id,
            assignment_id = :assignment_id,
            guide_id = :guide_id,
            date = :date,
            description = :description,
            issue = :issue,
            solution = :solution,
            customer_feedback = :customer_feedback,
            weather = :weather,
            incident = :incident,
            health_status = :health_status,
            special_activity = :special_activity,
            handling_notes = :handling_notes,
            guide_rating = :guide_rating
            WHERE id = :id";
        $data['id'] = $id;
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function deleteById($id): bool
    {
        $stmt = self::$pdo->prepare("DELETE FROM tour_logs WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getLogsByAssignmentId($assignmentId)
    {
        $sql = "SELECT tl.*, u.full_name as guide_name 
                FROM {$this->table} tl
                LEFT JOIN guides g ON tl.guide_id = g.id
                LEFT JOIN users u ON g.user_id = u.user_id
                WHERE tl.assignment_id = :assignment_id
                ORDER BY tl.date DESC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['assignment_id' => $assignmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLogsByTourId($tourId)
    {
        $sql = "SELECT tl.*, u.full_name as guide_name 
                FROM {$this->table} tl
                LEFT JOIN guides g ON tl.guide_id = g.id
                LEFT JOIN users u ON g.user_id = u.user_id
                WHERE tl.tour_id = :tour_id AND tl.assignment_id IS NULL
                ORDER BY tl.date DESC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['tour_id' => $tourId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getToursWithLogStats($filters = [])
    {
        $params = [];
        $where = "WHERE ta.status IN ('active', 'completed')";

        if (!empty($filters['guide_id'])) {
            $where .= " AND ta.guide_id = :guide_id";
            $params['guide_id'] = $filters['guide_id'];
        }

        if (!empty($filters['keyword'])) {
            $where .= " AND (t.name LIKE :keyword OR u.full_name LIKE :keyword)";
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['status'])) {
            $where .= " AND ta.status = :status";
            $params['status'] = $filters['status'];
        }

        $sql = "SELECT ta.id as assignment_id, t.name, ta.start_date, ta.end_date, ta.status,
                       u.full_name as guide_name,
                       COUNT(tl.id) as log_count, 
                       MAX(tl.date) as last_log_date
                FROM tour_assignments ta
                INNER JOIN tours t ON ta.tour_id = t.id
                INNER JOIN guides g ON ta.guide_id = g.id
                INNER JOIN users u ON g.user_id = u.user_id
                LEFT JOIN tour_logs tl ON ta.id = tl.assignment_id
                $where
                GROUP BY ta.id, t.name, ta.start_date, ta.end_date, ta.status, u.full_name
                ORDER BY ta.start_date DESC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tours với log stats cho HDV cụ thể
     */
    public function getToursWithLogStatsByGuide($guideId)
    {
        $sql = "SELECT ta.id as assignment_id, t.name, ta.start_date, ta.end_date, ta.status,
                       u.full_name as guide_name,
                       COUNT(tl.id) as log_count, 
                       MAX(tl.date) as last_log_date
                FROM tour_assignments ta
                INNER JOIN tours t ON ta.tour_id = t.id
                INNER JOIN guides g ON ta.guide_id = g.id
                INNER JOIN users u ON g.user_id = u.user_id
                LEFT JOIN tour_logs tl ON ta.id = tl.assignment_id
                WHERE ta.guide_id = :guide_id 
                AND ta.status IN ('active', 'completed')
                GROUP BY ta.id, t.name, ta.start_date, ta.end_date, ta.status, u.full_name
                ORDER BY ta.start_date DESC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['guide_id' => $guideId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy logs của HDV cụ thể
     */
    public function getLogsByGuideId($guideId)
    {
        $sql = "SELECT tl.*, t.name AS tour_name, u.full_name AS guide_name
                FROM tour_logs tl
                JOIN tours t ON t.id = tl.tour_id
                JOIN guides g ON g.id = tl.guide_id
                JOIN users u ON u.user_id = g.user_id
                WHERE tl.guide_id = :guide_id
                ORDER BY tl.date DESC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['guide_id' => $guideId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kiểm tra HDV có quyền truy cập log không
     */
    public function canGuideAccessLog($logId, $guideId)
    {
        $sql = "SELECT COUNT(*) as count
                FROM tour_logs tl
                INNER JOIN tour_assignments ta ON tl.tour_id = ta.tour_id
                WHERE tl.id = :log_id 
                AND ta.guide_id = :guide_id
                AND ta.status = 'active'";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['log_id' => $logId, 'guide_id' => $guideId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Kiểm tra HDV có quyền truy cập tour không
     */
    public function canGuideAccessTour($tourId, $guideId)
    {
        $sql = "SELECT COUNT(*) as count
                FROM tour_assignments
                WHERE tour_id = :tour_id 
                AND guide_id = :guide_id
                AND status = 'active'";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['tour_id' => $tourId, 'guide_id' => $guideId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
}
