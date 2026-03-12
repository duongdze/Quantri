<?php
require_once 'models/BaseModel.php';

class TourDeparture extends BaseModel
{
    protected $table = 'tour_departures';
    protected $columns = [
        'id',
        'tour_id',
        'departure_date',
        'max_seats',
        'price_adult',
        'price_child',
        'status',
        'created_at'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get departures by tour ID
     */
    public function getByTourId($tourId)
    {
        $sql = "SELECT 
                    td.id,
                    td.tour_id,
                    td.version_id,
                    td.departure_date,
                    td.max_seats,
                    td.booked_seats,
                    td.price_adult,
                    td.price_child,
                    td.price_infant,
                    td.status,
                    td.notes,
                    (td.max_seats - td.booked_seats) as available_seats,
                    tv.name as version_name
                FROM tour_departures td
                LEFT JOIN tour_versions tv ON td.version_id = tv.id
                WHERE td.tour_id = :tour_id
                    AND td.status = 'open'
                    AND td.departure_date >= CURDATE()
                ORDER BY td.departure_date ASC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['tour_id' => $tourId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Lấy lịch khởi hành gần nhất (>= hôm nay) cho tour
     * @param int $tourId
     * @return array|null
     */
    public function getNextDepartureByTourId($tourId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tour_id = :tid AND departure_date >= CURDATE() AND (status = 'open' OR status = 'guaranteed') ORDER BY departure_date ASC LIMIT 1";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['tid' => $tourId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function findById($id)
    {
        $item = $this->find('*', 'id = :id', ['id' => $id]);
        return $item ?: null;
    }
}
