<?php

class TourAssignmentController
{
    protected $model;

    public function __construct()
    {
        $this->model = new TourAssignment();
    }

    /**
     * Hiển thị trang quản lý phân công tour
     */
    public function index()
    {
        // Chỉ admin mới được truy cập
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        if ($userRole !== 'admin') {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
            header('Location:' . BASE_URL_ADMIN);
            exit;
        }

        $assignments = $this->model->getAllAssignments();

        // Load guides và tours cho dropdown
        $guideModel = new Guide();
        $tourModel = new Tour();

        $guides = $guideModel->getAll();
        $tours = $tourModel->select('*', null, [], 'name ASC');

        require_once PATH_VIEW_ADMIN . 'pages/guides/tour-assignments.php';
    }

    /**
     * Phân công tour cho HDV
     */
    public function assign()
    {
        // Chỉ admin mới được phân công
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        if ($userRole !== 'admin') {
            $_SESSION['error'] = 'Bạn không có quyền thực hiện thao tác này';
            header('Location:' . BASE_URL_ADMIN);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location:' . BASE_URL_ADMIN . '&action=guides/tour-assignments');
            exit;
        }

        $guideId = $_POST['guide_id'] ?? null;
        $tourId = $_POST['tour_id'] ?? null;
        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;
        $status = $_POST['status'] ?? 'active';

        if (!$guideId || !$tourId) {
            $_SESSION['error'] = 'Vui lòng chọn HDV và Tour';
            header('Location:' . BASE_URL_ADMIN . '&action=guides/tour-assignments');
            exit;
        }

        try {
            $result = $this->model->assignTourToGuide($guideId, $tourId, $startDate, $endDate, $status);

            if ($result) {
                $_SESSION['success'] = 'Phân công tour thành công';
            } else {
                $_SESSION['error'] = 'Không thể phân công tour';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }

        header('Location:' . BASE_URL_ADMIN . '&action=guides/tour-assignments');
        exit;
    }

    /**
     * Hủy phân công
     */
    public function remove()
    {
        // Chỉ admin mới được hủy phân công
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        if ($userRole !== 'admin') {
            $_SESSION['error'] = 'Bạn không có quyền thực hiện thao tác này';
            header('Location:' . BASE_URL_ADMIN);
            exit;
        }

        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy phân công';
            header('Location:' . BASE_URL_ADMIN . '&action=guides/tour-assignments');
            exit;
        }

        try {
            $result = $this->model->removeAssignment($id);

            if ($result) {
                $_SESSION['success'] = 'Hủy phân công thành công';
            } else {
                $_SESSION['error'] = 'Không thể hủy phân công';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }

        header('Location:' . BASE_URL_ADMIN . '&action=guides/tour-assignments');
        exit;
    }

    /**
     * Admin xóa assignment (AJAX endpoint)
     */
    public function removeAssignmentByAdmin()
    {
        header('Content-Type: application/json');

        // Chỉ admin mới được xóa
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        if ($userRole !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này']);
            exit;
        }

        $id = $_POST['assignment_id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin assignment']);
            exit;
        }

