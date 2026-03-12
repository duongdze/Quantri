    <?php
require_once 'BaseModel.php';

class TourPricing extends BaseModel
{
    protected $table = 'tour_pricing_options';
    protected $columns = [
        'id',
        'tour_id',
        'label',
        'description',
        'created_at'
    ];

    public function getByTourId($tourId)
    {
        return $this->select('*', 'tour_id = :tour_id', ['tour_id' => $tourId], 'id ASC');
    }
}
