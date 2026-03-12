<?php
require_once PATH_MODEL . 'BookingCustomerModel.php';

class BookingCustomerController {
    public function listByBooking() {
        $bookingId = $_GET['id'] ?? $_GET['booking_id'] ?? null;
        if (!$bookingId) {
            die("Thiếu booking_id");
        }

        $booking = BookingCustomerModel::getBookingInfo($bookingId);
        if (!$booking) {
            die("Không tìm thấy thông tin booking.");
        }

        $customers = BookingCustomerModel::getCustomersByBookingId($bookingId);

        require_once PATH_VIEW_ADMIN . 'pages/bookings/customers_list.php';
    }
}
