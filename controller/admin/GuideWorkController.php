<?php
require_once 'models/GuideWorkModel.php';

class GuideWorkController
{
    public function schedule()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $role = $_SESSION['role'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;

        if ($role === 'guide' && $userId) {
            $guide = GuideWorkModel::getGuideByUserId($userId);
            if (!$guide) {
                die("Không tìm thấy hướng dẫn viên.");
            }

            $assignments = GuideWorkModel::getAssignmentsByGuideId($guide['id']) ?: [];
            require_once PATH_VIEW_ADMIN . 'pages/guide_works/schedule_guide.php';
        } else {
            $guides = GuideWorkModel::getAllGuides();
            $guideAssignments = [];

            foreach ($guides as $g) {
                $assignments = GuideWorkModel::getAssignmentsByGuideId($g['id']) ?: [];
                $guideAssignments[] = [
                    'guide' => $g,
                    'assignments' => $assignments
                ];
            }

            require_once PATH_VIEW_ADMIN . 'pages/guide_works/schedule_all.php';
        }
    }

    public function tourDetail()
    {
        $tourId = $_GET['id'] ?? null;
        $guideId = $_GET['guide_id'] ?? null;

        if (!$tourId || !$guideId) {
            die("Thiếu tour_id hoặc guide_id");
        }

        // Lấy thông tin tour
        $tour = GuideWorkModel::getTourById($tourId);
        $assignment = GuideWorkModel::getAssignment($tourId, $guideId);
        $itineraries = GuideWorkModel::getItinerariesByTourId($tourId) ?: [];

        // Lấy bookings của tour này
        $bookingModel = new Booking();
        $bookings = $bookingModel->getByTourId($tourId);

        // Lấy danh sách khách từ tất cả bookings
        $customerModel = new BookingCustomer();
        $allCustomers = [];
        $stats = [
            'total' => 0,
            'checked_in' => 0,
            'not_arrived' => 0,
            'absent' => 0
        ];

        foreach ($bookings as $booking) {
            // Lấy khách đi cùng
            $customers = $customerModel->getCustomersWithCheckinStatus($booking['id']);

            // Kiểm tra xem main customer đã có trong booking_customers chưa
            $mainCustomerExists = false;
            foreach ($customers as $customer) {
                if ($customer['full_name'] === $booking['customer_name']) {
                    $mainCustomerExists = true;
                    break;
                }
            }

            // Chỉ thêm virtual main customer nếu chưa có trong database
            if (!empty($booking['customer_name']) && !$mainCustomerExists) {
                $mainCustomer = [
                    'id' => 'main_' . $booking['id'],
                    'full_name' => $booking['customer_name'],
                    'booking_code' => $booking['id'],
                    'booking_customer_name' => $booking['customer_name'],
                    'checkin_status' => 'not_arrived',
                    'passenger_type' => 'adult',
                    'is_foc' => 0,
                    'is_main' => true, // Đánh dấu là người đặt
                    'phone' => null,
                    'special_request' => null
                ];
                $allCustomers[] = $mainCustomer;

                // Tính stats
                $stats['total']++;
                $stats['not_arrived']++;
            }

            // Thêm khách đi cùng
            foreach ($customers as $customer) {
                $customer['booking_code'] = $booking['id'];
                $customer['booking_customer_name'] = $booking['customer_name'] ?? 'N/A';
                $customer['is_main'] = false;
                $allCustomers[] = $customer;

                // Tính stats
                $stats['total']++;
                $status = $customer['checkin_status'] ?? 'not_arrived';
                if (isset($stats[$status])) {
                    $stats[$status]++;
                }
            }
        }

        require_once PATH_VIEW_ADMIN . 'pages/guide_works/tour_detail.php';
    }

    /**
     * HDV hủy nhận tour (phải trước 3 ngày)
     */
    public function cancelAssignment()
    {
        header('Content-Type: application/json');

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $assignmentId = $_POST['assignment_id'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;

        if (!$assignmentId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu assignment ID']);
            exit;
        }

        // Lấy thông tin assignment
        $assignment = GuideWorkModel::getAssignmentById($assignmentId);

        if (!$assignment) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy phân công']);
            exit;
        }

        // Kiểm tra xem HDV có phải là người được phân công không
        $guide = GuideWorkModel::getGuideByUserId($userId);
        if (!$guide || $guide['id'] != $assignment['guide_id']) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền hủy phân công này']);
            exit;
        }

        // Kiểm tra điều kiện 3 ngày
        $startDate = new DateTime($assignment['start_date']);
        $today = new DateTime();
        $daysUntilStart = $today->diff($startDate)->days;

        if ($daysUntilStart < 3 || $today >= $startDate) {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể hủy tour. Phải hủy trước ít nhất 3 ngày so với ngày bắt đầu.'
            ]);
            exit;
        }

        // Xóa assignment
        $result = GuideWorkModel::deleteAssignment($assignmentId);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã hủy nhận tour thành công'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi hủy tour'
            ]);
        }
        exit;
    }

    /**
     * Cập nhật trạng thái tour assignment
     */
    public function updateStatus()
    {
        header('Content-Type: application/json');

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $assignmentId = $_POST['assignment_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;

        // Validate input
        if (!$assignmentId || !$status) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc']);
            exit;
        }

        // Validate status value
        $validStatuses = ['pending', 'active', 'completed'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
            exit;
        }

        // Lấy thông tin assignment
        $assignment = GuideWorkModel::getAssignmentById($assignmentId);

        if (!$assignment) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy phân công']);
            exit;
        }

        // Kiểm tra quyền (HDV được phân công hoặc admin)
        $role = $_SESSION['role'] ?? null;

        if ($role !== 'admin') {
            // Nếu không phải admin, kiểm tra xem có phải HDV được phân công không
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.']);
                exit;
            }

            $guide = GuideWorkModel::getGuideByUserId($userId);
            if (!$guide) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin hướng dẫn viên.']);
                exit;
            }

            if ($guide['id'] != $assignment['guide_id']) {
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền cập nhật trạng thái tour này.']);
                exit;
            }
        }

        // Update status
        $result = GuideWorkModel::updateAssignmentStatus($assignmentId, $status);

        if ($result) {
            $statusLabels = [
                'pending' => 'Chưa bắt đầu',
                'active' => 'Đang diễn ra',
                'completed' => 'Hoàn thành'
            ];
            echo json_encode([
                'success' => true,
                'message' => 'Đã cập nhật trạng thái thành "' . $statusLabels[$status] . '"'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái'
            ]);
        }
        exit;
    }
}
