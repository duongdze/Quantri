<?php
class BookingCustomerModel
{
    private static function ensurePdo()
    {
        if (BaseModel::getPdo() === null) {
            new BaseModel();
        }
        return BaseModel::getPdo();
    }

    // Lấy thông tin tour để hiển thị tiêu đề
    public static function getBookingInfo($bookingId)
    {
        $pdo = self::ensurePdo();
        $sql = "SELECT B.id, T.name AS tour_name
                FROM bookings B
                JOIN tours T ON B.tour_id = T.id
                WHERE B.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$bookingId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Lấy danh sách khách trong đoàn
    public static function getCustomersByBookingId($bookingId)
    {
        $pdo = self::ensurePdo();
        $sql = "SELECT name, gender, birth_date, phone, id_card, special_request,
                   room_type, passenger_type, is_foc
            FROM booking_customers
            WHERE booking_id = ?
            ORDER BY id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$bookingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
