<?php
require_once 'BaseModel.php';

class TourVersionPrice extends BaseModel
{
    protected $table = 'tour_version_prices';
    protected $columns = [
        'id',
        'version_id',
        'adult_percent',
        'child_percent',
        'infant_percent',
        'child_base_percent',
        'infant_base_percent',
        'created_at'
    ];

    /**
     * Get price by version_id
     * 
     * @param int $versionId
     * @return array|null
     */
    public function getByVersionId($versionId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE version_id = :version_id LIMIT 1";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['version_id' => $versionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Upsert price (insert or update)
     * 
     * @param int $versionId
     * @param array $priceData ['adult_percent', 'child_percent', 'infant_percent', 'child_base_percent', 'infant_base_percent']
     * @return bool
     */
    public function upsertPrice($versionId, $priceData)
    {
        try {
            // Check if price exists
            $existing = $this->getByVersionId($versionId);

            $data = [
                'version_id' => $versionId,
                'adult_percent' => $priceData['adult_percent'] ?? 0,
                'child_percent' => $priceData['child_percent'] ?? 0,
                'infant_percent' => $priceData['infant_percent'] ?? 0,
                'child_base_percent' => $priceData['child_base_percent'] ?? 75,
                'infant_base_percent' => $priceData['infant_base_percent'] ?? 50
            ];

            if ($existing) {
                // Update existing price - use update() method from BaseModel
                unset($data['version_id']); // Don't update version_id
                error_log('Updating tour version price: ' . json_encode($data));
                $result = $this->update($data, 'id = :id', ['id' => $existing['id']]);
                error_log('Update result: ' . ($result ? 'success' : 'failed'));
                return $result;
            } else {
                // Insert new price
                $data['created_at'] = date('Y-m-d H:i:s');
                error_log('Inserting tour version price: ' . json_encode($data));
                $result = $this->insert($data);
                error_log('Insert result: ' . ($result ? 'success' : 'failed'));
                return $result;
            }
        } catch (Exception $e) {
            error_log('Error in upsertPrice: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            throw $e; // Re-throw to be caught by controller
        }
    }

    /**
     * Delete price by version_id
     * 
     * @param int $versionId
     * @return bool
     */
    public function deleteByVersionId($versionId)
    {
        return $this->delete('version_id = :version_id', ['version_id' => $versionId]);
    }

    /**
     * Get all prices with version info
     * 
     * @return array
     */
    public function getAllWithVersionInfo()
    {
        $sql = "SELECT tvp.*, tv.name as version_name, tv.status as version_status
                FROM {$this->table} tvp
                INNER JOIN tour_versions tv ON tvp.version_id = tv.id
                ORDER BY tv.name ASC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy giá cho booking (ưu tiên version, fallback tour base_price)
     * 
     * @param int $tourId
     * @param int|null $versionId
     * @return array ['price_adult', 'price_child', 'price_infant']
     */
    public function getPriceForBooking($tourId, $versionId = null)
    {
        // Get tour base price
        $tourModel = new Tour();
        $tour = $tourModel->find('*', 'id = :id', ['id' => $tourId]);
        $basePrice = $tour['base_price'] ?? 0;

        // Try version first
        if ($versionId) {
            $version = $this->getByVersionId($versionId);
            if ($version) {
                // Tính giá từ % tăng/giảm
                return $this->calculatePricesFromPercent($basePrice, $version);
            }
        }

        // Fallback: default percentages (no version)
        return [
            'price_adult' => $basePrice,
            'price_child' => $basePrice * 0.75,  // 75% of adult
            'price_infant' => $basePrice * 0.50   // 50% of adult
        ];
    }

    /**
     * Tính giá từ % tăng/giảm
     * 
     * @param float $basePrice
     * @param array $version
     * @return array
     */
    private function calculatePricesFromPercent($basePrice, $version)
    {
        // Lấy % tăng/giảm
        $adultPercent = $version['adult_percent'] ?? 0;
        $childPercent = $version['child_percent'] ?? 0;
        $infantPercent = $version['infant_percent'] ?? 0;

        // Lấy tỷ lệ base
        $childBasePercent = $version['child_base_percent'] ?? 75;
        $infantBasePercent = $version['infant_base_percent'] ?? 50;

        // Tính giá người lớn
        // Ví dụ: base_price = 10tr, adult_percent = +20% → 12tr
        $priceAdult = $basePrice * (1 + $adultPercent / 100);

        // Tính giá trẻ em
        // Ví dụ: base_price = 10tr, child_base_percent = 75% → 7.5tr
        //        child_percent = +10% → 7.5tr × 1.1 = 8.25tr
        $priceChild = ($basePrice * $childBasePercent / 100) * (1 + $childPercent / 100);

        // Tính giá em bé (tương tự)
        $priceInfant = ($basePrice * $infantBasePercent / 100) * (1 + $infantPercent / 100);

        return [
            'price_adult' => round($priceAdult, 2),
            'price_child' => round($priceChild, 2),
            'price_infant' => round($priceInfant, 2)
        ];
    }

    /**
     * Lấy giá cho 1 loại khách cụ thể
     * 
     * @param int $tourId
     * @param int|null $versionId
     * @param string $passengerType ('adult', 'child', 'infant')
     * @return float
     */
    public function getPriceByType($tourId, $versionId, $passengerType)
    {
        $prices = $this->getPriceForBooking($tourId, $versionId);

        switch ($passengerType) {
            case 'adult':
                return $prices['price_adult'] ?? 0;
            case 'child':
                return $prices['price_child'] ?? 0;
            case 'infant':
                return $prices['price_infant'] ?? 0;
            default:
                return 0;
        }
    }
}
