<?php
require_once 'models/Tour.php';
require_once 'models/TourDeparture.php';
require_once 'models/Booking.php';

class ClientBookingController
{
    private $tourModel;
    private $departureModel;
    private $bookingModel;

    public function __construct()
    {
        $this->tourModel = new Tour();
        $this->departureModel = new TourDeparture();
        $this->bookingModel = new Booking();
    }

    public function create()
    {
        $tourId = $_GET['tour_id'] ?? null;
        $departureId = $_GET['departure_id'] ?? null;

        if (!$tourId || !$departureId) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $tour = $this->tourModel->findById($tourId);
        $departure = $this->departureModel->findById($departureId);

        if (!$tour || !$departure) {
            header('Location: ' . BASE_URL);
            exit;
        }

        // Check if departure is available
        $isFull = in_array($departure['status'], ['closed', 'full']) || ($departure['max_seats'] - $departure['booked_seats'] <= 0);
        if ($isFull) {
            $_SESSION['error'] = 'Rất tiếc, ngày khởi hành này đã hết chỗ hoặc ngừng nhận khách!';
            header('Location: ' . BASE_URL . '?action=tour-detail&id=' . $tourId);
            exit;
        }

        // Calculate duration from itineraries if column is missing
        if (!isset($tour['duration_days'])) {
            $itineraries = $this->tourModel->getRelatedData('itineraries', $tourId);
            $tour['duration_days'] = count($itineraries) > 0 ? count($itineraries) : 'N/A';
        }

        require_once PATH_VIEW_CLIENT . 'pages/bookings/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL);
            exit;
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Lỗi bảo mật: CSRF token không hợp lệ!';
            header('Location: ' . BASE_URL);
            exit;
        }

        $tourId = $_POST['tour_id'];
        $departureId = $_POST['departure_id'];
        
        // Basic Validation
        $fullName = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';

