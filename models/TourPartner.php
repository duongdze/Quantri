<?php
require_once 'BaseModel.php';

class TourPartner extends BaseModel
{
    protected $table = 'tour_partner_services';
    protected $columns = [
        'id',
        'tour_id',
        'service_type',
        'partner_name',
        'contact',
        'notes',
        'created_at'
    ];

    public function getByTourId($tourId)
    {
        return $this->select('*', 'tour_id = :tour_id', ['tour_id' => $tourId], 'id ASC');
    }
}
