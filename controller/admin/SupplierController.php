<?php
require_once 'models/Supplier.php';
require_once 'models/SupplierContract.php'; // Add SupplierContract model

class SupplierController
{
    protected $model;
    protected $contractModel;

    public function __construct()
    {
        $this->model = new Supplier();
        $this->contractModel = new SupplierContract();
    }

    public function index()
    {
        // Get search and filter parameters
        $keyword = $_GET['keyword'] ?? '';
        $type = $_GET['type'] ?? '';
        $rating_min = $_GET['rating_min'] ?? '';

        // Build WHERE conditions
        $conditions = [];
        $params = [];

        if (!empty($keyword)) {
            $conditions[] = "(name LIKE ? OR contact_person LIKE ? OR phone LIKE ? OR email LIKE ?)";
            $searchTerm = "%$keyword%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if (!empty($type)) {
            $conditions[] = "type = ?";
            $params[] = $type;
        }

        if (!empty($rating_min)) {
            $conditions[] = "rating >= ?";
            $params[] = (float)$rating_min;
        }

        // Execute query
        $where = !empty($conditions) ? implode(' AND ', $conditions) : null;
        $suppliers = $this->model->select('*', $where, $params);

        // Calculate statistics
        $allSuppliers = $this->model->select();
        $stats = [
            'total' => count($allSuppliers),
            'active' => count(array_filter($allSuppliers, function ($s) {
                return !empty($s['rating']) && $s['rating'] >= 3;
            })),
            'high_rated' => count(array_filter($allSuppliers, function ($s) {
                return !empty($s['rating']) && $s['rating'] >= 4;
            })),
            'avg_rating' => !empty($allSuppliers) ?
                array_sum(array_column($allSuppliers, 'rating')) / count($allSuppliers) : 0
        ];

        // Get unique types for filter dropdown
        $types = array_unique(array_filter(array_column($allSuppliers, 'type')));

        require_once 'views/admin/pages/suppliers/index.php';
    }

