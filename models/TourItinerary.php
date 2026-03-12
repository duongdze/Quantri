<?php
class TourItinerary extends BaseModel
{
    // Table name in DB is `itineraries` according to project schema
    protected $table = 'itineraries';
    protected $columns = [
        'id',
        'tour_id',
        'day_label',
        'day_number',
        'time_start',
        'time_end',
        'title',
        'description',
        'activities',
        'image_url'
    ];
}