        // BK_02: Bỏ trống Họ tên
        if (empty($fullName)) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['error'] = 'Họ tên không được để trống';
            header("Location: " . BASE_URL . "?action=booking-create&tour_id=$tourId&departure_id=$departureId");
            exit;
        }

        // BK_03: SĐT sai định dạng (chứa chữ cái)
        if (!preg_match('/^[0-9]+$/', $phone)) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['error'] = 'Số điện thoại không hợp lệ, vui lòng chỉ nhập ký số';
            header("Location: " . BASE_URL . "?action=booking-create&tour_id=$tourId&departure_id=$departureId");
            exit;
        }

        // BK_04: SĐT không đủ chữ số (10-11 số)
        if (strlen($phone) < 10 || strlen($phone) > 11) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['error'] = 'Số điện thoại phải bao gồm từ 10 đến 11 chữ số';
            header("Location: " . BASE_URL . "?action=booking-create&tour_id=$tourId&departure_id=$departureId");
            exit;
        }

        // BK_05: Email sai định dạng
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['error'] = 'Địa chỉ Email không đúng định dạng chuẩn';
            header("Location: " . BASE_URL . "?action=booking-create&tour_id=$tourId&departure_id=$departureId");
            exit;
        }

        $adults = max(1, (int)$_POST['adults']);
        $children = max(0, (int)($_POST['children'] ?? 0));
        $totalSeats = $adults + $children;

        // Check availability
        $departure = $this->departureModel->findById($departureId);
        $remainingSeats = $departure['max_seats'] - $departure['booked_seats'];
        $isStatusUnavailable = in_array($departure['status'], ['closed', 'full']);
        
        if ($isStatusUnavailable || $remainingSeats < $totalSeats) {
             $_SESSION['old_input'] = $_POST;
             $_SESSION['error'] = 'Rất tiếc, tour đã hết chỗ hoặc không đủ số lượng ghế bạn yêu cầu!';
             header("Location: " . BASE_URL . "?action=booking-create&tour_id=$tourId&departure_id=$departureId");
             exit;
        }
        
        // Calculate Total Price
        $priceAdult = $departure['price_adult'] > 0 ? $departure['price_adult'] : $this->tourModel->findById($tourId)['base_price'];
        $priceChild = $departure['price_child'] > 0 ? $departure['price_child'] : $priceAdult;

        $totalPrice = ($adults * $priceAdult) + ($children * $priceChild);

        // 1. Prepare Booking Data (bookings table)
        // Note: avoiding columns that might not exist based on model review
        $bookingData = [
            'tour_id' => $tourId,
            'departure_id' => $departureId,
            'customer_id' => $_SESSION['user']['user_id'] ?? null, // If logged in
            'booking_date' => date('Y-m-d H:i:s'),
            'departure_date' => $departure['departure_date'],
            'final_price' => $totalPrice,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'created_by' => $_SESSION['user']['user_id'] ?? 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Start Transaction
        try {
            $this->bookingModel->beginTransaction();

            // Insert Booking
            $bookingId = $this->bookingModel->insert($bookingData);
            $bookingCode = 'BK' . str_pad($bookingId, 6, '0', STR_PAD_LEFT); // Generate display code

            // 2. Insert Booking Customer (Lead Passenger)
            require_once 'models/BookingCustomer.php';
            $bookingCustomerModel = new BookingCustomer();
            
            $customerData = [
                'booking_id' => $bookingId,
                'full_name' => $_POST['full_name'],
                'phone' => $_POST['phone'],
                'passenger_type' => 'adult', // Lead is usually adult
            ];
            $bookingCustomerModel->insert($customerData);

            // 3. Update Seats
            $newBookedSeats = $departure['booked_seats'] + $totalSeats;
            $this->departureModel->update(['booked_seats' => $newBookedSeats], "id = :id", ['id' => $departureId]);

            $this->bookingModel->commit();

            // Gửi email xác nhận booking qua PHPMailer
            require_once PATH_ROOT . 'services/MailService.php';
            $tour = $this->tourModel->findById($tourId);

            $bookingArr = [
                'id'           => $bookingId,
                'booking_code' => $bookingCode,
                'total_price'  => $totalPrice,
            ];
            $userArr = [
                'full_name' => $_POST['full_name'],
                'email'     => $_POST['email'],
            ];
            // Gửi email cho khách hàng
            MailService::sendBookingConfirmation($userArr, $bookingArr, $tour ?? []);

            // Thông báo admin
            if (defined('MAIL_FROM') && MAIL_FROM !== 'your_email@gmail.com') {
                MailService::notifyAdminNewBooking(MAIL_FROM, $bookingArr, $tour ?? [], $userArr);
            }

            header('Location: ' . BASE_URL . '?action=booking-payment&code=' . $bookingCode);


        } catch (Exception $e) {
            $this->bookingModel->rollBack();
            $_SESSION['error'] = 'Lỗi xử lý đặt tour: ' . $e->getMessage();
             header("Location: " . BASE_URL . "?action=booking-create&tour_id=$tourId&departure_id=$departureId");
        }
    }

    public function payment()
    {
        $code = $_GET['code'] ?? '';
        if (!$code) {
             header('Location: ' . BASE_URL);
             exit;
        }
        
        // Fetch booking info for display
        $booking = $this->bookingModel->find('*', 'id = :id', ['id' => intval(substr($code, 2))]); // BK000001 -> 1
        
        if (!$booking) {
             header('Location: ' . BASE_URL);
             exit;
        }

        $tour = $this->tourModel->findById($booking['tour_id']);


        require_once PATH_VIEW_CLIENT . 'pages/bookings/payment.php';
    }

    public function success()
    {
        $code = $_GET['code'] ?? '';
        if ($code) {
            $bookingId = intval(substr($code, 2));
            $booking = $this->bookingModel->getBookingWithDetails($bookingId);
        }
        require_once PATH_VIEW_CLIENT . 'pages/bookings/success.php';
    }

    /**
     * Đơn hàng của tôi
     */
    public function myBookings()
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        $customerId = $_SESSION['user']['user_id'];
        $pdo = BaseModel::getPdo();
        $stmt = $pdo->prepare("
            SELECT b.*,
                   t.name AS tour_name,
                   MAX(CASE WHEN tgi.main_img = 1 THEN tgi.image_url END) AS tour_image,
                   td.departure_date
            FROM bookings b
            LEFT JOIN tours t ON b.tour_id = t.id
            LEFT JOIN tour_gallery_images tgi ON t.id = tgi.tour_id
            LEFT JOIN tour_departures td ON b.departure_id = td.id
            WHERE b.customer_id = :cid
            GROUP BY b.id, t.name, td.departure_date
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([':cid' => $customerId]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Đơn hàng của tôi – VietTour';
        require_once PATH_VIEW_CLIENT . 'pages/bookings/my-bookings.php';
    }

    /**
     * Chi tiết đơn hàng của tôi
     */
    public function myBookingDetail()
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        $code = $_GET['code'] ?? '';
        if (!$code) {
            header('Location: ' . BASE_URL . '?action=my-bookings');
            exit;
        }

        $bookingId = intval(substr($code, 2));
        $booking = $this->bookingModel->getBookingWithDetails($bookingId);

        // Chỉ cho phép xem booking của chính mình
        if (!$booking || $booking['customer_id'] != $_SESSION['user']['user_id']) {
            header('Location: ' . BASE_URL . '?action=my-bookings');
            exit;
        }

        // Lấy danh sách khách
        $pdo = BaseModel::getPdo();
        $stmt = $pdo->prepare("SELECT * FROM booking_customers WHERE booking_id = :bid");
        $stmt->execute([':bid' => $bookingId]);
        $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Chi tiết đơn hàng – VietTour';
        require_once PATH_VIEW_CLIENT . 'pages/bookings/detail.php';
    }

    /**
     * Xử lý thanh toán VNPay — redirect sang VNPay gateway
     */
    public function processVnpay()
    {
        $code = $_GET['code'] ?? '';
        if (!$code) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $bookingId = intval(substr($code, 2));
        $booking = $this->bookingModel->find('*', 'id = :id', ['id' => $bookingId]);

        if (!$booking || !defined('VNPAY_ENABLED') || !VNPAY_ENABLED) {
            $_SESSION['error'] = 'Không thể xử lý thanh toán VNPay.';
            header('Location: ' . BASE_URL . '?action=booking-payment&code=' . $code);
            exit;
        }

        require_once PATH_ROOT . 'services/VNPayService.php';

        $returnUrl = BASE_URL . '?action=vnpay-return';
        $orderInfo = 'Thanh toan tour ' . $code;

        $paymentUrl = VNPayService::createPaymentUrl($booking, $orderInfo, $returnUrl);
        header('Location: ' . $paymentUrl);
        exit;
    }

    /**
     * VNPay callback — xử lý kết quả trả về từ VNPay
     */
    public function vnpayReturn()
    {
        require_once PATH_ROOT . 'services/VNPayService.php';

        $result = VNPayService::verifyReturn($_GET);

        if ($result['success']) {
            // Cập nhật trạng thái booking thành "paid"
            $bookingCode = $result['booking_code'];
            $bookingId = intval(substr($bookingCode, 2));

            $this->bookingModel->update(
                ['status' => 'paid', 'payment_method' => 'vnpay'],
                'id = :id',
                ['id' => $bookingId]
            );

            $_SESSION['success'] = 'Thanh toán VNPay thành công! Mã giao dịch: ' . ($result['transaction_no'] ?? '');
            header('Location: ' . BASE_URL . '?action=booking-success&code=' . $bookingCode);
        } else {
            $_SESSION['error'] = 'Thanh toán thất bại: ' . $result['message'];
            $bookingCode = $result['booking_code'];
            header('Location: ' . BASE_URL . '?action=booking-payment&code=' . $bookingCode);
        }
        exit;
    }

    /**
     * Tải hóa đơn PDF
     */
    public function downloadInvoice()
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        $code = $_GET['code'] ?? '';
        if (!$code) {
            header('Location: ' . BASE_URL . '?action=my-bookings');
            exit;
        }

        $bookingId = intval(substr($code, 2));
        $booking = $this->bookingModel->getBookingWithDetails($bookingId);

        if (!$booking || $booking['customer_id'] != $_SESSION['user']['user_id']) {
            $_SESSION['error'] = 'Không tìm thấy đơn hàng.';
            header('Location: ' . BASE_URL . '?action=my-bookings');
            exit;
        }

        // Lấy danh sách khách
        $pdo = BaseModel::getPdo();
        $stmt = $pdo->prepare("SELECT * FROM booking_customers WHERE booking_id = :bid");
        $stmt->execute([':bid' => $bookingId]);
        $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once PATH_ROOT . 'services/PdfService.php';
        PdfService::generateBookingInvoice($booking, $passengers);
    }
    /**
     * Hủy đơn hàng từ phía khách hàng
     */
    public function cancel()
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        $code = $_GET['code'] ?? '';
        if (!$code) {
            header('Location: ' . BASE_URL . '?action=my-bookings');
            exit;
        }

        $bookingId = intval(substr($code, 2));
        $booking = $this->bookingModel->getBookingWithDetails($bookingId);

        // 1. Kiểm tra đơn hàng có tồn tại và thuộc về khách hàng này không
        if (!$booking || $booking['customer_id'] != $_SESSION['user']['user_id']) {
            $_SESSION['error'] = 'Không tìm thấy đơn hàng hoặc bạn không có quyền hủy đơn này.';
            header('Location: ' . BASE_URL . '?action=my-bookings');
            exit;
        }

        // 2. Kiểm tra trạng thái có cho phép hủy không (Chỉ cho hủy nếu đang chờ thanh toán/xác nhận)
        $cancellableStatuses = ['pending', 'cho_xac_nhan'];
        if (!in_array($booking['status'], $cancellableStatuses)) {
            $_SESSION['error'] = 'Đơn hàng hiện tại không thể hủy. Vui lòng liên hệ hỗ trợ để được giải quyết.';
            header('Location: ' . BASE_URL . '?action=booking-detail&code=' . $code);
            exit;
        }

        try {
            $this->bookingModel->beginTransaction();

            // 3. Tính toán tiền hoàn lại (Refund Policy)
            $today = new DateTime();
            $departureDate = new DateTime($booking['departure_date']);
            
            // BK_19: Tính số ngày chênh lệch dựa trên ngày (không tính giờ) để đảm bảo độ chính xác
            $todayDate = new DateTime($today->format('Y-m-d'));
            $depDate = new DateTime($departureDate->format('Y-m-d'));
            
            $interval = $todayDate->diff($depDate);
            $daysBefore = $interval->invert ? - $interval->days : $interval->days;

            $refundPercent = 0;
            if ($daysBefore >= 15) {
                $refundPercent = 100;
            } elseif ($daysBefore >= 8) {
                $refundPercent = 80;
            } elseif ($daysBefore >= 3) {
                $refundPercent = 50;
            } elseif ($daysBefore >= 1) {
                $refundPercent = 20;
            } else {
                $refundPercent = 0;
            }

            $totalPrice = $booking['total_price'] ?? $booking['final_price'] ?? 0;
            $refundAmount = ($totalPrice * $refundPercent) / 100;

            // 4. Cập nhật trạng thái booking và số tiền hoàn
            $this->bookingModel->update(
                [
                    'status' => 'da_huy', 
                    'updated_at' => date('Y-m-d H:i:s'),
                    'refund_amount' => $refundAmount,
                    'refund_percentage' => $refundPercent
                ],
                'id = :id',
                ['id' => $bookingId]
            );

            // 5. Hoàn trả suất tour vào kho (inventory)
            // Lấy số lượng khách từ booking_customers + 1 (khách đặt chính)
            $pdo = BaseModel::getPdo();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM booking_customers WHERE booking_id = :bid");
            $stmt->execute([':bid' => $bookingId]);
            $companionCount = (int)$stmt->fetchColumn();
            $totalSeatsToReturn = $companionCount + 1;

            // Cập nhật lại số ghế đã đặt trong tour_departures
            $departure = $this->departureModel->findById($booking['departure_id']);
            if ($departure) {
                $newBookedSeats = max(0, $departure['booked_seats'] - $totalSeatsToReturn);
                $this->departureModel->update(
                    ['booked_seats' => $newBookedSeats],
                    "id = :id",
                    ['id' => $booking['departure_id']]
                );
            }

            $this->bookingModel->commit();
            $_SESSION['success'] = 'Hủy đơn hàng thành công! Suất tour đã được hoàn lại.';

        } catch (Exception $e) {
            $this->bookingModel->rollBack();
            $_SESSION['error'] = 'Có lỗi xảy ra khi hủy đơn: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '?action=my-bookings');
        exit;
    }
}
