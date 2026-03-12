<?php
class TourImage extends BaseModel
{
    protected $table = 'tour_gallery_images';
    protected $primaryKey = 'id';
    protected $columns = [
        'id',
        'tour_id',
        'image_url',
        'caption',
        'main_img',
        'sort_order',
        'created_at'
    ];

    /**
     * Get all images for a specific tour
     */
    public function getByTourId($tourId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE tour_id = :tour_id 
                ORDER BY sort_order ASC, id ASC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['tour_id' => $tourId]);

        return $stmt->fetchAll();
    }

    /**
     * Get main image for a tour
     */
    public function getMainImage($tourId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE tour_id = :tour_id AND main_img = 1 
                LIMIT 1";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['tour_id' => $tourId]);

        return $stmt->fetch();
    }

    /**
     * Insert new tour image
     */
    public function insert($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (tour_id, image_url, caption, main_img, sort_order, created_at) 
                VALUES (:tour_id, :image_url, :caption, :main_img, :sort_order, NOW())";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            'tour_id' => $data['tour_id'],
            'image_url' => $data['image_url'],
            'caption' => $data['caption'] ?? '',
            'main_img' => $data['main_img'] ?? 0,
            'sort_order' => $data['sort_order'] ?? 0
        ]);

        return self::$pdo->lastInsertId();
    }

    /**
     * Delete all images for a tour
     */
    public function deleteByTourId($tourId)
    {
        $sql = "DELETE FROM {$this->table} WHERE tour_id = :tour_id";

        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute(['tour_id' => $tourId]);
    }
}
