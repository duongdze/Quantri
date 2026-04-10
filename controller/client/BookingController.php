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
        if (empty($_POST['full_name']) || empty($_POST['phone']) || empty($_POST['email'])) {
             $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
             header("Location: " . BASE_URL . "?action=booking-create&tour_id=$tourId&departure_id=$departureId");
             exit;
        }

        $adults = max(1, (int)$_POST['adults']);
        $children = max(0, (int)($_POST['children'] ?? 0));
        $totalSeats = $adults + $children;

        // Check availability
        $departure = $this->departureModel->findById($departureId);
        if ($departure['max_seats'] - $departure['booked_seats'] < $totalSeats) {
             $_SESSION['error'] = 'Số chỗ còn lại không đủ!';
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
}