    public function create()
    {
        // $contracts is empty for create view
        $contracts = [];
        require_once 'views/admin/pages/suppliers/form.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: ' . BASE_URL_ADMIN . '&action=suppliers');
            exit;
        }

        try {
            // Validate required fields
            $required = ['name'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    $_SESSION['error'] = "Trường {$field} là bắt buộc";
                    header('Location: ' . BASE_URL_ADMIN . '&action=suppliers/create');
                    exit;
                }
            }

            // Prepare data
            $data = [
                'name' => trim($_POST['name']),
                'type' => trim($_POST['type'] ?? ''),
                'contact_person' => trim($_POST['contact_person'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'rating' => !empty($_POST['rating']) ? (float)$_POST['rating'] : null,
                'description' => trim($_POST['description'] ?? '')
            ];

            // Validate email format if provided
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Email không hợp lệ';
                header('Location: ' . BASE_URL_ADMIN . '&action=suppliers/create');
                exit;
            }

            // Validate rating range if provided
            if ($data['rating'] !== null && ($data['rating'] < 0 || $data['rating'] > 5)) {
                $_SESSION['error'] = 'Đánh giá phải từ 0 đến 5';
                header('Location: ' . BASE_URL_ADMIN . '&action=suppliers/create');
                exit;
            }

            // Insert into database
            $supplierId = $this->model->insert($data);

            if ($supplierId) {
                // Save contracts if any
                if (!empty($_POST['contracts']) && is_array($_POST['contracts'])) {
                    foreach ($_POST['contracts'] as $c) {
                        $contractData = [
                            'supplier_id' => $supplierId,
                            'contract_name' => trim($c['name'] ?? ''),
                            'start_date' => !empty($c['start_date']) ? trim($c['start_date']) : null,
                            'end_date' => !empty($c['end_date']) ? trim($c['end_date']) : null,
                            'price_info' => !empty($c['price']) ? trim($c['price']) : null,
                            'status' => 'active',
                            'notes' => trim($c['notes'] ?? '')
                        ];

                        if ($contractData['contract_name'] || $contractData['start_date'] || $contractData['end_date'] || $contractData['price_info'] || $contractData['notes']) {
                            $this->contractModel->insert($contractData);
                        }
                    }
                }

                $_SESSION['success'] = 'Thêm nhà cung cấp thành công';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi thêm nhà cung cấp';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL_ADMIN . '&action=suppliers');
        exit;
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'ID không hợp lệ';
            header('Location: ' . BASE_URL_ADMIN . '&action=suppliers');
            exit;
        }

        $supplier = $this->model->find('*', 'id = :id', ['id' => $id]);

        if (!$supplier) {
            $_SESSION['error'] = 'Không tìm thấy nhà cung cấp';
            header('Location: ' . BASE_URL_ADMIN . '&action=suppliers');
            exit;
        }

        // Fetch contracts for edit view
        $contracts = $this->contractModel->getBySupplierId($id);

        require_once 'views/admin/pages/suppliers/form.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: ' . BASE_URL_ADMIN . '&action=suppliers');
            exit;
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'ID không hợp lệ';
            header('Location: ' . BASE_URL_ADMIN . '&action=suppliers');
            exit;
        }

        try {
            // Validate required fields
            $required = ['name'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    $_SESSION['error'] = "Trường {$field} là bắt buộc";
                    header('Location: ' . BASE_URL_ADMIN . '&action=suppliers/edit&id=' . $id);
                    exit;
                }
            }

            // Prepare data
            $data = [
                'name' => trim($_POST['name']),
                'type' => trim($_POST['type'] ?? ''),
                'contact_person' => trim($_POST['contact_person'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'rating' => !empty($_POST['rating']) ? (float)$_POST['rating'] : null,
                'description' => trim($_POST['description'] ?? '')
            ];

            // Validate email format if provided
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Email không hợp lệ';
                header('Location: ' . BASE_URL_ADMIN . '&action=suppliers/edit&id=' . $id);
                exit;
            }

            // Validate rating range if provided
            if ($data['rating'] !== null && ($data['rating'] < 0 || $data['rating'] > 5)) {
                $_SESSION['error'] = 'Đánh giá phải từ 0 đến 5';
                header('Location: ' . BASE_URL_ADMIN . '&action=suppliers/edit&id=' . $id);
                exit;
            }

            // Update database
            $result = $this->model->update($data, 'id = :id', ['id' => $id]);

            if ($result !== false) {
                // Replace contracts: delete existing then insert provided
                // delete all existing for supplier
                $this->contractModel->delete('supplier_id = :sid', ['sid' => $id]);

                if (!empty($_POST['contracts']) && is_array($_POST['contracts'])) {
                    foreach ($_POST['contracts'] as $c) {
                        $contractData = [
                            'supplier_id' => $id,
                            'contract_name' => trim($c['name'] ?? ''),
                            'start_date' => !empty($c['start_date']) ? trim($c['start_date']) : null,
                            'end_date' => !empty($c['end_date']) ? trim($c['end_date']) : null,
                            'price_info' => !empty($c['price']) ? trim($c['price']) : null,
                            'status' => 'active',
                            'notes' => trim($c['notes'] ?? '')
                        ];

                        if ($contractData['contract_name'] || $contractData['start_date'] || $contractData['end_date'] || $contractData['price_info'] || $contractData['notes']) {
                            $this->contractModel->insert($contractData);
                        }
                    }
                }

                $_SESSION['success'] = 'Cập nhật nhà cung cấp thành công';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật nhà cung cấp';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL_ADMIN . '&action=suppliers');
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: ' . BASE_URL_ADMIN . '&action=suppliers');
            exit;
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'ID không hợp lệ';
            header('Location: ' . BASE_URL_ADMIN . '&action=suppliers');
            exit;
        }

        try {
            // Delete contracts
            $this->contractModel->delete('supplier_id = :sid', ['sid' => $id]);

            // Delete supplier
            $result = $this->model->delete('id = :id', ['id' => $id]);

            if ($result) {
                $_SESSION['success'] = 'Xóa nhà cung cấp thành công';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi xóa nhà cung cấp';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL_ADMIN . '&action=suppliers');
        exit;
    }

    public function detail()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'ID không hợp lệ';
            header('Location: ' . BASE_URL_ADMIN . '&action=suppliers');
            exit;
        }

        $supplier = $this->model->find('*', 'id = :id', ['id' => $id]);

        if (!$supplier) {
            $_SESSION['error'] = 'Không tìm thấy nhà cung cấp';
            header('Location: ' . BASE_URL_ADMIN . '&action=suppliers');
            exit;
        }

        // Fetch contracts
        $contracts = $this->contractModel->getBySupplierId($id);

        require_once 'views/admin/pages/suppliers/detail.php';
    }
}
