<?php
require_once 'models/BaseModel.php';

/**
 * Model quản lý nhà xe
 */
class BusCompany extends BaseModel
{
    protected $table = 'bus_companies';
    protected $columns = [
        'id',
        'company_code',
        'company_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'business_license',
        'vehicle_type',
        'vehicle_brand',
        'total_vehicles',
        'status',
        'rating',
        'notes',
        'created_at',
        'updated_at'
    ];

    /**
     * Lấy danh sách nhà xe đang hoạt động
     */
    public function getActiveBusCompanies()
    {
        return $this->select('*', "status = 'active'", [], 'company_name ASC');
    }

    /**
     * Lấy nhà xe theo ID
     */
    public function getById($id)
    {
        return $this->find('*', 'id = :id', ['id' => $id]);
    }

    /**
     * Lấy tất cả nhà xe
     */
    public function getAll()
    {
        return $this->select('*', '', [], 'company_name ASC');
    }

    /**
     * Cập nhật trạng thái nhà xe
     */
    public function updateStatus($id, $status)
    {
        return $this->update(
            ['status' => $status],
            'id = :id',
            ['id' => $id]
        );
    }

    /**
     * Kiểm tra mã nhà xe đã tồn tại chưa
     */
    public function companyCodeExists($code, $excludeId = null)
    {
        $conditions = 'company_code = :code';
        $params = ['code' => $code];

        if ($excludeId) {
            $conditions .= ' AND id != :id';
            $params['id'] = $excludeId;
        }

        $result = $this->find('id', $conditions, $params);
        return !empty($result);
    }

    /**
     * Kiểm tra số điện thoại đã tồn tại chưa
     */
    public function phoneExists($phone, $excludeId = null)
    {
        $conditions = 'phone = :phone';
        $params = ['phone' => $phone];

        if ($excludeId) {
            $conditions .= ' AND id != :id';
            $params['id'] = $excludeId;
        }

        $result = $this->find('id', $conditions, $params);
        return !empty($result);
    }

    /**
     * Lấy thống kê nhà xe
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                    SUM(total_vehicles) as total_vehicles,
                    AVG(rating) as avg_rating
                FROM {$this->table}";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm kiếm nhà xe
     */
    public function search($keyword, $minRating = null)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (company_name LIKE :keyword 
                OR company_code LIKE :keyword 
                OR phone LIKE :keyword 
                OR email LIKE :keyword)";

        $params = ['keyword' => "%{$keyword}%"];

        if ($minRating !== null) {
            $sql .= " AND rating >= :rating";
            $params['rating'] = $minRating;
        }

        $sql .= " ORDER BY company_name ASC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy nhà xe theo đánh giá tối thiểu
     */
    public function getByMinRating($minRating)
    {
        return $this->select('*', "rating >= :rating AND status = 'active'", ['rating' => $minRating], 'rating DESC, company_name ASC');
    }
}
