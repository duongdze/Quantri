<?php
require_once 'models/TourVehicle.php';
require_once 'models/BusCompany.php';
require_once 'models/TourAssignment.php';

class TourVehicleController
{
    private $model;
    private $busCompanyModel;
    private $assignmentModel;

    public function __construct()
    {
        $this->model = new TourVehicle();
        $this->busCompanyModel = new BusCompany();
        $this->assignmentModel = new TourAssignment();
    }

    /**
     * Danh sách xe của một tour assignment
     */
    public function index()
    {
        $assignment_id = $_GET['assignment_id'] ?? null;

        if (!$assignment_id) {
            $_SESSION['error'] = 'Không xác định được chuyến đi';
            header('Location: ' . BASE_URL_ADMIN); // Hoặc trang danh sách tour
            exit;
        }

        $vehicles = $this->model->getByTourAssignment($assignment_id);
        $assignment = $this->assignmentModel->getById($assignment_id); // Cần đảm bảo method này tồn tại hoặc dùng query raw

        // Nếu getById không có trong TourAssignment, ta có thể cần tự query hoặc dummy data trước
        // Tạm thời giả định getById tồn tại hoặc sẽ fix sau.

        require_once PATH_VIEW_ADMIN . 'pages/tour_vehicles/index.php';
    }

    /**
     * Form thêm xe
     */
    public function create()
    {
        $assignment_id = $_GET['assignment_id'] ?? null;
        if (!$assignment_id) {
            $_SESSION['error'] = 'Thiếu ID chuyến đi';
            header('Location: ' . BASE_URL_ADMIN);
            exit;
        }

        $busCompanies = $this->busCompanyModel->getAll(); // Cần method getAll
        $assignment = $this->assignmentModel->getById($assignment_id);

        require_once PATH_VIEW_ADMIN . 'pages/tour_vehicles/form.php';
    }

    /**
     * Lưu xe mới
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL_ADMIN);
            exit;
        }

        $assignment_id = $_POST['tour_assignment_id'] ?? null;
        if (!$assignment_id) {
            $_SESSION['error'] = 'Lỗi dữ liệu hệ thống';
            header('Location: ' . BASE_URL_ADMIN);
            exit;
        }

        // Validate
        $vehicle_plate = $_POST['vehicle_plate'] ?? '';
        $driver_name = $_POST['driver_name'] ?? '';
        $driver_phone = $_POST['driver_phone'] ?? '';
        $bus_company_id = $_POST['bus_company_id'] ?? null;

        if (empty($vehicle_plate)) {
            $_SESSION['error'] = 'Vui lòng nhập biển số xe';
            header('Location: ' . BASE_URL_ADMIN . '&action=tour_vehicles/create&assignment_id=' . $assignment_id);
            exit;
        }

        $data = [
            'tour_assignment_id' => $assignment_id,
            'bus_company_id' => $bus_company_id,
            'vehicle_plate' => $vehicle_plate,
            'vehicle_type' => $_POST['vehicle_type'] ?? '',
            'vehicle_brand' => $_POST['vehicle_brand'] ?? '',
            'driver_name' => $driver_name,
            'driver_phone' => $driver_phone,
            'driver_license' => $_POST['driver_license'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'status' => 'assigned'
        ];

        try {
            $this->model->insert($data); // BaseModel insert method
            $_SESSION['success'] = 'Đã thêm xe thành công';
            header('Location: ' . BASE_URL_ADMIN . '&action=tour_vehicles&assignment_id=' . $assignment_id);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
            header('Location: ' . BASE_URL_ADMIN . '&action=tour_vehicles/create&assignment_id=' . $assignment_id);
        }
    }

    /**
     * Form sửa xe
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy xe';
            header('Location: ' . BASE_URL_ADMIN);
            exit;
        }

        $vehicle = $this->model->getById($id);
        if (!$vehicle) {
            $_SESSION['error'] = 'Dữ liệu xe không tồn tại';
            header('Location: ' . BASE_URL_ADMIN);
            exit;
        }

        $assignment_id = $vehicle['tour_assignment_id'];
        $busCompanies = $this->busCompanyModel->getAll();

        require_once PATH_VIEW_ADMIN . 'pages/tour_vehicles/form.php';
    }

    /**
     * Cập nhật xe
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            exit;
        }

        $id = $_POST['id'] ?? null;
        $assignment_id = $_POST['tour_assignment_id'] ?? null;

        $data = [
            'bus_company_id' => $_POST['bus_company_id'] ?? null,
            'vehicle_plate' => $_POST['vehicle_plate'] ?? '',
            'vehicle_type' => $_POST['vehicle_type'] ?? '',
            'vehicle_brand' => $_POST['vehicle_brand'] ?? '',
            'driver_name' => $_POST['driver_name'] ?? '',
            'driver_phone' => $_POST['driver_phone'] ?? '',
            'driver_license' => $_POST['driver_license'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'status' => $_POST['status'] ?? 'assigned'
        ];

        try {
            $this->model->update($data, 'id=:id', ['id' => $id]);
            $_SESSION['success'] = 'Cập nhật xe thành công';
            header('Location: ' . BASE_URL_ADMIN . '&action=tour_vehicles&assignment_id=' . trim($assignment_id));
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
            header('Location: ' . BASE_URL_ADMIN . '&action=tour_vehicles/edit&id=' . $id);
        }
    }

    /**
     * Xóa xe
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        $assignment_id = $_GET['assignment_id'] ?? null;

        if ($id) {
            $this->model->delete('id=:id', ['id' => $id]);
            $_SESSION['success'] = 'Đã xóa xe khỏi tour';
        }

        if ($assignment_id) {
            header('Location: ' . BASE_URL_ADMIN . '&action=tour_vehicles&assignment_id=' . $assignment_id);
        } else {
            header('Location: ' . BASE_URL_ADMIN);
        }
    }
    /**
     * AJAX: Lấy lịch sử xe của nhà xe
     */
    public function getHistoryByCompany()
    {
        $bus_company_id = $_GET['bus_company_id'] ?? null;

        if (!$bus_company_id) {
            echo json_encode([]);
            exit;
        }

        // Query distinct vehicles from history
        // Use raw query for DISTINCT as BaseModel might not support it easily
        $sql = "SELECT DISTINCT vehicle_plate, vehicle_type, vehicle_brand, driver_name, driver_phone, driver_license 
                FROM tour_vehicles 
                WHERE bus_company_id = :bus_company_id 
                AND vehicle_plate IS NOT NULL 
                AND vehicle_plate != ''
                ORDER BY created_at DESC";

        $model = new TourVehicle();
        $stmt = $model->getPdo()->prepare($sql);
        $stmt->execute(['bus_company_id' => $bus_company_id]);
        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($vehicles);
        exit;
    }
}
