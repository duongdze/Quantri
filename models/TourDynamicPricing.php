<?php
require_once 'BaseModel.php';

class TourDynamicPricing extends BaseModel
{
    protected $table = 'version_dynamic_pricing';
    protected $columns = [
        'id',
        'version_id',
        'departure_id',
        'apply_type',
        'amount',
        'amount_type',
        'start_date',
        'end_date',
        'notes',
        'created_at',
    ];

    public function getByVersionId($versionId)
    {
        return $this->select('*', 'version_id = :version_id', ['version_id' => $versionId], 'id ASC');
    }

    public function getByDepartureId($departureId)
    {
        return $this->select('*', 'departure_id = :departure_id', ['departure_id' => $departureId], 'id ASC');
    }
}