        try {
            $result = $this->model->removeAssignment($id);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Xóa phân công thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể xóa phân công']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * AJAX: Lấy danh sách tour của một HDV
     */
    public function getGuideTours()
    {
        header('Content-Type: application/json');

        $guideId = $_GET['guide_id'] ?? null;

        if (!$guideId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu guide_id']);
            exit;
        }

        $tours = $this->model->getToursByGuide($guideId);
        echo json_encode(['success' => true, 'tours' => $tours]);
        exit;
    }

    /**
     * AJAX: Lấy danh sách HDV của một tour
     */
    public function getTourGuides()
    {
        header('Content-Type: application/json');

        $tourId = $_GET['tour_id'] ?? null;

        if (!$tourId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu tour_id']);
            exit;
        }

        $guides = $this->model->getGuidesByTour($tourId);
        echo json_encode(['success' => true, 'guides' => $guides]);
        exit;
    }
    /**
     * Hiển thị trang danh sách tour khả dụng (chỉ cho HDV)
     */
    public function availableTours()
    {
        // Chỉ HDV và Admin mới được xem
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        if (!in_array($userRole, ['guide', 'admin'])) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
            header('Location:' . BASE_URL_ADMIN);
            exit;
        }

        $tourAssignmentModel = new TourAssignment();
        $rawTours = $tourAssignmentModel->getAvailableTours();

        // Loại bỏ duplicate tours dựa trên ID
        $availableTours = [];
        $seenIds = [];

        foreach ($rawTours as $tour) {
            $tourId = $tour['id'];
            if (!isset($seenIds[$tourId])) {
                $seenIds[$tourId] = true;
                // Thêm version breakdown
                $tour['version_breakdown'] = $tourAssignmentModel->getTourVersionBreakdown($tourId);
                $availableTours[] = $tour;
            }
        }

        // Nếu là admin, lấy danh sách HDV để phân công
        $guides = [];
        if ($userRole === 'admin') {
            $guideModel = new Guide();
            $guides = $guideModel->getAll();
        }

        include_once PATH_VIEW_ADMIN . 'pages/guides/available-tours.php';
    }
    /**
     * HDV nhận tour (AJAX endpoint)
     */
    public function claimTour()
    {
        header('Content-Type: application/json');

        // Kiểm tra quyền
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        $userId = $_SESSION['user']['user_id'] ?? null;

        if (!in_array($userRole, ['guide', 'admin'])) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền nhận tour']);
            exit;
        }

        // Get guide ID
        require_once 'models/Guide.php';
        $guideModel = new Guide();
        $guide = $guideModel->getByUserId($userId);

