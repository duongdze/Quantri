<?php
require_once 'models/BusCompany.php';

class BusCompanyController
{
    private $model;

    public function __construct()
    {
        $this->model = new BusCompany();
    }

    /**
     * Hiển thị danh sách nhà xe
     */
    public function index()
    {
        // Lấy tham số filter
        $keyword = $_GET['keyword'] ?? '';
        $ratingMin = $_GET['rating_min'] ?? null;

        // Lấy danh sách nhà xe
        if (!empty($keyword) || $ratingMin !== null) {
            $busCompanies = $this->model->search($keyword, $ratingMin);
        } else {
            $busCompanies = $this->model->getAll();
        }

        // Lấy thống kê
        $stats = $this->model->getStats();

        require_once PATH_VIEW_ADMIN . 'pages/bus_companies/index.php';
    }

    /**
     * Hiển thị form tạo nhà xe mới
     */
    public function create()
    {
        require_once PATH_VIEW_ADMIN . 'pages/bus_companies/create.php';
    }

    /**
     * Lưu nhà xe mới
     */
    public function store()
    {
        try {
            // Validate input
            $errors = $this->validateBusCompany($_POST);

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $_POST;
                header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies/create');
                exit;
            }

            // Kiểm tra mã nhà xe trùng
            if ($this->model->companyCodeExists($_POST['company_code'])) {
                $_SESSION['error'] = 'Mã nhà xe đã tồn tại!';
                $_SESSION['old'] = $_POST;
                header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies/create');
                exit;
            }

            // Kiểm tra SĐT trùng
            if (!empty($_POST['phone']) && $this->model->phoneExists($_POST['phone'])) {
                $_SESSION['error'] = 'Số điện thoại đã tồn tại!';
                $_SESSION['old'] = $_POST;
                header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies/create');
                exit;
            }

            // Chuẩn bị dữ liệu
            $data = [
                'company_code' => trim($_POST['company_code']),
                'company_name' => trim($_POST['company_name']),
                'contact_person' => !empty($_POST['contact_person']) ? trim($_POST['contact_person']) : null,
                'phone' => !empty($_POST['phone']) ? trim($_POST['phone']) : null,
                'email' => !empty($_POST['email']) ? trim($_POST['email']) : null,
                'address' => !empty($_POST['address']) ? trim($_POST['address']) : null,
                'business_license' => !empty($_POST['business_license']) ? trim($_POST['business_license']) : null,
                'vehicle_type' => !empty($_POST['vehicle_type']) ? trim($_POST['vehicle_type']) : null,
                'vehicle_brand' => !empty($_POST['vehicle_brand']) ? trim($_POST['vehicle_brand']) : null,
                'total_vehicles' => !empty($_POST['total_vehicles']) ? intval($_POST['total_vehicles']) : 0,
                'status' => $_POST['status'] ?? 'active',
                'rating' => !empty($_POST['rating']) ? floatval($_POST['rating']) : 5.00,
                'notes' => !empty($_POST['notes']) ? trim($_POST['notes']) : null
            ];

            $id = $this->model->insert($data);

            if ($id) {
                $_SESSION['success'] = 'Thêm nhà xe thành công!';
                header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies');
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi thêm nhà xe!';
                header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies/create');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
            header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies/create');
        }
        exit;
    }

