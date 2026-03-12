<?php
require_once 'models/BaseModel.php';

class Guide extends BaseModel
{
    protected $table = 'guides';
    protected $columns = [
        'id',
        'user_id',
        'languages',
        'experience_years',
        'rating',
        'health_status',
        'notes',
        'guide_type',
        'specialization',
        'total_tours',
        'performance_score'
    ];

    /**
     * Lấy danh sách tất cả HDV kèm thông tin user
     * @return array
     */
    public function getAll()
    {
        $sql = "SELECT 
                    g.*,
                    u.full_name,
                    u.email,
                    u.phone,
                    u.avatar
                FROM {$this->table} AS g
                LEFT JOIN users AS u ON g.user_id = u.user_id
                ORDER BY g.id DESC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách HDV với tên (cho dropdown)
     * @return array
     */
    public function getAllWithName()
    {
        $stmt = self::$pdo->query("SELECT g.id, u.full_name FROM guides g JOIN users u ON u.user_id = g.user_id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tổng số HDV đang hoạt động
     * @return int
     */
    public function getTotalActiveGuides()
    {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} g
                JOIN users u ON g.user_id = u.user_id
                WHERE u.is_active = 1";

        $stmt = self::$pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy danh sách HDV sẵn sàng (chưa được phân công tour trong khoảng thời gian cụ thể)
     * @param int $limit Giới hạn số lượng kết quả
     * @return array
     */
    public function getAvailableGuides($limit = 5)
    {
        $sql = "SELECT 
                    g.id,
                    u.full_name,
                    u.phone,
                    g.languages,
                    g.rating,
                    g.experience_years,
                    (SELECT COUNT(*) FROM tour_assignments ta WHERE ta.guide_id = g.id) as total_tours
                FROM {$this->table} g
                JOIN users u ON g.user_id = u.user_id
                WHERE u.is_active = 1
                ORDER BY g.rating DESC, total_tours ASC
                LIMIT :limit";

        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin HDV theo ID
     * @param int $id
     * @return array|false
     */
    public function getById($id)
    {
        return $this->find('*', 'id = :id', ['id' => $id]);
    }

    /**
     * Lấy thông tin HDV chi tiết kèm user info
     * @param int $id
     * @return array|false
     */
    public function getGuideWithDetails($id)
    {
        $sql = "SELECT 
                    g.*,
                    u.full_name,
                    u.email,
                    u.phone,
                    u.avatar,
                    u.role
                FROM {$this->table} AS g
                LEFT JOIN users AS u ON g.user_id = u.user_id
                WHERE g.id = :id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy HDV theo user_id
     * @param int $user_id
     * @return array|false
     */
    public function getByUserId($user_id)
    {
        return $this->find('*', 'user_id = :user_id', ['user_id' => $user_id]);
    }

    /**
     * Tạo HDV mới (cả user và guide)
     * @param array $userData
     * @param array $guideData
     * @return int Guide ID
     */
    public function createGuide($userData, $guideData)
    {
        try {
            $this->beginTransaction();

            // Tạo user trước
            $userModel = new UserModel();
            $user_id = $userModel->insert($userData);

            // Tạo guide với user_id vừa tạo
            $guideData['user_id'] = $user_id;
            $guide_id = $this->insert($guideData);

            $this->commit();
            return $guide_id;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Cập nhật HDV (cả user và guide)
     * @param int $guide_id
     * @param array $userData
     * @param array $guideData
     * @return bool
     */
    public function updateGuide($guide_id, $userData, $guideData)
    {
        try {
            $this->beginTransaction();

            // Lấy user_id từ guide
            $guide = $this->getById($guide_id);
            if (!$guide) {
                throw new Exception('Guide not found');
            }

            // Cập nhật user
            $userModel = new UserModel();
            $userModel->update($userData, 'user_id = :user_id', ['user_id' => $guide['user_id']]);

            // Cập nhật guide
            $this->update($guideData, 'id = :id', ['id' => $guide_id]);

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Xóa HDV
     * @param int $id
     * @return bool
     */
    public function deleteGuide($id)
    {
        try {
            $this->beginTransaction();

            // Lấy thông tin guide để có user_id
            $guide = $this->getById($id);
            if (!$guide) {
                throw new Exception('Guide not found');
            }

            // Xóa guide
            $this->delete('id = :id', ['id' => $id]);

            // Xóa user
            $userModel = new UserModel();
            $userModel->delete('user_id = :user_id', ['user_id' => $guide['user_id']]);

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Đếm tổng số HDV
     * @return int
     */
    public function getTotalGuides()
    {
        $result = $this->count();
        return (int) $result;
    }

    /**
     * Lấy danh sách HDV theo loại
     * @param string $type - domestic, international, specialized
     * @return array
     */
    public function getByType($type)
    {
        $sql = "SELECT 
                    g.*,
                    u.full_name,
                    u.email,
                    u.phone,
                    u.avatar
                FROM {$this->table} AS g
                LEFT JOIN users AS u ON g.user_id = u.user_id
                WHERE 1=1
                ORDER BY g.rating DESC, u.full_name ASC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách HDV nhóm theo loại
     * @return array
     */
    public function getGroupedByType()
    {
        $sql = "SELECT 
                    g.guide_type,
                    COUNT(*) as total,
                    AVG(g.rating) as avg_rating,
                    AVG(g.performance_score) as avg_performance
                FROM {$this->table} AS g
                GROUP BY g.guide_type
                ORDER BY total DESC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật loại và chuyên môn HDV
     * @param int $id
     * @param string $type
     * @param string $specialization
     * @return bool
     */
    public function updateGuideType($id, $type, $specialization)
    {
        return $this->update(
            ['guide_type' => $type, 'specialization' => $specialization],
            'id = :id',
            ['id' => $id]
        );
    }

    /**
     * Tăng số tour đã dẫn
     * @param int $id
     * @return bool
     */
    public function incrementTourCount($id)
    {
        $sql = "UPDATE {$this->table} SET total_tours = total_tours + 1 WHERE id = :id";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Cập nhật điểm hiệu suất
     * @param int $id
     * @param float $score
     * @return bool
     */
    public function updatePerformanceScore($id, $score)
    {
        return $this->update(
            ['performance_score' => $score],
            'id = :id',
            ['id' => $id]
        );
    }

    /**
     * Lấy top HDV theo hiệu suất
     * @param int $limit
     * @return array
     */
    public function getTopPerformers($limit = 10)
    {
        $sql = "SELECT 
                    g.*,
                    u.full_name,
                    u.email,
                    u.avatar
                FROM {$this->table} AS g
                LEFT JOIN users AS u ON g.user_id = u.user_id
                ORDER BY g.performance_score DESC, g.total_tours DESC, g.rating DESC
                LIMIT :limit";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