        if (!$guide) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy hồ sơ HDV tương ứng. Vui lòng liên hệ quản trị.']);
            exit;
        }

        $guideId = $guide['id'];
        $tourId = $_POST['tour_id'] ?? null;

        if (!$tourId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin tour']);
            exit;
        }

        try {
            $tourAssignmentModel = new TourAssignment();

            // Kiểm tra tour đã có HDV chưa
            if ($tourAssignmentModel->tourHasGuide($tourId)) {
                echo json_encode(['success' => false, 'message' => 'Tour này đã có HDV khác nhận rồi']);
                exit;
            }

            // Kiểm tra HDV đã nhận tour này chưa
            if ($tourAssignmentModel->isGuideAssignedToTour($guideId, $tourId)) {
                echo json_encode(['success' => false, 'message' => 'Bạn đã nhận tour này rồi']);
                exit;
            }

            // Tính tổng số khách của tour
            $bookingModel = new Booking();
            $sql = "SELECT 
                    COUNT(DISTINCT b.id) as booking_count,
                    COALESCE(SUM(bc_count.total), COUNT(DISTINCT b.id)) as total_customers
                FROM bookings b
                LEFT JOIN (
                    SELECT booking_id, COUNT(*) as total 
                    FROM booking_customers 
                    GROUP BY booking_id
                ) bc_count ON b.id = bc_count.booking_id
                WHERE b.tour_id = :tour_id
                AND b.status NOT IN ('hoan_tat', 'da_huy')";

            $pdo = $bookingModel->getPDO();
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['tour_id' => $tourId]);
            $tourStats = $stmt->fetch(PDO::FETCH_ASSOC);

            $totalCustomers = $tourStats['total_customers'] ?? 0;

            // Validate 15-30 người
            if ($totalCustomers < 15) {
                echo json_encode(['success' => false, 'message' => 'Tour chưa đủ 15 người. Hiện tại: ' . $totalCustomers . ' người']);
                exit;
            }

            if ($totalCustomers > 30) {
                echo json_encode(['success' => false, 'message' => 'Tour quá đông (>30 người). Cần chia nhóm. Hiện tại: ' . $totalCustomers . ' người']);
                exit;
            }

            // Lấy ngày khởi hành sớm nhất
            $sql = "SELECT MIN(booking_date) as start_date 
                FROM bookings 
                WHERE tour_id = :tour_id 
                AND status NOT IN ('hoan_tat', 'da_huy')";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['tour_id' => $tourId]);
            $dateInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            $startDate = $dateInfo['start_date'] ?? date('Y-m-d');

            // Gán tour cho HDV (1 record duy nhất)
            $result = $tourAssignmentModel->insert([
                'guide_id' => $guideId,
                'tour_id' => $tourId,
                'start_date' => $startDate,
                'status' => 'active'
            ]);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => "Nhận tour thành công! Tổng {$totalCustomers} khách."
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể nhận tour']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Admin phân công HDV cho tour (AJAX endpoint)
     */
    public function adminAssignGuide()
    {
        header('Content-Type: application/json');

        // Chỉ admin mới được phân công
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        if ($userRole !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này']);
            exit;
        }

        $guideId = $_POST['guide_id'] ?? null;
        $tourId = $_POST['tour_id'] ?? null;

        if (!$guideId || !$tourId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin HDV hoặc Tour']);
            exit;
        }

        try {
            $tourAssignmentModel = new TourAssignment();

            // Kiểm tra tour đã có HDV chưa
            if ($tourAssignmentModel->tourHasGuide($tourId)) {
                echo json_encode(['success' => false, 'message' => 'Tour này đã có HDV phụ trách']);
                exit;
            }

            // Lấy ngày khởi hành sớm nhất
            $bookingModel = new Booking();
            $pdo = $bookingModel->getPDO();
            $sql = "SELECT MIN(booking_date) as start_date 
                FROM bookings 
                WHERE tour_id = :tour_id 
                AND status NOT IN ('hoan_tat', 'da_huy')";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['tour_id' => $tourId]);
            $dateInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            $startDate = $dateInfo['start_date'] ?? date('Y-m-d');

            // Gán tour cho HDV
            $result = $tourAssignmentModel->insert([
                'guide_id' => $guideId,
                'tour_id' => $tourId,
                'start_date' => $startDate,
                'status' => 'active'
            ]);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Phân công HDV thành công!'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể phân công HDV']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Hiển thị danh sách booking của tour
     */
    public function tourBookings()
    {
        $tourId = $_GET['tour_id'] ?? null;

        if (!$tourId) {
            $_SESSION['error'] = 'Không tìm thấy tour';
            header('Location: ' . BASE_URL_ADMIN . '&action=guides/available-tours');
            exit;
        }

        // Lấy thông tin tour
        $tourModel = new Tour();
        $tour = $tourModel->findById($tourId);

        if (!$tour) {
            $_SESSION['error'] = 'Tour không tồn tại';
            header('Location: ' . BASE_URL_ADMIN . '&action=guides/available-tours');
            exit;
        }

        // Lấy danh sách booking của tour
        $bookingModel = new Booking();
        $sql = "SELECT b.*, 
                    u.full_name as customer_name,
                    COUNT(bc.id) + 1 as total_customers
                FROM bookings b
                LEFT JOIN users u ON b.customer_id = u.user_id
                LEFT JOIN booking_customers bc ON b.id = bc.id
                WHERE b.tour_id = :tour_id
                AND b.status NOT IN ('hoan_tat', 'da_huy')
                GROUP BY b.id
                ORDER BY b.booking_date ASC";

        $pdo = $bookingModel->getPDO();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['tour_id' => $tourId]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once PATH_VIEW_ADMIN . 'pages/guides/tour-bookings.php';
    }

    /**
     * HDV nhận booking cụ thể (AJAX)
     */
    public function acceptBooking()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $bookingId = $_POST['booking_id'] ?? null;
        $tourId = $_POST['tour_id'] ?? null;

        if (!$bookingId || !$tourId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            exit;
        }

        // Kiểm tra user là HDV
        $userRole = $_SESSION['user']['role'] ?? '';
        if ($userRole !== 'guide') {
            echo json_encode(['success' => false, 'message' => 'Chỉ HDV mới có thể nhận booking']);
            exit;
        }

        // Lấy guide_id
        $guideModel = new Guide();
        $guide = $guideModel->getByUserId($_SESSION['user']['user_id']);

        if (!$guide) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin HDV']);
            exit;
        }

        try {
            // Lấy thông tin booking
            $bookingModel = new Booking();
            $booking = $bookingModel->getById($bookingId);

            if (!$booking) {
                echo json_encode(['success' => false, 'message' => 'Booking không tồn tại']);
                exit;
            }

            // Phân công tour cho HDV
            $result = $this->model->insert([
                'guide_id' => $guide['id'],
                'tour_id' => $tourId,
                'start_date' => $booking['booking_date'],
                'status' => 'active'
            ]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Nhận booking thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể nhận booking']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }
}
