<?php

class AvailableToursController
{
    protected $tourAssignmentModel;

    public function __construct()
    {
        $this->tourAssignmentModel = new TourAssignment();
    }

    /**
     * Hiển thị trang Tour Khả Dụng
     */
    public function index()
    {
        // Chỉ HDV và Admin mới được xem
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        if (!in_array($userRole, ['guide', 'admin'])) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
            header('Location:' . BASE_URL_ADMIN);
            exit;
        }

        // Lấy danh sách tour theo từng lịch khởi hành
        $availableTours = $this->tourAssignmentModel->getAvailableTours();

        // Nếu là admin, lấy danh sách HDV để phân công
        $guides = [];
        if ($userRole === 'admin') {
            $guideModel = new Guide();
            $guides = $guideModel->getAll();
        }

        include_once PATH_VIEW_ADMIN . 'pages/available_tours/index.php';
    }

    /**
     * Admin phân công HDV cho tour (AJAX endpoint)
     */
    public function assignGuide()
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
        $departureId = $_POST['departure_id'] ?? null;
        $departureDate = $_POST['departure_date'] ?? null;

        if (!$guideId || !$tourId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin HDV hoặc Tour']);
            exit;
        }

        try {
            // Kiểm tra tour đã có HDV chưa (cho ngày cụ thể)
            if ($this->tourAssignmentModel->tourHasGuide($tourId, $departureDate)) {
                echo json_encode(['success' => false, 'message' => 'Tour này đã có HDV phụ trách cho ngày ' . date('d/m/Y', strtotime($departureDate))]);
                exit;
            }

            // Nếu không có departure_date, lấy từ departure_id hoặc ngày hiện tại
            if (!$departureDate) {
                if ($departureId) {
                    // Lấy từ tour_departures
                    $departureModel = new TourDeparture();
                    $departure = $departureModel->findById($departureId);
                    $departureDate = $departure['departure_date'] ?? date('Y-m-d');
                } else {
                    $departureDate = date('Y-m-d');
                }
            }

            // Validate departure date
            if (strtotime($departureDate) < strtotime(date('Y-m-d'))) {
                echo json_encode(['success' => false, 'message' => 'Ngày khởi hành không được trong quá khứ']);
                exit;
            }

            // Gán tour cho HDV
            $assignmentData = [
                'guide_id' => $guideId,
                'tour_id' => $tourId,
                'start_date' => $departureDate,
                'status' => 'active'
            ];

            // Chỉ thêm departure_id nếu có
            if (!empty($departureId)) {
                $assignmentData['departure_id'] = $departureId;
            }

            $result = $this->tourAssignmentModel->insert($assignmentData);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Phân công HDV thành công! Ngày khởi hành: ' . date('d/m/Y', strtotime($departureDate))
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
        $departureId = $_POST['departure_id'] ?? null;
        $departureDate = $_POST['departure_date'] ?? null;

        if (!$tourId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin tour']);
            exit;
        }

        try {
            // Kiểm tra tour đã có HDV chưa
            if ($this->tourAssignmentModel->tourHasGuide($tourId)) {
                echo json_encode(['success' => false, 'message' => 'Tour này đã có HDV khác nhận rồi']);
                exit;
            }

            // Kiểm tra HDV đã nhận tour này chưa
            if ($this->tourAssignmentModel->isGuideAssignedToTour($guideId, $tourId)) {
                echo json_encode(['success' => false, 'message' => 'Bạn đã nhận tour này rồi']);
                exit;
            }

            // Tính tổng số khách của tour
            $bookingModel = new Booking();
            $sql = "SELECT 
                    COUNT(DISTINCT b.id) as booking_count,
                    COUNT(DISTINCT b.id) + COALESCE(SUM(bc_count.total), 0) as total_customers
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

            // Lấy ngày khởi hành
            if (!$departureDate) {
                if ($departureId) {
                    // Lấy từ tour_departures
                    $departureModel = new TourDeparture();
                    $departure = $departureModel->findById($departureId);
                    $startDate = $departure['departure_date'] ?? date('Y-m-d');
                } else {
                    // Lấy ngày khởi hành sớm nhất từ bookings
                    $sql = "SELECT MIN(booking_date) as start_date 
                        FROM bookings 
                        WHERE tour_id = :tour_id 
                        AND status NOT IN ('hoan_tat', 'da_huy')";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['tour_id' => $tourId]);
                    $dateInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                    $startDate = $dateInfo['start_date'] ?? date('Y-m-d');
                }
            } else {
                $startDate = $departureDate;
            }

            // Gán tour cho HDV
            $assignmentData = [
                'guide_id' => $guideId,
                'tour_id' => $tourId,
                'start_date' => $startDate,
                'status' => 'active'
            ];

            // Chỉ thêm departure_id nếu có
            if (!empty($departureId)) {
                $assignmentData['departure_id'] = $departureId;
            }

            $result = $this->tourAssignmentModel->insert($assignmentData);

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
}
