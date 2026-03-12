<?php

class BookingController
{
    protected $model;

    public function __construct()
    {
        $this->model = new Booking();
    }

    public function index()
    {
        // Lấy thông tin user hiện tại
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        $userId = $_SESSION['user']['user_id'] ?? null;

        // Lọc bookings theo role
        if ($userRole === 'guide') {
            $guideModel = new Guide();
            $guide = $guideModel->getByUserId($userId);
            $guideId = $guide['id'] ?? null;
            $bookings = $this->model->getAllByRole('guide', $guideId);
        } else {
            $bookings = $this->model->getAllByRole('admin');
        }

        // Lấy thống kê
        $stats = $this->model->getStats();

        require_once PATH_VIEW_ADMIN . 'pages/bookings/index.php';
    }

    public function create()
    {
        // Load customers and tours data for dropdown
        $customerModel = new UserModel();
        $tourModel = new Tour();
        $versionModel = new TourVersion();
        $supplierModel = new Supplier();

        $customers = $customerModel->select('*', "role = :role", ['role' => 'customer']);
        $tours = $tourModel->select('*', null, [], 'name ASC');
        $versions = $versionModel->getActiveVersionsWithPrices();
        $suppliers = $supplierModel->select();

        require_once PATH_VIEW_ADMIN . 'pages/bookings/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location:' . BASE_URL_ADMIN . '&action=bookings/create');
            exit;
        }

        try {
            // Validate inputs
            $customer_id = $_POST['customer_id'] ?? null;
            $tour_id = $_POST['tour_id'] ?? null;
            $version_id = $_POST['version_id'] ?? null; // Phiên bản tour (tùy chọn)
            $booking_date = $_POST['booking_date'] ?? null;
            $total_price = $_POST['total_price'] ?? null;
            $status = $_POST['status'] ?? 'cho_xac_nhan';
            $notes = $_POST['notes'] ?? '';

            // Basic validation
            if (!$customer_id || !$tour_id || !$booking_date || !$total_price || !$status) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin bắt buộc';
                header('Location:' . BASE_URL_ADMIN . '&action=bookings/create');
                exit;
            }

            // Insert booking (không có supplier_id nữa)
            $booking_id = $this->model->insert([
                'customer_id' => $customer_id,
                'tour_id' => $tour_id,
                'version_id' => !empty($version_id) ? $version_id : null,
                'departure_id' => $_POST['departure_id'] ?? null,
                'booking_date' => $booking_date,
                'total_price' => $total_price,
                'final_price' => $total_price,
                'status' => $status,
                'notes' => $notes,
                'created_by' => $_SESSION['user']['user_id'] ?? null
            ]);


            // Tự động thêm supplier từ tour vào booking_suppliers_assignment
            if ($booking_id) {
                $tourModel = new Tour();
                $tour = $tourModel->find('*', 'id = :id', ['id' => $tour_id]);

                if (!empty($tour['supplier_id'])) {
                    $bsaModel = new BookingSupplierAssignment();
                    $bsaModel->addSupplierToBooking(
                        $booking_id,
                        $tour['supplier_id'],
                        'tour_operator',
                        1,
                        0,
                        'Supplier mặc định từ tour'
                    );
                }
            }

            // Insert booking customers (companions)
            if (!empty($_POST['companion_name'])) {
                $bookingCustomerModel = new BookingCustomer();

                foreach ($_POST['companion_name'] as $index => $name) {
                    if (!empty($name)) {
                        $bookingCustomerModel->insert([
                            'booking_id' => $booking_id,
                            'full_name' => $name,
                            'passenger_type' => $_POST['companion_passenger_type'][$index] ?? 'adult',
                            'is_foc' => isset($_POST['companion_is_foc'][$index]) ? 1 : 0,
                            'gender' => $_POST['companion_gender'][$index] ?? '',
                            'birth_date' => $_POST['companion_birth_date'][$index] ?? null,
                            'phone' => $_POST['companion_phone'][$index] ?? '',
                            'id_card' => $_POST['companion_id_card'][$index] ?? '',
                            'special_request' => $_POST['companion_special_request'][$index] ?? '',
                            'room_type' => $_POST['companion_room_type'][$index] ?? ''
                        ]);
                    }
                }

                // Recalculate total price based on passenger types
                $calculation = $bookingCustomerModel->calculateTotalPrice($booking_id, $tour_id, $version_id);

                // Update booking with calculated price
                $this->model->update([
                    'total_price' => $calculation['total'],
                    'final_price' => $calculation['total']
                ], 'id = :id', ['id' => $booking_id]);
            }

            $_SESSION['success'] = 'Tạo đơn đặt tour thành công';
            header('Location:' . BASE_URL_ADMIN . '&action=bookings');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
            header('Location:' . BASE_URL_ADMIN . '&action=bookings/create');
            exit;
        }
    }

    public function detail()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy booking';
            header('Location:' . BASE_URL_ADMIN . '&action=bookings');
            exit;
        }

        // Handle price update from calculator widget
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_price'])) {
            $calculatedTotal = $_POST['calculated_total'] ?? null;

            if ($calculatedTotal) {
                try {
                    $this->model->update([
                        'total_price' => $calculatedTotal,
                        'final_price' => $calculatedTotal
                    ], 'id = :id', ['id' => $id]);

                    $_SESSION['success'] = 'Cập nhật giá thành công!';
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Lỗi khi cập nhật giá: ' . $e->getMessage();
                }
            }

            // Redirect to prevent form resubmission
            header('Location: ' . BASE_URL_ADMIN . '&action=bookings/detail&id=' . $id);
            exit;
        }

        $booking = $this->model->getBookingWithDetails($id);

        if (!$booking) {
            $_SESSION['error'] = 'Booking không tồn tại';
            header('Location:' . BASE_URL_ADMIN . '&action=bookings');
            exit;
        }

        // Lấy danh sách khách đi kèm
        $bookingCustomerModel = new BookingCustomer();
        $companions = $bookingCustomerModel->getByBooking($id);

        require_once PATH_VIEW_ADMIN . 'pages/bookings/detail.php';
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy booking';
            header('Location:' . BASE_URL_ADMIN . '&action=bookings');
            exit;
        }

        // Kiểm tra quyền
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        $userId = $_SESSION['user']['user_id'] ?? null;

        if (!$this->model->canUserEditBooking($id, $userId, $userRole)) {
            $_SESSION['error'] = 'Bạn không có quyền sửa booking này';
            header('Location:' . BASE_URL_ADMIN . '&action=bookings');
            exit;
        }

        $booking = $this->model->getBookingWithDetails($id);

        if (!$booking) {
            $_SESSION['error'] = 'Booking không tồn tại';
            header('Location:' . BASE_URL_ADMIN . '&action=bookings');
            exit;
        }

        // Lấy danh sách khách đi kèm
        $bookingCustomerModel = new BookingCustomer();
        $companions = $bookingCustomerModel->getByBooking($id);

        // Load customers and tours data for dropdown
        $customerModel = new UserModel();
        $tourModel = new Tour();
        $versionModel = new TourVersion();

        $customers = $customerModel->select('*', "role = :role", ['role' => 'customer']);
        $tours = $tourModel->select('*', null, [], 'name ASC');
        $versions = $versionModel->getActiveVersionsWithPrices();

        // If booking has an inactive version, add it to the list so it can be displayed
        if ($booking['version_id']) {
            $currentVersion = $versionModel->findById($booking['version_id']);
            if ($currentVersion && $currentVersion['status'] === 'inactive') {
                // Check if version is not already in the list
                $versionExists = false;
                foreach ($versions as $v) {
                    if ($v['id'] == $booking['version_id']) {
                        $versionExists = true;
                        break;
                    }
                }

                if (!$versionExists) {
                    // Get version with prices
                    $versionPriceModel = new TourVersionPrice();
                    $priceInfo = $versionPriceModel->getByVersionId($booking['version_id']);
                    $currentVersion = array_merge($currentVersion, $priceInfo ?: []);
                    $currentVersion['is_inactive'] = true; // Mark as inactive for UI
                    $versions[] = $currentVersion;
                }
            }
        }

        // Get bus companies list
        $busCompanyModel = new BusCompany();
        $busCompanies = $busCompanyModel->getActiveBusCompanies();

        // Get guides list
        $guideModel = new Guide();
        $guides = $guideModel->getAll();

        // Get suppliers list
        $supplierModel = new Supplier();
        $suppliers = $supplierModel->select();

        // Get booking suppliers (many-to-many)
        $bsaModel = new BookingSupplierAssignment();
        $bookingSuppliers = $bsaModel->getByBookingId($id);

        // Auto-add tour supplier if booking has no suppliers yet
        if (empty($bookingSuppliers) && !empty($booking['tour_supplier_id'])) {
            // Tự động thêm supplier từ tour cho booking cũ
            $bsaModel->addSupplierToBooking(
                $id,
                $booking['tour_supplier_id'],
                'tour_operator',
                1,
                0,
                'Supplier mặc định từ tour (tự động thêm)'
            );
            // Reload suppliers
            $bookingSuppliers = $bsaModel->getByBookingId($id);
        }

        // Load version prices for passenger type pricing
        $versionPriceModel = new TourVersionPrice();
        $versionPrices = $versionPriceModel->getPriceForBooking($booking['tour_id'], $booking['version_id']);

        require_once PATH_VIEW_ADMIN . 'pages/bookings/edit.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location:' . BASE_URL_ADMIN . '&action=bookings');
            exit;
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy booking';
            header('Location:' . BASE_URL_ADMIN . '&action=bookings');
            exit;
        }

        // Kiểm tra quyền
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        $userId = $_SESSION['user']['user_id'] ?? null;

        if (!$this->model->canUserEditBooking($id, $userId, $userRole)) {
            $_SESSION['error'] = 'Bạn không có quyền sửa booking này';
            header('Location:' . BASE_URL_ADMIN . '&action=bookings');
            exit;
        }

        try {
            // Validate inputs
            $customer_id = $_POST['customer_id'] ?? null;
            $tour_id = $_POST['tour_id'] ?? null;
            $booking_date = $_POST['booking_date'] ?? null;
            $total_price = $_POST['total_price'] ?? null;
            $status = $_POST['status'] ?? 'cho_xac_nhan';
            $notes = $_POST['notes'] ?? '';
            $version_id = $_POST['version_id'] ?? null;

            // Basic validation
            if (!$customer_id || !$tour_id || !$booking_date || !$total_price || !$status) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin bắt buộc';
                header('Location:' . BASE_URL_ADMIN . '&action=bookings/edit&id=' . $id);
                exit;
            }

            // Update booking (không có supplier_id nữa)
            $this->model->update([
                'customer_id' => $customer_id,
                'tour_id' => $tour_id,
                'version_id' => !empty($version_id) ? $version_id : null,
                'booking_date' => $booking_date,
                'total_price' => $total_price,
                'final_price' => $total_price,
                'status' => $status,
                'bus_company_id' => !empty($_POST['bus_company_id']) ? $_POST['bus_company_id'] : null,
                'notes' => $notes
            ], 'id = :id', ['id' => $id]);

            // Cập nhật booking suppliers (many-to-many)
            if (isset($_POST['suppliers']) && is_array($_POST['suppliers'])) {
                $bsaModel = new BookingSupplierAssignment();
                $bsaModel->updateSuppliersForBooking($id, $_POST['suppliers']);
            }

            // NOTE: Companions are managed separately via AJAX endpoints
            // (addCompanion, updateCompanion, deleteCompanion)
            // Do NOT delete and recreate companions here to prevent data loss

            // Recalculate total price based on version and companions
            // NOTE: Customer (booker) is always counted as 1 adult by default
            $bookingCustomerModel = new BookingCustomer();
            $companions = $bookingCustomerModel->getByBooking($id);

            // Always recalculate if version_id is set (even without companions)
            // because customer (booker) counts as 1 adult
            if ($version_id || !empty($companions)) {
                $calculation = $bookingCustomerModel->calculateTotalPrice($id, $tour_id, $version_id);

                // Update booking with calculated price
                $this->model->update([
                    'total_price' => $calculation['total'],
                    'final_price' => $calculation['total']
                ], 'id = :id', ['id' => $id]);
            }

            $_SESSION['success'] = 'Cập nhật đơn đặt tour thành công';
            header('Location:' . BASE_URL_ADMIN . '&action=bookings/detail&id=' . $id);
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
            header('Location:' . BASE_URL_ADMIN . '&action=bookings/edit&id=' . $id);
            exit;
        }
    }

    public function delete()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy booking';
            header('Location:' . BASE_URL_ADMIN . '&action=bookings');
            exit;
        }

        // Kiểm tra quyền - chỉ admin mới được xóa
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        if ($userRole !== 'admin') {
            $_SESSION['error'] = 'Chỉ admin mới có quyền xóa booking';
            header('Location:' . BASE_URL_ADMIN . '&action=bookings');
            exit;
        }

        try {
            $result = $this->model->deleteBooking($id);

            if ($result) {
                $_SESSION['success'] = 'Xóa booking thành công';
            } else {
                $_SESSION['error'] = 'Không thể xóa booking';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }

        header('Location:' . BASE_URL_ADMIN . '&action=bookings');
        exit;
    }

    /**
     * AJAX endpoint để cập nhật trạng thái booking
     */
    public function updateStatus()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $bookingId = $_POST['booking_id'] ?? null;
        $newStatus = $_POST['status'] ?? null;

        if (!$bookingId || !$newStatus) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc']);
            exit;
        }

        // Kiểm tra quyền
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        $userId = $_SESSION['user']['user_id'] ?? null;

        if (!$this->model->canUserEditBooking($bookingId, $userId, $userRole)) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền cập nhật booking này']);
            exit;
        }

        // Validate status
        $validStatuses = ['cho_xac_nhan', 'da_coc', 'hoan_tat', 'da_huy'];
        if (!in_array($newStatus, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
            exit;
        }

        // Cập nhật trạng thái
        $result = $this->model->updateStatus($bookingId, $newStatus);

        if ($result) {
            // Lấy tên trạng thái để hiển thị
            $statusNames = [
                'cho_xac_nhan' => 'Chờ xác nhận',
                'da_coc' => 'Đã cọc',
                'hoan_tat' => 'Hoàn tất',
                'da_huy' => 'Hủy'
            ];

            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công',
                'status' => $newStatus,
                'status_text' => $statusNames[$newStatus] ?? $newStatus
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái']);
        }
        exit;
    }
    public function addCompanion()
    {
        header('Content-Type: application/json');
        // Kiểm tra quyền
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        $userId = $_SESSION['user']['user_id'] ?? null;
        $bookingId = $_POST['booking_id'] ?? null;
        if (!$bookingId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin booking']);
            exit;
        }
        // Kiểm tra quyền sửa booking này
        if (!$this->model->canUserEditBooking($bookingId, $userId, $userRole)) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thêm khách cho booking này']);
            exit;
        }
        // Lấy dữ liệu từ form
        $data = [
            'booking_id' => $bookingId,
            'full_name' => $_POST['name'] ?? '',
            'gender' => $_POST['gender'] ?? null,
            'birth_date' => $_POST['birth_date'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'id_card' => $_POST['id_card'] ?? null,
            'room_type' => $_POST['room_type'] ?? null,
            'passenger_type' => $_POST['passenger_type'] ?? 'adult',
            'special_request' => $_POST['special_request'] ?? null
        ];
        // Validate
        if (empty($data['full_name'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập họ tên khách']);
            exit;
        }
        // Thêm vào database
        try {
            $companionModel = new BookingCustomer();
            $companionId = $companionModel->insert($data);

            echo json_encode([
                'success' => true,
                'message' => 'Thêm khách đi kèm thành công',
                'companion_id' => $companionId,
                'companion' => $data
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }
    /**
     * Cập nhật thông tin khách đi kèm
     * AJAX endpoint
     */
    public function updateCompanion()
    {
        header('Content-Type: application/json');
        $companionId = $_POST['companion_id'] ?? null;
        $bookingId = $_POST['booking_id'] ?? null;
        if (!$companionId || !$bookingId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            exit;
        }
        // Kiểm tra quyền
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        $userId = $_SESSION['user']['user_id'] ?? null;
        if (!$this->model->canUserEditBooking($bookingId, $userId, $userRole)) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền sửa khách này']);
            exit;
        }
        // Lấy dữ liệu từ form
        $data = [
            'full_name' => $_POST['name'] ?? '',
            'gender' => $_POST['gender'] ?? null,
            'birth_date' => $_POST['birth_date'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'id_card' => $_POST['id_card'] ?? null,
            'room_type' => $_POST['room_type'] ?? null,
            'passenger_type' => $_POST['passenger_type'] ?? 'adult',
            'special_request' => $_POST['special_request'] ?? null
        ];

        // Validate
        if (empty($data['full_name'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập họ tên khách']);
            exit;
        }
        // Cập nhật database
        try {
            $companionModel = new BookingCustomer();
            $companionModel->update($data, 'id = :id', ['id' => $companionId]);

            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật thông tin khách thành công',
                'companion' => $data
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }
    /**
     * Xóa khách đi kèm
     * AJAX endpoint
     */
    public function deleteCompanion()
    {
        header('Content-Type: application/json');
        $companionId = $_POST['companion_id'] ?? null;
        $bookingId = $_POST['booking_id'] ?? null;
        if (!$companionId || !$bookingId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            exit;
        }
        // Kiểm tra quyền
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        $userId = $_SESSION['user']['user_id'] ?? null;
        if (!$this->model->canUserEditBooking($bookingId, $userId, $userRole)) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa khách này']);
            exit;
        }
        // Xóa khỏi database
        try {
            $companionModel = new BookingCustomer();
            // FIX: Sử dụng đúng cú pháp delete với WHERE clause
            $companionModel->delete('id = :id', ['id' => $companionId]);

            echo json_encode([
                'success' => true,
                'message' => 'Xóa khách đi kèm thành công'
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Cập nhật yêu cầu đặc biệt (dành cho HDV)
     * AJAX endpoint
     */
    public function updateSpecialRequest()
    {
        header('Content-Type: application/json');

        $companionId = $_POST['companion_id'] ?? null;
        $bookingId = $_POST['booking_id'] ?? null;
        $specialRequest = $_POST['special_request'] ?? '';

        if (!$companionId || !$bookingId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            exit;
        }

        // Kiểm tra quyền - HDV chỉ được sửa booking được phân công
        $userRole = $_SESSION['user']['role'] ?? 'customer';
        $userId = $_SESSION['user']['user_id'] ?? null;

        if (!$this->model->canUserEditBooking($bookingId, $userId, $userRole)) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền cập nhật yêu cầu này']);
            exit;
        }

        // Cập nhật special_request
        try {
            $companionModel = new BookingCustomer();
            $companionModel->update([
                'special_request' => $specialRequest
            ], 'id = :id', ['id' => $companionId]);

            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật yêu cầu đặc biệt thành công'
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Hiển thị trang check-in
     */
    public function checkin()
    {
        $bookingId = $_GET['id'] ?? null;

        if (!$bookingId) {
            $_SESSION['error'] = 'Không tìm thấy booking';
            header('Location: ' . BASE_URL_ADMIN . '&action=bookings');
            return;
        }

        // Lấy thông tin booking
        $booking = $this->model->getById($bookingId);
        if (!$booking) {
            $_SESSION['error'] = 'Booking không tồn tại';
            header('Location: ' . BASE_URL_ADMIN . '&action=bookings');
            return;
        }

        // Lấy thông tin tour
        $tourModel = new Tour();
        $tour = $tourModel->find('*', 'id = :id', ['id' => $booking['tour_id']]);

        // Lấy danh sách khách
        $customerModel = new BookingCustomer();
        $customers = $customerModel->getCustomersWithCheckinStatus($bookingId);
        $stats = $customerModel->getCheckinStats($bookingId);

        require_once PATH_VIEW_ADMIN . 'pages/bookings/checkin.php';
    }

    /**
     * Cập nhật trạng thái check-in (AJAX)
     */
    public function updateCheckin()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            exit;
        }

        $customerId = $_POST['customer_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $notes = $_POST['notes'] ?? null;

        if (!$customerId || !$status) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        // Validate status
        if (!in_array($status, ['not_arrived', 'checked_in', 'absent'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }


        try {
            $userId = $_SESSION['user']['user_id'] ?? null;
            $customerModel = new BookingCustomer();

            // Check if this is a main customer (ID starts with 'main_')
            if (strpos($customerId, 'main_') === 0) {
                // Extract booking_id from 'main_42' -> 42
                $bookingId = (int)str_replace('main_', '', $customerId);

                // Get booking info with customer_name
                $sql = "SELECT 
                            B.*,
                            U.full_name AS customer_name
                        FROM bookings AS B
                        LEFT JOIN users AS U ON B.customer_id = U.user_id
                        WHERE B.id = :booking_id";

                $pdo = BaseModel::getPdo();
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['booking_id' => $bookingId]);
                $booking = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$booking) {
                    echo json_encode(['success' => false, 'message' => 'Không tìm thấy booking']);
                    exit;
                }

                // Check if main customer record already exists
                $sql = "SELECT id FROM booking_customers 
                        WHERE booking_id = :booking_id 
                        AND full_name = :full_name 
                        LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'booking_id' => $bookingId,
                    'full_name' => $booking['customer_name']
                ]);
                $existingCustomer = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingCustomer) {
                    // Update existing record
                    $result = $customerModel->updateCheckinStatus(
                        $existingCustomer['id'],
                        $status,
                        $userId,
                        $notes
                    );

                    if ($result) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Cập nhật thành công',
                            'timestamp' => date('H:i d/m/Y')
                        ]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
                    }
                } else {
                    // Create new booking_customer record for main customer
                    $customerData = [
                        'booking_id' => $bookingId,
                        'full_name' => $booking['customer_name'] ?? 'N/A',
                        'passenger_type' => 'adult',
                        'is_foc' => 0,
                        'checkin_status' => $status,
                        'checked_by' => $userId,
                        'checkin_time' => ($status !== 'not_arrived') ? date('Y-m-d H:i:s') : null,
                        'checkin_notes' => $notes
                    ];

                    $newCustomerId = $customerModel->insert($customerData);

                    if ($newCustomerId) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Cập nhật thành công',
                            'timestamp' => date('H:i d/m/Y')
                        ]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Không thể tạo record']);
                    }
                }
            } else {
                // Regular customer - update existing record
                $result = $customerModel->updateCheckinStatus(
                    $customerId,
                    $status,
                    $userId,
                    $notes
                );

                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Cập nhật thành công',
                        'timestamp' => date('H:i d/m/Y')
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
                }
            }
        } catch (Exception $e) {
            error_log('Check-in error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Check-in hàng loạt
     */
    public function bulkCheckin()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            return;
        }

        $customerIds = $_POST['customer_ids'] ?? [];
        $status = $_POST['status'] ?? 'checked_in';

        if (empty($customerIds)) {
            echo json_encode(['success' => false, 'message' => 'Chưa chọn khách']);
            return;
        }

        try {
            $userId = $_SESSION['user']['user_id'] ?? null;
            $customerModel = new BookingCustomer();
            $count = 0;

            foreach ($customerIds as $customerId) {
                if ($customerModel->updateCheckinStatus($customerId, $status, $userId)) {
                    $count++;
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "Đã check-in {$count} khách",
                'count' => $count
            ]);
        } catch (Exception $e) {
            error_log('Bulk check-in error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * In danh sách đoàn
     */
    public function printGroupList()
    {
        $bookingId = $_GET['id'] ?? null;

        if (!$bookingId) {
            $_SESSION['error'] = 'Không tìm thấy booking';
            header('Location: ' . BASE_URL_ADMIN . '&action=bookings');
            return;
        }

        // Lấy thông tin booking
        $booking = $this->model->getById($bookingId);
        if (!$booking) {
            $_SESSION['error'] = 'Booking không tồn tại';
            header('Location: ' . BASE_URL_ADMIN . '&action=bookings');
            return;
        }

        // Lấy thông tin tour
        $tourModel = new Tour();
        $tour = $tourModel->find('*', 'id = :id', ['id' => $booking['tour_id']]);

        // Lấy TẤT CẢ booking của tour này (chỉ đã cọc và chờ xác nhận)
        $pdo = $this->model->getPdo();
        $stmt = $pdo->prepare("
            SELECT b.*, u.full_name, u.email, u.phone
            FROM bookings b
            LEFT JOIN users u ON b.customer_id = u.user_id
            WHERE b.tour_id = :tour_id 
            AND b.status IN ('da_coc', 'cho_xac_nhan')
        ");
        $stmt->execute([
            ':tour_id' => $booking['tour_id']
        ]);
        $allBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy danh sách khách từ TẤT CẢ booking
        $customerModel = new BookingCustomer();
        $customers = [];

        foreach ($allBookings as $bk) {
            // Lấy các khách đi kèm từ booking_customers
            $bookingCustomers = $customerModel->getByBooking($bk['id']);

            // Kiểm tra xem người đặt booking đã có trong booking_customers chưa
            $mainCustomerExists = false;
            foreach ($bookingCustomers as $customer) {
                if ($customer['full_name'] === $bk['full_name']) {
                    $mainCustomerExists = true;
                    break;
                }
            }

            // 1. Chỉ thêm người đặt booking nếu CHƯA có trong booking_customers
            if (!empty($bk['full_name']) && !$mainCustomerExists) {
                $bookingCustomer = [
                    'full_name' => $bk['full_name'],
                    'gender' => '', // Không có trong users table
                    'birth_date' => '', // Không có trong users table
                    'id_card' => '', // Không có trong users table
                    'passenger_type' => 'adult', // Người đặt thường là người lớn
                    'room_type' => '',
                    'special_request' => '',
                    'is_foc' => 0,
                    'booking_code' => $bk['id'],
                    'booking_customer_name' => $bk['full_name']
                ];
                $customers[] = $bookingCustomer;
            }

            // 2. Thêm các khách đi kèm (từ bảng booking_customers)
            foreach ($bookingCustomers as $customer) {
                $customer['booking_code'] = $bk['id'];
                $customer['booking_customer_name'] = $bk['full_name'] ?? 'N/A';
                $customers[] = $customer;
            }
        }

        // Thống kê theo loại khách
        $stats = [
            'adults' => 0,
            'children' => 0,
            'infants' => 0,
            'total' => count($customers)
        ];


        foreach ($customers as $customer) {
            $type = $customer['passenger_type'] ?? 'adult';
            if ($type === 'adult') $stats['adults']++;
            elseif ($type === 'child') $stats['children']++;
            elseif ($type === 'infant') $stats['infants']++;
        }

        require_once PATH_VIEW_ADMIN . 'pages/bookings/print-group-list.php';
    }

    /**
     * AJAX endpoint to get departures by tour ID
     */
    public function getDeparturesByTour()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $tourId = $_GET['tour_id'] ?? null;

        if (!$tourId) {
            echo json_encode(['success' => false, 'message' => 'Tour ID is required']);
            exit;
        }

        try {
            $departureModel = new TourDeparture();
            $departures = $departureModel->getByTourId($tourId);

            // Get tour base price for fallback
            $tourModel = new Tour();
            $tour = $tourModel->find('*', 'id = :id', ['id' => $tourId]);
            $basePrice = $tour['base_price'] ?? 0;

            // Format departures for response
            $formattedDepartures = array_map(function ($dep) use ($basePrice) {
                return [
                    'id' => $dep['id'],
                    'departure_date' => $dep['departure_date'],
                    'formatted_date' => date('d/m/Y', strtotime($dep['departure_date'])),
                    'price_adult' => $dep['price_adult'] ?: $basePrice,
                    'price_child' => $dep['price_child'] ?: ($dep['price_adult'] ?: $basePrice),
                    'price_infant' => $dep['price_infant'] ?: 0,
                    'available_seats' => $dep['available_seats'],
                    'max_seats' => $dep['max_seats'],
                    'version_name' => $dep['version_name'] ?? null
                ];
            }, $departures);

            echo json_encode([
                'success' => true,
                'departures' => $formattedDepartures,
                'base_price' => $basePrice
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching departures: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}
