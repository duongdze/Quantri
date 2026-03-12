<?php

class BookingSupplierAssignment extends BaseModel
{
    protected $table = 'booking_suppliers_assignment';

    protected $columns = [
        'id',
        'booking_id',
        'supplier_id',
        'service_type',
        'quantity',
        'price',
        'notes'
    ];

    /**
     * Lấy tất cả suppliers của 1 booking
     */
    public function getByBookingId($bookingId)
    {
        $sql = "SELECT 
                    bsa.*,
                    s.name AS supplier_name,
                    s.type AS supplier_type,
                    s.contact_person,
                    s.phone AS supplier_phone,
                    s.email AS supplier_email
                FROM {$this->table} AS bsa
                LEFT JOIN suppliers AS s ON bsa.supplier_id = s.id
                WHERE bsa.booking_id = :booking_id
                ORDER BY bsa.service_type, bsa.id";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['booking_id' => $bookingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tính tổng chi phí suppliers cho 1 booking
     */
    public function getTotalCostByBookingId($bookingId)
    {
        $sql = "SELECT SUM(price * quantity) AS total_cost
                FROM {$this->table}
                WHERE booking_id = :booking_id";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['booking_id' => $bookingId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_cost'] ?? 0;
    }

    /**
     * Xóa tất cả suppliers của 1 booking
     */
    public function deleteByBookingId($bookingId)
    {
        return $this->delete('booking_id = :bid', ['bid' => $bookingId]);
    }

    /**
     * Thêm supplier cho booking
     */
    public function addSupplierToBooking($bookingId, $supplierId, $serviceType, $quantity = 1, $price = 0, $notes = '')
    {
        return $this->insert([
            'booking_id' => $bookingId,
            'supplier_id' => $supplierId,
            'service_type' => $serviceType,
            'quantity' => $quantity,
            'price' => $price,
            'notes' => $notes
        ]);
    }

    /**
     * Cập nhật suppliers cho booking (xóa cũ, thêm mới)
     */
    public function updateSuppliersForBooking($bookingId, $suppliers)
    {
        // Xóa suppliers cũ
        $this->deleteByBookingId($bookingId);

        // Thêm suppliers mới
        foreach ($suppliers as $supplier) {
            if (!empty($supplier['supplier_id'])) {
                $this->addSupplierToBooking(
                    $bookingId,
                    $supplier['supplier_id'],
                    $supplier['service_type'] ?? 'other',
                    $supplier['quantity'] ?? 1,
                    $supplier['price'] ?? 0,
                    $supplier['notes'] ?? ''
                );
            }
        }

        return true;
    }
}
