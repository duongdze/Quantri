<?php
require_once 'models/TourCategory.php';

class TourCategoryController
{
    protected $model;
    protected $tourModel;

    public function __construct()
    {
        $this->model = new TourCategory();
        require_once 'models/Tour.php';
        $this->tourModel = new Tour();
    }

    /**
     * Validate category data
     */
    protected function validateCategoryData($data, $isUpdate = false)
    {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'Tên danh mục không được để trống';
        } elseif (strlen(trim($data['name'])) > 255) {
            $errors['name'] = 'Tên danh mục không được vượt quá 255 ký tự';
        }

        // Validate slug if provided
        if (!empty(trim($data['slug'] ?? ''))) {
            $slug = trim($data['slug']);

            // Check slug format
            if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
                $errors['slug'] = 'Slug chỉ được chứa chữ thường, số và dấu gạch ngang';
            }

            // Check for duplicate slug
            $where = 'slug = :slug';
            $params = ['slug' => $slug];

            if ($isUpdate) {
                $where .= ' AND id != :id';
                $params['id'] = $data['id'];
            }

            $existing = $this->model->select('id', $where, $params);
            if (!empty($existing)) {
                $errors['slug'] = 'Slug đã tồn tại';
            }
        }

        // Check for duplicate name
        if (!$isUpdate || ($isUpdate && $data['name'] !== $data['current_name'])) {
            $where = 'name = :name';
            $params = ['name' => $data['name']];

            if ($isUpdate) {
                $where .= ' AND id != :id';
                $params['id'] = $data['id'];
            }

            $existing = $this->model->select('id', $where, $params);
            if (!empty($existing)) {
                $errors['name'] = 'Tên danh mục đã tồn tại';
            }
        }

        return $errors;
    }

    /**
     * List all categories
     */
    public function index()
    {
        $categories = $this->model->getAllCategories();

        // Debug: Log loaded categories
        error_log('DEBUG: Loaded categories: ' . print_r($categories, true));

        // Get tour counts for each category
        $categoriesWithCounts = [];
        foreach ($categories as $category) {
            $category['tour_count'] = $this->tourModel->select('COUNT(*) as count', 'category_id = :category_id', ['category_id' => $category['id']])[0]['count'] ?? 0;
            $categoriesWithCounts[] = $category;
        }
        $categories = $categoriesWithCounts;

        // Debug: Log final categories with tour counts
        error_log('DEBUG: Final categories with tour counts: ' . print_r($categories, true));

        $title = 'Quản lý Danh mục Tour';
        require_once PATH_VIEW_ADMIN . 'pages/tours_categories/index.php';
    }

    /**
     * Show create form
     */
    public function create()
    {
        // Clear old input when entering create form
        unset($_SESSION['old_input']);
        unset($_SESSION['form_errors']);

        $title = 'Thêm Danh mục Mới';
        require_once PATH_VIEW_ADMIN . 'pages/tours_categories/form.php';
    }

    /**
     * Store new category
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_categories');
            return;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'icon' => trim($_POST['icon'] ?? '')
        ];

        // Validate data
        $errors = $this->validateCategoryData($data);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_input'] = $data;
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_categories/create');
            return;
        }

        try {
            // Debug: Log data before insert
            error_log('DEBUG: Inserting category data: ' . print_r($data, true));

            $result = $this->model->insertCategory($data);
            error_log('DEBUG: Insert result ID: ' . $result);

            $_SESSION['success'] = 'Thêm danh mục thành công';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_categories');
        } catch (Exception $e) {
            error_log('Error creating tour category: ' . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi thêm danh mục. Vui lòng thử lại.';
            $_SESSION['old_input'] = $data;
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_categories/create');
        }
    }

    /**
     * Show edit form
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Thiếu thông tin danh mục';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_categories');
            return;
        }

        $category = $this->model->findById($id);
        if (!$category) {
            $_SESSION['error'] = 'Không tìm thấy danh mục';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_categories');
            return;
        }

        $title = 'Chỉnh sửa Danh mục';

        // Clear old input when entering edit form
        unset($_SESSION['old_input']);
        unset($_SESSION['form_errors']);

        require_once PATH_VIEW_ADMIN . 'pages/tours_categories/form.php';
    }

    /**
     * Update category
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_categories');
            return;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Thiếu thông tin danh mục';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_categories');
            return;
        }

        $category = $this->model->findById($id);
        if (!$category) {
            $_SESSION['error'] = 'Không tìm thấy danh mục';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_categories');
            return;
        }

        $data = [
            'id' => $id,
            'current_name' => $category['name'],
            'name' => trim($_POST['name'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'icon' => trim($_POST['icon'] ?? '')
        ];

        // Validate data
        $errors = $this->validateCategoryData($data, true);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_input'] = $data;
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_categories/edit&id=' . $id);
            return;
        }

        try {
            unset($data['current_name']);
            unset($data['id']);

            $this->model->updateById($id, $data);
            $_SESSION['success'] = 'Cập nhật danh mục thành công';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_categories');
        } catch (Exception $e) {
            error_log('Error updating tour category: ' . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật danh mục. Vui lòng thử lại.';
            $_SESSION['old_input'] = $data;
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_categories/edit&id=' . $id);
        }
    }

    /**
     * Delete category
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Thiếu thông tin danh mục';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_categories');
            return;
        }

        try {
            $this->model->deleteCategory($id);
            $_SESSION['success'] = 'Xóa danh mục thành công';
        } catch (Exception $e) {
            error_log('Error deleting tour category: ' . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ' . BASE_URL_ADMIN . '&action=tours_categories');
    }
}
