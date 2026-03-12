<?php
require_once 'models/BaseModel.php';

class TourPolicy extends BaseModel
{
    protected $table = 'tour_policies';
    protected $columns = [
        'id',
        'name',
        'slug',
        'description',
        'created_at',
        'updated_at'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all policies
     */
    public function getAllPolicies()
    {
        return $this->select('*', '1 ORDER BY name ASC');
    }

    /**
     * Create slug from name
     */
    public function createSlug($name, $id = null)
    {
        // Convert Vietnamese characters to ASCII
        $slug = $this->removeVietnameseTones($name);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slug)));

        // Check if slug exists
        $where = 'slug = :slug';
        $params = ['slug' => $slug];

        if ($id) {
            $where .= ' AND id != :id';
            $params['id'] = $id;
        }

        $existing = $this->select('id', $where, $params);

        if (!empty($existing)) {
            $slug .= '-' . time();
        }

        return $slug;
    }

    /**
     * Remove Vietnamese tones for slug generation
     */
    private function removeVietnameseTones($str)
    {
        $vietnameseTones = [
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
            'đ',
            'À', 'Á', 'Ạ', 'Ả', 'Ã', 'Â', 'Ầ', 'Ấ', 'Ậ', 'Ẩ', 'Ẫ', 'Ă', 'Ằ', 'Ắ', 'Ặ', 'Ẳ', 'Ẵ',
            'È', 'É', 'Ẹ', 'Ẻ', 'Ẽ', 'Ê', 'Ề', 'Ế', 'Ệ', 'Ể', 'Ễ',
            'Ì', 'Í', 'Ị', 'Ỉ', 'Ĩ',
            'Ò', 'Ó', 'Ọ', 'Ỏ', 'Õ', 'Ô', 'Ồ', 'Ố', 'Ộ', 'Ổ', 'Ỗ', 'Ơ', 'Ờ', 'Ớ', 'Ợ', 'Ở', 'Ỡ',
            'Ù', 'Ú', 'Ụ', 'Ủ', 'Ũ', 'Ư', 'Ừ', 'Ứ', 'Ự', 'Ử', 'Ữ',
            'Ỳ', 'Ý', 'Ỵ', 'Ỷ', 'Ỹ',
            'Đ'
        ];

        $replacements = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd',
            'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A',
            'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E',
            'I', 'I', 'I', 'I', 'I',
            'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O',
            'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U',
            'Y', 'Y', 'Y', 'Y', 'Y',
            'D'
        ];

        return str_replace($vietnameseTones, $replacements, $str);
    }

    /**
     * Find policy by ID
     */
    public function findById($id)
    {
        return $this->find('*', 'id = :id', ['id' => $id]);
    }

    /**
     * Find policy by slug
     */
    public function findBySlug($slug)
    {
        return $this->find('*', 'slug = :slug', ['slug' => $slug]);
    }

    /**
     * Update policy by ID
     */
    public function updateById($id, $data)
    {
        // Auto-generate slug if not provided
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = $this->createSlug($data['name'], $id);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($data, 'id = :id', ['id' => $id]);
    }

    /**
     * Insert new policy
     */
    public function insertPolicy($data)
    {
        // Auto-generate slug if not provided
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = $this->createSlug($data['name']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->insert($data);
    }

    /**
     * Delete policy (check if it has tour assignments)
     */
    public function deletePolicy($id)
    {
        // Check if policy has tour assignments
        require_once 'models/TourPolicyAssignment.php';
        $assignmentModel = new TourPolicyAssignment();
        $assignments = $assignmentModel->getByPolicyId($id);

        if (!empty($assignments)) {
            throw new Exception('Không thể xóa chính sách này vì đang có tour sử dụng.');
        }

        return $this->delete('id = :id', ['id' => $id]);
    }
}
