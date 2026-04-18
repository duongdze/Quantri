<?php
require_once 'models/TourLog.php';
require_once 'models/Tour.php';
require_once 'models/Guide.php';

class TourLogController
{
    protected $model;

    public function __construct()
    {
        check_role(['admin', 'guide']);
        $this->model = new TourLog();

        // Tự động cập nhật các tour đã kết thúc
        require_once 'models/TourAssignment.php';
        (new TourAssignment())->autoCompleteStaleAssignments();
    }

    public function index()
    {
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        $filters = [
            'keyword'  => $_GET['keyword'] ?? '',
            'guide_id' => $_GET['guide_id'] ?? '',
            'status'   => $_GET['status'] ?? ''
        ];

        $guideModel = new Guide();
        $allGuides = [];

        if ($userRole === 'guide') {
            // HDV chỉ xem tours của mình
            $guide = $guideModel->getByUserId($_SESSION['user']['user_id']);
            if ($guide) {
                // HDV thường không cần lọc theo chính mình, nhưng ta vẫn lấy dữ liệu
                $tours = $this->model->getToursWithLogStatsByGuide($guide['id']);
                // Ở đây nếu muốn hỗ trợ search trong list của HDV, ta có thể cải tiến Model thêm
            } else {
                $tours = [];
            }
        } else {
            // Admin xem tất cả và có thể lọc
            $tours = $this->model->getToursWithLogStats($filters);
            $allGuides = $guideModel->getAllWithName();
        }

        require_once PATH_VIEW_ADMIN . 'pages/tours_logs/index.php';
    }

    public function create()
    {
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        $tourModel = new Tour();
        $guideModel = new Guide();
        $assignmentModel = new TourAssignment();

        if ($userRole === 'guide') {
            // HDV chỉ chọn tours của mình
            $guide = $guideModel->getByUserId($_SESSION['user']['user_id']);
            if ($guide) {
                // Lấy các chuyến đi (assignments) đang hoạt động của HDV này
                $sql = "SELECT ta.id as assignment_id, t.name as tour_name, ta.start_date, ta.tour_id
                        FROM tour_assignments ta
                        INNER JOIN tours t ON ta.tour_id = t.id
                        WHERE ta.guide_id = :guide_id AND ta.status = 'active'";
                $pdo = $tourModel->getPDO();
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['guide_id' => $guide['id']]);
                $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $assignments = [];
            }
            $guides = [$guide];
        } else {
            // Admin chọn từ tất cả chuyến đi đang active/completed
            $sql = "SELECT ta.id as assignment_id, t.name as tour_name, ta.start_date, ta.tour_id, u.full_name as guide_name
                    FROM tour_assignments ta
                    INNER JOIN tours t ON ta.tour_id = t.id
                    INNER JOIN guides g ON ta.guide_id = g.id
                    INNER JOIN users u ON g.user_id = u.user_id
                    WHERE ta.status IN ('active', 'completed')
                    ORDER BY ta.start_date DESC";
            $pdo = $tourModel->getPDO();
            $stmt = $pdo->query($sql);
            $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $guides = $guideModel->getAllWithName();
        }

        $selectedAssignment = null;
        if (isset($_GET['assignment_id'])) {
            $selectedAssignment = $assignmentModel->getById($_GET['assignment_id']);
        }

