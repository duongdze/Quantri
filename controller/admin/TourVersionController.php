<?php
require_once 'models/TourVersion.php';
require_once 'models/TourVersionPrice.php';
require_once 'models/Booking.php';
require_once 'models/TourDeparture.php';

class TourVersionController
{
    protected $model;
    protected $tourModel;
    protected $priceModel;

    public function __construct()
    {
        $this->model = new TourVersion();
        require_once 'models/Tour.php';
        $this->tourModel = new Tour();
        $this->priceModel = new TourVersionPrice();
    }

    /**
     * Validate tour version data
     */
    protected function validateVersionData($data, $isUpdate = false)
    {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'Tên phiên bản không được để trống';
        } elseif (strlen(trim($data['name'])) > 255) {
            $errors['name'] = 'Tên phiên bản không được vượt quá 255 ký tự';
        }
        return $errors;
    }

    /**
     * Get tour ID from request
     */
    protected function getTourIdFromRequest()
    {
        return $_GET['tour_id'] ?? $_POST['tour_id'] ?? null;
    }

    /**
     * List all versions
     */
    public function index()
    {
        $versions = $this->model->getAllVersions();

        // Get statistics
        $totalVersions = $this->model->countTotal();
        $activeVersions = $this->model->countByStatus('active');
        $inactiveVersions = $this->model->countByStatus('inactive');

        $title = 'Quản lý phiên bản';
        require_once PATH_VIEW_ADMIN . 'pages/tours_versions/index.php';
    }

    /**
     * Show create form
     */
    public function create()
    {
        // Default prices (% tăng/giảm)
        $prices = [
            'adult_percent' => 0,
            'child_percent' => 0,
            'infant_percent' => 0,
            'child_base_percent' => 75,
            'infant_base_percent' => 50
        ];

        $title = 'Thêm phiên bản mới';
        require_once PATH_VIEW_ADMIN . 'pages/tours_versions/form.php';
    }

    /**
     * Store new version
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions');
            return;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status' => isset($_POST['status']) && in_array($_POST['status'], ['active', 'inactive']) ?
                $_POST['status'] : 'inactive',
            'created_at' => date('Y-m-d H:i:s'),
            // 'updated_at' => date('Y-m-d H:i:s')
        ];

        // Validate data
        $errors = $this->validateVersionData($data);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_input'] = $data;
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions/create');
            return;
        }

        try {
            $versionId = $this->model->insert($data);

            // Save prices (% tăng/giảm)
            $priceData = [
                'adult_percent' => floatval($_POST['adult_percent'] ?? 0),
                'child_percent' => floatval($_POST['child_percent'] ?? 0),
                'infant_percent' => floatval($_POST['infant_percent'] ?? 0),
                'child_base_percent' => floatval($_POST['child_base_percent'] ?? 75),
                'infant_base_percent' => floatval($_POST['infant_base_percent'] ?? 50)
            ];
            $this->priceModel->upsertPrice($versionId, $priceData);

            $_SESSION['success'] = 'Thêm phiên bản thành công';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions');
            return;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi khi thêm phiên bản: ' . $e->getMessage();
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions/create');
            return;
        }
    }

    /**
     * Show edit form
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'Thiếu thông tin phiên bản';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions');
            return;
        }

        $version = $this->model->findById($id);
        if (!$version) {
            $_SESSION['error'] = 'Không tìm thấy phiên bản';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions');
            return;
        }

        // Load prices
        $prices = $this->priceModel->getByVersionId($id);
        if (!$prices) {
            $prices = [
                'adult_percent' => 0,
                'child_percent' => 0,
                'infant_percent' => 0,
                'child_base_percent' => 75,
                'infant_base_percent' => 50
            ];
        }

        $title = 'Chỉnh sửa phiên bản';
        require_once PATH_VIEW_ADMIN . 'pages/tours_versions/form.php';
    }

    /**
     * Update version
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions');
            return;
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'Thiếu thông tin phiên bản';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions');
            return;
        }

        $version = $this->model->findById($id);
        if (!$version) {
            $_SESSION['error'] = 'Không tìm thấy phiên bản';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions');
            return;
        }

        $data = [
            'id' => $id,
            'current_name' => $version['name'], // For duplicate check
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status' => isset($_POST['status']) && in_array($_POST['status'], ['active', 'inactive']) ?
                $_POST['status'] : 'inactive',
            // 'updated_at' => date('Y-m-d H:i:s')
        ];

        // Validate data
        $errors = $this->validateVersionData($data, true);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_input'] = $data;
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions/edit&id=' . $id);
            return;
        }

        try {
            // Don't update created_at
            unset($data['current_name']);
            unset($data['id']);

            $this->model->update($data, 'id = :id', ['id' => $id]);

            // Update prices (% tăng/giảm)
            $priceData = [
                'adult_percent' => floatval($_POST['adult_percent'] ?? 0),
                'child_percent' => floatval($_POST['child_percent'] ?? 0),
                'infant_percent' => floatval($_POST['infant_percent'] ?? 0),
                'child_base_percent' => floatval($_POST['child_base_percent'] ?? 75),
                'infant_base_percent' => floatval($_POST['infant_base_percent'] ?? 50)
            ];
            $this->priceModel->upsertPrice($id, $priceData);

            $_SESSION['success'] = 'Cập nhật phiên bản thành công';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions');
        } catch (Exception $e) {
            error_log('Error updating tour version: ' . $e->getMessage());
            $_SESSION['error'] = 'Lỗi khi cập nhật phiên bản: ' . $e->getMessage();
            $_SESSION['old_input'] = $data;
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions/edit&id=' . $id);
        }
    }

    /**
     * Delete version
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'Thiếu thông tin phiên bản';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions');
            return;
        }

        $version = $this->model->findById($id);
        if (!$version) {
            $_SESSION['error'] = 'Không tìm thấy phiên bản';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions');
            return;
        }

        try {
            // 1. Check for existing bookings
            $bookingModel = new Booking();
            $bookingCount = $bookingModel->count('version_id = :id', ['id' => $id]);
            if ($bookingCount > 0) {
                $_SESSION['error'] = "Không thể xóa phiên bản này vì đang có $bookingCount booking sử dụng.";
                header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions');
                return;
            }

            // 2. Check for existing tour departures
            $departureModel = new TourDeparture();
            $departureCount = $departureModel->count('version_id = :id', ['id' => $id]);
            if ($departureCount > 0) {
                $_SESSION['error'] = "Không thể xóa phiên bản này vì đang có $departureCount lịch khởi hành sử dụng.";
                header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions');
                return;
            }

            // Delete prices first (cascade)
            $this->priceModel->deleteByVersionId($id);

            // Then delete version
            $this->model->delete('id = :id', ['id' => $id]);
            $_SESSION['success'] = 'Xóa phiên bản thành công';
        } catch (Exception $e) {
            error_log('Error deleting tour version: ' . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi xóa phiên bản. Vui lòng thử lại.';
        }

        header('Location: ' . BASE_URL_ADMIN . '&action=tours_versions');
    }

    /**
     * Toggle version status
     */
    public function toggleStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['_method']) || $_POST['_method'] !== 'PATCH') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? null;

        if (!$id || !in_array($status, ['active', 'inactive'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        try {
            $version = $this->model->findById($id);
            if (!$version) {
                throw new Exception('Version not found');
            }

            $this->model->updateById($id, [
                'status' => $status,
                // 'updated_at' => date('Y-m-d H:i:s')
            ]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log('Error toggling version status: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    /**
     * Show tour version and policy mapping
     */
    public function tourMapping()
    {
        // Load all tours with version and policy info
        $pdo = BaseModel::getPdo();

        // Get tours with version and category info
        $stmt = $pdo->prepare("
            SELECT t.*, tc.name as category_name, tv.name as version_name, tv.description as version_description
            FROM tours t 
            LEFT JOIN tour_categories tc ON t.category_id = tc.id 
            LEFT JOIN tour_versions tv ON t.tour_version_id = tv.id
            ORDER BY t.name ASC
        ");
        $stmt->execute();
        $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Load policies for each tour
        foreach ($tours as &$tour) {
            $stmt = $pdo->prepare("
                SELECT p.* FROM tour_policies p
                INNER JOIN tour_policy_assignments tpa ON p.id = tpa.policy_id
                WHERE tpa.tour_id = :tour_id
            ");
            $stmt->execute(['tour_id' => $tour['id']]);
            $tour['policies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Load all versions
        $versions = $this->model->getAllVersions();

        // Load all policies
        require_once 'models/TourPolicy.php';
        $policyModel = new TourPolicy();
        $policies = $policyModel->select();

        // Load version-policies mapping
        $versionPolicies = [];
        foreach ($versions as $version) {
            $stmt = $pdo->prepare("
                SELECT p.* FROM tour_policies p
                INNER JOIN tour_version_policies tvp ON p.id = tvp.policy_id
                WHERE tvp.version_id = :version_id
            ");
            $stmt->execute(['version_id' => $version['id']]);
            $versionPolicies[] = [
                'version' => $version,
                'version_name' => $version['name'],
                'policies' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        }

        require_once PATH_VIEW_ADMIN . 'pages/tours_versions/tour_mapping.php';
    }
}
