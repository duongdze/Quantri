<?php

class AvailableToursController
{
    protected $tourAssignmentModel;

    public function __construct()
    {
        check_role(['admin', 'guide']);
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
        $groupNumber = $_POST['group_number'] ?? 1;

        if (!$guideId || !$tourId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin HDV hoặc Tour']);
            exit;
        }

        try {
            // Kiểm tra suất tour này đã có HDV chưa
            if ($this->tourAssignmentModel->tourHasGuide($tourId, $departureDate, $groupNumber)) {
                echo json_encode(['success' => false, 'message' => 'Suất dẫn tour này đã có HDV phụ trách']);
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

            // Tính toán ngày kết thúc dựa trên thời lượng tour
            $duration = $this->tourAssignmentModel->getTourDuration($tourId);
            $endDate = date('Y-m-d', strtotime($departureDate . " + " . ($duration - 1) . " days"));

            // KIỂM TRA TRÙNG LỊCH
            $busyTour = $this->tourAssignmentModel->isGuideBusy($guideId, $departureDate, $endDate);
            if ($busyTour) {
                $overlapInfo = "từ " . date('d/m', strtotime($busyTour['start_date'])) . " đến " . date('d/m', strtotime($busyTour['end_date']));
                echo json_encode(['success' => false, 'message' => "Hướng dẫn viên này bận tour '{$busyTour['tour_name']}' ({$overlapInfo})"]);
                exit;
            }

            // Gán tour cho HDV
            $assignmentData = [
                'guide_id' => $guideId,
                'tour_id' => $tourId,
                'start_date' => $departureDate,
                'end_date' => $endDate,
                'group_number' => $groupNumber,
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
        $groupNumber = $_POST['group_number'] ?? 1;

        if (!$tourId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin tour']);
            exit;
        }

        try {
            // 1. Xác định ngày khởi hành trước
            if (!$departureDate) {
                if ($departureId) {
                    $departureModel = new TourDeparture();
                    $departure = $departureModel->findById($departureId);
                    $startDate = $departure['departure_date'] ?? date('Y-m-d');
                } else {
                    $sql = "SELECT MIN(departure_date) as start_date 
                        FROM bookings 
                        WHERE tour_id = :tour_id 
                        AND status NOT IN ('hoan_tat', 'da_huy')";
                    $stmt = $this->tourAssignmentModel->getPDO()->prepare($sql);
                    $stmt->execute(['tour_id' => $tourId]);
                    $dateInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                    $startDate = $dateInfo['start_date'] ?? date('Y-m-d');
                }
            } else {
                $startDate = $departureDate;
            }

            // 2. Các kiểm tra cơ bản
            if ($this->tourAssignmentModel->tourHasGuide($tourId, $startDate, $groupNumber)) {
                echo json_encode(['success' => false, 'message' => 'Suất dẫn tour này đã có HDV khác nhận.']);
                exit;
            }
            
            // Một HDV có thể nhận nhiều nhóm của cùng 1 tour nếu bạn cho phép, 
            // nhưng ở đây ta vẫn kiểm tra xem họ đã nhận đúng nhóm này chưa.
            $sqlCheck = "SELECT COUNT(*) FROM tour_assignments 
                         WHERE guide_id = :gid AND tour_id = :tid 
                         AND start_date = :sd AND group_number = :gn AND status = 'active'";
            $stmtCheck = $this->tourAssignmentModel->getPDO()->prepare($sqlCheck);
            $stmtCheck->execute(['gid' => $guideId, 'tid' => $tourId, 'sd' => $startDate, 'gn' => $groupNumber]);
            if ($stmtCheck->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Bạn đã nhận nhóm này rồi']);
                exit;
            }

            // 3. Lấy thông tin quy mô tour để kiểm tra
            require_once 'models/Tour.php';
            $tourModelObj = new Tour();
            $tourInfo = $tourModelObj->findById($tourId);
            
            // Ưu tiên lấy max_seats từ tour_departures (lịch cụ thể)
            $departureModel = new TourDeparture();
            $departureInfo = null;
            if ($departureId) {
                $departureInfo = $departureModel->findById($departureId);
            } else {
                // Thử tìm theo tour_id và ngày
                $departureInfo = $departureModel->find('*', 'tour_id = :tid AND departure_date = :dd', [
                    'tid' => $tourId,
                    'dd' => $startDate
                ]);
            }

            $minParticipants = (int)($tourInfo['min_participants'] ?? 15);
            $maxSeats = (int)($departureInfo['max_seats'] ?? ($tourInfo['max_participants'] ?? 30));

            // Tính tổng khách
            $bookingModel = new Booking();
            $sql = "SELECT 
                    COALESCE(SUM(bc_count.total), 0) as total_customers
                FROM bookings b
                LEFT JOIN (
                    SELECT booking_id, COUNT(*) as total 
                    FROM booking_customers 
                    GROUP BY booking_id
                ) bc_count ON b.id = bc_count.booking_id
                WHERE b.tour_id = :tour_id
                AND b.departure_date = :departure_date
                AND b.status NOT IN ('hoan_tat', 'da_huy')";

            $stmtCount = $bookingModel->getPDO()->prepare($sql);
            $stmtCount->execute([
                'tour_id' => $tourId,
                'departure_date' => $startDate
            ]);
            $tourStats = $stmtCount->fetch(PDO::FETCH_ASSOC);

            $totalCustomers = $tourStats['total_customers'] ?? 0;

            // Validate theo quy mô thực tế
            if ($totalCustomers < $minParticipants) {
                echo json_encode(['success' => false, 'message' => "Tour chưa đủ số người tối thiểu ({$minParticipants} người). Hiện tại: {$totalCustomers} người"]);
                exit;
            }

            if ($totalCustomers > $maxSeats) {
                // Nếu vượt quá max_seats, kiểm tra xem suất groupNumber có hợp lệ không
                $requiredGroups = ceil($totalCustomers / $maxSeats);
                if ($groupNumber > $requiredGroups) {
                    echo json_encode(['success' => false, 'message' => 'Tour đã được gộp lại do Admin tăng số chỗ. Vui lòng làm mới trang và nhận lại suất Nhóm 1.']);
                    exit;
                }
            } else {
                // Nếu không vượt quá, mà HDV lại nhận group > 1 thì báo gộp
                if ($groupNumber > 1) {
                    echo json_encode(['success' => false, 'message' => 'Tour này đã được gộp lại thành 1 nhóm duy nhất.']);
                    exit;
                }
            }

            // KIỂM TRA TRÙNG LỊCH
            $duration = $this->tourAssignmentModel->getTourDuration($tourId);
            $endDate = date('Y-m-d', strtotime($startDate . " + " . ($duration - 1) . " days"));
            $busyTour = $this->tourAssignmentModel->isGuideBusy($guideId, $startDate, $endDate);
            if ($busyTour) {
                $overlapInfo = "từ " . date('d/m', strtotime($busyTour['start_date'])) . " đến " . date('d/m', strtotime($busyTour['end_date']));
                echo json_encode(['success' => false, 'message' => "Bạn hiện bận tour '{$busyTour['tour_name']}' ({$overlapInfo})"]);
                exit;
            }

            // Gán tour cho HDV
            $assignmentData = [
                'guide_id' => $guideId,
                'tour_id' => $tourId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'group_number' => $groupNumber,
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