        require_once PATH_VIEW_ADMIN . 'pages/tours_logs/create.php';
    }

    public function store()
    {
        $assignmentId = $_POST['assignment_id'] ?? null;
        $tourId = $_POST['tour_id'] ?? null;

        // Nếu có assignment_id mà chưa có tour_id, lấy tour_id từ assignment
        if ($assignmentId && !$tourId) {
            $assignmentModel = new TourAssignment();
            $assign = $assignmentModel->getById($assignmentId);
            $tourId = $assign['tour_id'] ?? null;
        }

        $data = [
            'tour_id'           => $tourId,
            'assignment_id'     => $assignmentId,
            'guide_id'          => $_POST['guide_id'] ?? null,
            'date'              => $_POST['date'] ?? date('Y-m-d'),
            'description'       => $_POST['description'] ?? '',
            'issue'             => $_POST['issue'] ?? '',
            'solution'          => $_POST['solution'] ?? '',
            'customer_feedback' => $_POST['customer_feedback'] ?? '',
            'weather'           => $_POST['weather'] ?? '',
            'incident'          => $_POST['incident'] ?? '',
            'health_status'     => $_POST['health_status'] ?? '',
            'special_activity'  => $_POST['special_activity'] ?? '',
            'handling_notes'    => $_POST['handling_notes'] ?? '',
            'guide_rating'      => $_POST['guide_rating'] ?? null,
        ];

        // Get guide_id from user_id in session
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $userRole = $_SESSION['user']['role'] ?? 'customer';
        if ($userRole === 'guide') {
            $guideModel = new Guide();
            $guide = $guideModel->getByUserId($_SESSION['user']['user_id']);
            if ($guide) {
                $data['guide_id'] = $guide['id'];
            }
        }

        // If guide_id is still empty, use the posted value or set to null
        if (empty($data['guide_id'])) {
            $data['guide_id'] = !empty($_POST['guide_id']) ? $_POST['guide_id'] : null;
        }

        $this->model->create($data);
        // Redirect back to trip detail
        if ($data['assignment_id']) {
            header('Location:' . BASE_URL_ADMIN . '&action=tours_logs/tour_detail&assignment_id=' . $data['assignment_id']);
        } else {
            header('Location:' . BASE_URL_ADMIN . '&action=tours_logs');
        }
        exit;
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            die('Thiếu ID');
        }

        $log = $this->model->findById($id);
        if (!$log) {
            die('Không tìm thấy nhật ký');
        }

        // Kiểm tra quyền truy cập
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        if ($userRole === 'guide') {
            $guideModel = new Guide();
            $guide = $guideModel->getByUserId($_SESSION['user']['user_id']);
            if (!$guide || !$this->model->canGuideAccessLog($id, $guide['id'])) {
                die('Bạn không có quyền chỉnh sửa nhật ký này');
            }
        }

        $tourModel = new Tour();
        $tours = $tourModel->select();

        $guideModel = new Guide();
        $guides = $guideModel->getAllWithName();

        require_once PATH_VIEW_ADMIN . 'pages/tours_logs/edit.php';
    }

    public function update()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            die('Thiếu ID');
        }

        // Kiểm tra quyền truy cập
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        if ($userRole === 'guide') {
            $guideModel = new Guide();
            $guide = $guideModel->getByUserId($_SESSION['user']['user_id']);
            if (!$guide || !$this->model->canGuideAccessLog($id, $guide['id'])) {
                die('Bạn không có quyền chỉnh sửa nhật ký này');
            }
        }

        $data = [
            'tour_id'           => $_POST['tour_id'] ?? null,
            'assignment_id'     => $_POST['assignment_id'] ?? null,
            'guide_id'          => $_POST['guide_id'] ?? null,
            'date'              => $_POST['date'] ?? date('Y-m-d'),
            'description'       => $_POST['description'] ?? '',
            'issue'             => $_POST['issue'] ?? '',
            'solution'          => $_POST['solution'] ?? '',
            'customer_feedback' => $_POST['customer_feedback'] ?? '',
            'weather'           => $_POST['weather'] ?? '',
            'incident'          => $_POST['incident'] ?? '',
            'health_status'     => $_POST['health_status'] ?? '',
            'special_activity'  => $_POST['special_activity'] ?? '',
            'handling_notes'    => $_POST['handling_notes'] ?? '',
            'guide_rating'      => $_POST['guide_rating'] ?? null,
        ];

        // Get guide_id from user_id in session
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $userRole = $_SESSION['user']['role'] ?? 'customer';
        if ($userRole === 'guide') {
            $guideModel = new Guide();
            $guide = $guideModel->getByUserId($_SESSION['user']['user_id']);
            if ($guide) {
                $data['guide_id'] = $guide['id'];
            }
        }

        // If guide_id is still empty, use the posted value or set to null
        if (empty($data['guide_id'])) {
            $data['guide_id'] = !empty($_POST['guide_id']) ? $_POST['guide_id'] : null;
        }

        $this->model->updateLog($id, $data);

        // Redirect back to trip detail
        if ($data['assignment_id']) {
            header('Location:' . BASE_URL_ADMIN . '&action=tours_logs/tour_detail&assignment_id=' . $data['assignment_id']);
        } else {
            header('Location:' . BASE_URL_ADMIN . '&action=tours_logs');
        }
        exit;
    }

    public function detail()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            die('Thiếu ID');
        }

        $log = $this->model->findById($id);
        if (!$log) {
            die('Không tìm thấy nhật ký');
        }

        require_once PATH_VIEW_ADMIN . 'pages/tours_logs/detail.php';
    }

    public function tourDetail()
    {
        $assignmentId = $_GET['assignment_id'] ?? null;
        $tourId = $_GET['id'] ?? null; // compatibility for old logs if needed

        if (!$assignmentId && !$tourId) {
            die('Thiếu thông tin chuyến đi');
        }

        $assignmentModel = new TourAssignment();
        $tourModel = new Tour();

        if ($assignmentId) {
            $assignment = $assignmentModel->getById($assignmentId);
            if (!$assignment) die('Không tìm thấy chuyến đi');
            $tourId = $assignment['tour_id'];
            $tour = $tourModel->findById($tourId);
            
            // Merge assignment data to tour object for easy access in view
            if ($tour && $assignment) {
                $assignmentId = $assignment['id']; // Đảm bảo ID này luôn có giá trị cho View
                $tour['guide_name'] = $assignment['guide_name'] ?? 'N/A';
                $tour['start_date'] = $assignment['start_date'];
                $tour['status'] = $assignment['status'];
            }
            
            $logs = $this->model->getLogsByAssignmentId($assignmentId);
            
            // Tên hiển thị kèm khoảng ngày
            $dateRange = date('d/m/Y', strtotime($assignment['start_date']));
            if (!empty($assignment['end_date']) && $assignment['end_date'] !== $assignment['start_date']) {
                $dateRange .= " - " . date('d/m/Y', strtotime($assignment['end_date']));
            }
            $tripTitle = ($tour['name'] ?? 'Tour') . " (" . $dateRange . ")";
        } else {
            // Fallback cho logs cũ (nhưng Option A là ẩn nên có thể không cần lo lắng nhiều ở đây)
            $tour = $tourModel->findById($tourId);
            $logs = $this->model->getLogsByTourId($tourId);
            $assignment = null;
            $tripTitle = $tour['name'] . " (Lịch sử)";
        }

        if (!$tour) {
            die('Không tìm thấy Tour');
        }

        // Kiểm tra quyền truy cập tour (có thể cần cập nhật checks ở đây)
        // ...

        // Lấy danh sách khách có yêu cầu đặc biệt
        $bookingCustomerModel = new BookingCustomer();
        $specialRequests = $bookingCustomerModel->getSpecialRequestsByTour($tourId);

        require_once PATH_VIEW_ADMIN . 'pages/tours_logs/tour_detail.php';
    }

    public function delete()
    {
        $id = $_POST['id'] ?? null;
        $tourId = $_POST['tour_id'] ?? null; // Pass tour_id to redirect back correctly

        if ($id) {
            // Kiểm tra quyền xóa
            $userRole = $_SESSION['user']['role'] ?? 'customer';
            if ($userRole === 'guide') {
                $guideModel = new Guide();
                $guide = $guideModel->getByUserId($_SESSION['user']['user_id']);
                if (!$guide || !$this->model->canGuideAccessLog($id, $guide['id'])) {
                    die('Bạn không có quyền xóa nhật ký này');
                }
            }

            // Get log to find tour_id if not passed
            if (!$tourId) {
                $log = $this->model->findById($id);
                $tourId = $log['tour_id'] ?? null;
            }
            $this->model->deleteById($id);
        }

        if ($tourId) {
            header('Location:' . BASE_URL_ADMIN . '&action=tours_logs/tour_detail&id=' . $tourId);
        } else {
            header('Location:' . BASE_URL_ADMIN . '&action=tours_logs');
        }
        exit;
    }

    /**
     * AJAX endpoint: Đánh dấu yêu cầu đặc biệt đã xử lý
     */
    public function markRequestHandled()
    {
        header('Content-Type: application/json');

        $customerId = $_POST['customer_id'] ?? null;
        $handled = $_POST['handled'] ?? 1;

        if (!$customerId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu customer ID']);
            exit;
        }

        $bookingCustomerModel = new BookingCustomer();
        $result = $bookingCustomerModel->markRequestHandled($customerId, $handled);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật trạng thái']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
        }
        exit;
    }
}
