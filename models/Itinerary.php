<?php
require_once 'models/BaseModel.php';

class Itinerary extends BaseModel
{
    protected $table = 'itineraries';
    protected $columns = [
        'id',
        'tour_id',
        'day_number',
        'title',
        'activities',
        'image_url'
    ];

    public function __construct()
    {
        parent::__construct();
    }
}
