<?php
require_once 'models/BaseModel.php';

class TourCategory extends BaseModel
{
    protected $table = 'tour_categories';
    protected $columns = [
        'id',
        'name',
        'slug',
        'description',
        'icon',
        'created_at',
        'updated_at'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all categories
     */
    public function getAllCategories()
    {
        return $this->select('*', '1 ORDER BY name ASC');
    }

    /**
     * Get category with tour count
     */
    public function getCategoryWithTourCount($categoryId)
    {
        $sql = "SELECT tc.*, COUNT(t.id) as tour_count 
                FROM {$this->table} tc 
                LEFT JOIN tours t ON tc.id = t.category_id 
                WHERE tc.id = :id 
                GROUP BY tc.id";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['id' => $categoryId]);
        return $stmt->fetch();
    }

    /**
     * Create slug from name
     */
    public function createSlug($name, $id = null)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

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
     * Find category by ID
     */
    public function findById($id)
    {
        return $this->find('*', 'id = :id', ['id' => $id]);
    }

    /**
     * Find category by slug
     */
    public function findBySlug($slug)
    {
        return $this->find('*', 'slug = :slug', ['slug' => $slug]);
    }

    /**
     * Update category by ID
     */
    public function updateById($id, $data)
    {
        // Auto-generate slug if not provided
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = $this->createSlug($data['name'], $id);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($data, 'id = :id', ['id' => $id]);
    }

    /**
     * Insert new category
     */
    public function insertCategory($data)
    {
        // Auto-generate slug if not provided
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = $this->createSlug($data['name']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->insert($data);
    }

    /**
     * Delete category (check if it has tours)
     */
    public function deleteCategory($id)
    {
        // Check if category has tours
        require_once 'models/Tour.php';
        $tourModel = new Tour();
        $tours = $tourModel->select('id', 'category_id = :category_id', ['category_id' => $id]);

        if (!empty($tours)) {
            throw new Exception('Không thể xóa danh mục này vì đang có tour sử dụng.');
        }

        return $this->delete('id = :id', ['id' => $id]);
    }
}