    /**
     * Hiển thị form sửa nhà xe
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy nhà xe!';
            header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies');
            exit;
        }

        $busCompany = $this->model->getById($id);

        if (!$busCompany) {
            $_SESSION['error'] = 'Không tìm thấy nhà xe!';
            header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies');
            exit;
        }

        require_once PATH_VIEW_ADMIN . 'pages/bus_companies/edit.php';
    }

    /**
     * Cập nhật nhà xe
     */
    public function update()
    {
        try {
            $id = $_POST['id'] ?? null;

            if (!$id) {
                $_SESSION['error'] = 'Không tìm thấy nhà xe!';
                header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies');
                exit;
            }

            // Validate input
            $errors = $this->validateBusCompany($_POST, $id);

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $_POST;
                header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies/edit&id=' . $id);
                exit;
            }

            // Kiểm tra mã nhà xe trùng (trừ bản ghi hiện tại)
            if ($this->model->companyCodeExists($_POST['company_code'], $id)) {
                $_SESSION['error'] = 'Mã nhà xe đã tồn tại!';
                $_SESSION['old'] = $_POST;
                header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies/edit&id=' . $id);
                exit;
            }

            // Kiểm tra SĐT trùng (trừ bản ghi hiện tại)
            if (!empty($_POST['phone']) && $this->model->phoneExists($_POST['phone'], $id)) {
                $_SESSION['error'] = 'Số điện thoại đã tồn tại!';
                $_SESSION['old'] = $_POST;
                header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies/edit&id=' . $id);
                exit;
            }

            // Chuẩn bị dữ liệu
            $data = [
                'company_code' => trim($_POST['company_code']),
                'company_name' => trim($_POST['company_name']),
                'contact_person' => !empty($_POST['contact_person']) ? trim($_POST['contact_person']) : null,
                'phone' => !empty($_POST['phone']) ? trim($_POST['phone']) : null,
                'email' => !empty($_POST['email']) ? trim($_POST['email']) : null,
                'address' => !empty($_POST['address']) ? trim($_POST['address']) : null,
                'business_license' => !empty($_POST['business_license']) ? trim($_POST['business_license']) : null,
                'vehicle_type' => !empty($_POST['vehicle_type']) ? trim($_POST['vehicle_type']) : null,
                'vehicle_brand' => !empty($_POST['vehicle_brand']) ? trim($_POST['vehicle_brand']) : null,
                'total_vehicles' => !empty($_POST['total_vehicles']) ? intval($_POST['total_vehicles']) : 0,
                'status' => $_POST['status'] ?? 'active',
                'rating' => !empty($_POST['rating']) ? floatval($_POST['rating']) : 5.00,
                'notes' => !empty($_POST['notes']) ? trim($_POST['notes']) : null
            ];

            $result = $this->model->update($data, 'id = :id', ['id' => $id]);

            if ($result) {
                $_SESSION['success'] = 'Cập nhật nhà xe thành công!';
            } else {
                $_SESSION['error'] = 'Không có thay đổi nào được thực hiện!';
            }

            header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
            header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies/edit&id=' . $id);
        }
        exit;
    }

    /**
     * Xóa nhà xe
     */
    public function delete()
    {
        try {
            $id = $_POST['id'] ?? null;

            if (!$id) {
                $_SESSION['error'] = 'Không tìm thấy nhà xe!';
                header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies');
                exit;
            }

            $result = $this->model->delete('id = :id', ['id' => $id]);

            if ($result) {
                $_SESSION['success'] = 'Xóa nhà xe thành công!';
            } else {
                $_SESSION['error'] = 'Không thể xóa nhà xe!';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies');
        exit;
    }

    /**
     * Hiển thị chi tiết nhà xe
     */
    public function detail()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy nhà xe!';
            header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies');
            exit;
        }

        $busCompany = $this->model->getById($id);

        if (!$busCompany) {
            $_SESSION['error'] = 'Không tìm thấy nhà xe!';
            header('Location: ' . BASE_URL_ADMIN . '&action=bus-companies');
            exit;
        }

        require_once PATH_VIEW_ADMIN . 'pages/bus_companies/detail.php';
    }

    /**
     * Validate dữ liệu nhà xe
     */
    private function validateBusCompany($data, $excludeId = null)
    {
        $errors = [];

        if (empty($data['company_code'])) {
            $errors['company_code'] = 'Vui lòng nhập mã nhà xe!';
        }

        if (empty($data['company_name'])) {
            $errors['company_name'] = 'Vui lòng nhập tên nhà xe!';
        }

        if (!empty($data['phone']) && !preg_match('/^[0-9]{10,11}$/', $data['phone'])) {
            $errors['phone'] = 'Số điện thoại không hợp lệ!';
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ!';
        }

        return $errors;
    }
}
