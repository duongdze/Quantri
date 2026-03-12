<?php
require_once 'models/TourPolicy.php';

class PolicyController
{
    protected $model;

    public function __construct()
    {
        $this->model = new TourPolicy();
    }

    /**
     * Validate policy data
     */
    protected function validatePolicyData($data, $isUpdate = false)
    {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'Tên chính sách không được để trống';
        } elseif (strlen(trim($data['name'])) > 255) {
            $errors['name'] = 'Tên chính sách không được vượt quá 255 ký tự';
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
                $errors['name'] = 'Tên chính sách đã tồn tại';
            }
        }

        return $errors;
    }

    /**
     * List all policies
     */
    public function index()
    {
        $policies = $this->model->getAllPolicies();

        // Get tour count for each policy
        require_once 'models/TourPolicyAssignment.php';
        $assignmentModel = new TourPolicyAssignment();
        
        $policiesWithCounts = [];
        foreach ($policies as $policy) {
            $assignments = $assignmentModel->getByPolicyId($policy['id']);
            $policy['tour_count'] = count($assignments);
            $policiesWithCounts[] = $policy;
        }
        $policies = $policiesWithCounts;

        $title = 'Quản lý Chính sách Tour';
        require_once PATH_VIEW_ADMIN . 'pages/policies/index.php';
    }

    /**
     * Show create form
     */
    public function create()
    {
        // Clear old input when entering create form
        unset($_SESSION['old_input']);
        unset($_SESSION['form_errors']);

        $title = 'Thêm Chính sách Mới';
        require_once PATH_VIEW_ADMIN . 'pages/policies/form.php';
    }

    /**
     * Store new policy
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL_ADMIN . '&action=policies');
            return;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? '')
        ];

        // Validate data
        $errors = $this->validatePolicyData($data);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_input'] = $data;
            header('Location: ' . BASE_URL_ADMIN . '&action=policies/create');
            return;
        }

        try {
            $result = $this->model->insertPolicy($data);
            $_SESSION['success'] = 'Thêm chính sách thành công';
            header('Location: ' . BASE_URL_ADMIN . '&action=policies');
        } catch (Exception $e) {
            error_log('Error creating policy: ' . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi thêm chính sách. Vui lòng thử lại.';
            $_SESSION['old_input'] = $data;
            header('Location: ' . BASE_URL_ADMIN . '&action=policies/create');
        }
    }

    /**
     * Show edit form
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Thiếu thông tin chính sách';
            header('Location: ' . BASE_URL_ADMIN . '&action=policies');
            return;
        }

        $policy = $this->model->findById($id);
        if (!$policy) {
            $_SESSION['error'] = 'Không tìm thấy chính sách';
            header('Location: ' . BASE_URL_ADMIN . '&action=policies');
            return;
        }

        $title = 'Chỉnh sửa Chính sách';

        // Clear old input when entering edit form
        unset($_SESSION['old_input']);
        unset($_SESSION['form_errors']);

        require_once PATH_VIEW_ADMIN . 'pages/policies/form.php';
    }

    /**
     * Update policy
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL_ADMIN . '&action=policies');
            return;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Thiếu thông tin chính sách';
            header('Location: ' . BASE_URL_ADMIN . '&action=policies');
            return;
        }

        $policy = $this->model->findById($id);
        if (!$policy) {
            $_SESSION['error'] = 'Không tìm thấy chính sách';
            header('Location: ' . BASE_URL_ADMIN . '&action=policies');
            return;
        }

        $data = [
            'id' => $id,
            'current_name' => $policy['name'],
            'name' => trim($_POST['name'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? '')
        ];

        // Validate data
        $errors = $this->validatePolicyData($data, true);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_input'] = $data;
            header('Location: ' . BASE_URL_ADMIN . '&action=policies/edit&id=' . $id);
            return;
        }

        try {
            unset($data['current_name']);
            unset($data['id']);

            $this->model->updateById($id, $data);
            $_SESSION['success'] = 'Cập nhật chính sách thành công';
            header('Location: ' . BASE_URL_ADMIN . '&action=policies');
        } catch (Exception $e) {
            error_log('Error updating policy: ' . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật chính sách. Vui lòng thử lại.';
            $_SESSION['old_input'] = $data;
            header('Location: ' . BASE_URL_ADMIN . '&action=policies/edit&id=' . $id);
        }
    }

    /**
     * Delete policy
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Thiếu thông tin chính sách';
            header('Location: ' . BASE_URL_ADMIN . '&action=policies');
            return;
        }

        try {
            $this->model->deletePolicy($id);
            $_SESSION['success'] = 'Xóa chính sách thành công';
        } catch (Exception $e) {
            error_log('Error deleting policy: ' . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ' . BASE_URL_ADMIN . '&action=policies');
    }
}
