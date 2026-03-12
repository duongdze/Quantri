<?php
require_once 'models/BaseModel.php';

class Tour extends BaseModel
{
    protected $table = 'tours';
    protected $columns = [
        'id',
        'name',
        'category_id',
        'supplier_id',
        'description',
        'base_price',
        'status',
        'featured',
        'duration_days',
        'max_participants',
        'min_participants',
        'difficulty_level',
        'start_location',
        'end_location',
        'created_at',
        'updated_at'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lấy danh sách tour có doanh thu cao nhất
     * @param int $limit Số lượng tour cần lấy
     * @return array
     */
    public function getTopToursByRevenue($limit = 5)
    {
        $sql = "SELECT 
                    t.id,
                    t.name,
                    t.base_price,
                    COUNT(b.id) as total_bookings,
                    COALESCE(SUM(b.final_price), 0) as total_revenue
                FROM {$this->table} t
                LEFT JOIN bookings b ON t.id = b.tour_id
                WHERE (b.status = 'paid' OR b.status = 'completed' OR b.status IS NULL)
                GROUP BY t.id, t.name, t.base_price
                HAVING total_revenue > 0
                ORDER BY total_revenue DESC
                LIMIT :limit";

        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thống kê số lượng tour theo danh mục
     * @return array
     */
    public function getTourCategoriesStats()
    {
        $sql = "SELECT 
                    tc.id,
                    tc.name as category_name,
                    COUNT(t.id) as tour_count
                FROM tour_categories tc
                LEFT JOIN {$this->table} t ON tc.id = t.category_id
                GROUP BY tc.id, tc.name
                ORDER BY tour_count DESC";

        $stmt = self::$pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách tour sắp khởi hành
     * @param int $days Số ngày tới để xác định tour sắp khởi hành
     * @param int $limit Số lượng tour cần lấy
     * @return array
     */
    public function getUpcomingTours($days = 30, $limit = 5)
    {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+{$days} days"));

        $sql = "SELECT 
                    t.id,
                    t.name,
                    td.departure_date,
                    td.max_seats,
                    td.booked_seats,
                    (td.max_seats - td.booked_seats) as available_seats,
                    td.price_adult as price
                FROM {$this->table} t
                JOIN tour_departures td ON t.id = td.tour_id
                WHERE td.departure_date BETWEEN :start_date AND :end_date
                AND td.status = 'open'
                AND td.max_seats > td.booked_seats
                ORDER BY td.departure_date ASC
                LIMIT :limit";

        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':start_date', $startDate);
        $stmt->bindValue(':end_date', $endDate);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Return all tours with pagination, filtering and advanced data
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return array
     */
    public function getAllTours($page = 1, $perPage = 10, $filters = [])
    {
        $page = max(1, (int)$page);
        $perPage = max(5, min(50, (int)$perPage));
        $offset = ($page - 1) * $perPage;

        // Build WHERE conditions
        $whereConditions = [];
        $params = [];

        // Keyword search
        if (!empty($filters['keyword'])) {
            $whereConditions[] = "(t.name LIKE :keyword OR t.description LIKE :keyword)";
            $params[':keyword'] = '%' . $filters['keyword'] . '%';
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $whereConditions[] = "t.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }



        // Date range filter
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(t.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(t.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        // Price range filter
        if (!empty($filters['price_min'])) {
            $whereConditions[] = "t.base_price >= :price_min";
            $params[':price_min'] = $filters['price_min'];
        }
        if (!empty($filters['price_max'])) {
            $whereConditions[] = "t.base_price <= :price_max";
            $params[':price_max'] = $filters['price_max'];
        }

        // Rating filter
        if (!empty($filters['rating_min'])) {
            $whereConditions[] = "COALESCE(avg_rating, 0) >= :rating_min";
            $params[':rating_min'] = $filters['rating_min'];
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        // Build ORDER BY - Active first, then featured, then by selected sort, inactive last
        $orderBy = "CASE WHEN t.status = 'active' THEN 0 ELSE 1 END, t.featured DESC, t.created_at DESC";
        if (!empty($filters['sort_by'])) {
            $sortBy = $filters['sort_by'];
            $sortDir = strtoupper($filters['sort_dir'] ?? 'DESC');
            // Sanitize sort direction to avoid SQL injection risk
            $sortDir = in_array($sortDir, ['ASC', 'DESC']) ? $sortDir : 'DESC';

            switch ($sortBy) {
                case 'name':
                    $orderBy = "CASE WHEN t.status = 'active' THEN 0 ELSE 1 END, t.featured DESC, t.name $sortDir";
                    break;
                case 'price':
                    $orderBy = "CASE WHEN t.status = 'active' THEN 0 ELSE 1 END, t.featured DESC, t.base_price $sortDir";
                    break;
                case 'rating':
                    $orderBy = "CASE WHEN t.status = 'active' THEN 0 ELSE 1 END, t.featured DESC, COALESCE(avg_rating, 0) $sortDir";
                    break;
                case 'created_at':
                default:
                    $orderBy = "CASE WHEN t.status = 'active' THEN 0 ELSE 1 END, t.featured DESC, t.created_at $sortDir";
                    break;
            }
        }

        // Count query
        $countSql = "SELECT COUNT(DISTINCT t.id) FROM {$this->table} AS t
                     LEFT JOIN `tour_categories` AS tc ON t.category_id = tc.id
                     LEFT JOIN (
                         SELECT tour_id, AVG(rating) as avg_rating
                         FROM tour_feedbacks
                         GROUP BY tour_id
                     ) tf ON t.id = tf.tour_id
                     $whereClause";

        $countStmt = self::$pdo->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        // Main query with complex joins
        $sql = "SELECT
                    t.*,
                    t.*,
                    tc.name as category_name,
                    COALESCE(tf.avg_rating, 0) as avg_rating,
                    COALESCE(tb.booking_count, 0) as booking_count,
                    MAX(CASE WHEN tgi.main_img = 1 THEN tgi.image_url END) AS main_image,
                    GROUP_CONCAT(tgi.image_url ORDER BY tgi.sort_order SEPARATOR ',') as gallery_images,
                    0 as availability_percentage
                FROM {$this->table} AS t
                LEFT JOIN `tour_categories` AS tc ON t.category_id = tc.id
                LEFT JOIN (
                    SELECT tour_id, AVG(rating) as avg_rating
                    FROM tour_feedbacks
                    GROUP BY tour_id
                ) tf ON t.id = tf.tour_id
                LEFT JOIN (
                    SELECT tour_id, COUNT(*) as booking_count
                    FROM bookings
                    GROUP BY tour_id
                ) tb ON t.id = tb.tour_id
                LEFT JOIN `tour_gallery_images` tgi ON t.id = tgi.tour_id
                $whereClause
                GROUP BY t.id, tc.name, tf.avg_rating, tb.booking_count
                ORDER BY $orderBy
                LIMIT :limit OFFSET :offset";

        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($total / $perPage),
            'filters' => $filters,
        ];
    }



    public function findById($id)
    {
        $tour = $this->find('*', 'id = :id', ['id' => $id]);
        if (!$tour) {
            return null;
        }

        return $tour;
    }
    public function getRelatedData(string $tableName, int $tourId): array
    {
        $sql = "SELECT * FROM {$tableName} WHERE tour_id = :tour_id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['tour_id' => $tourId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createTour($tourData, $pricingOptions = [], $dynamicPricing = [], $itineraries = [], $partners = [], $uploadedImages = [], $policyIds = [], $versions = [])
    {
        $this->beginTransaction(); // BẮT ĐẦU TRANSACTION
        try {
            // 1. INSERT TOUR CƠ BẢN
            $tourId = $this->insert($tourData);

            // 2. INSERT PRICING OPTIONS VÀ DYNAMIC PRICING
            if (!empty($pricingOptions)) {
                $pricingModel = new TourPricing();
                $dynamicPricingModel = new TourDynamicPricing();
                $optionLabelToIdMap = [];

                // Insert options and create a map from label to the new ID
                foreach ($pricingOptions as $option) {
                    $optionLabel = $option['label'] ?? '';
                    if (!empty($optionLabel)) {
                        $optionId = $pricingModel->insert([
                            'tour_id' => $tourId,
                            'label' => $optionLabel,
                            'description' => $option['description'] ?? '',
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                        $optionLabelToIdMap[$optionLabel] = $optionId;
                    }
                }

                // TODO: Dynamic pricing table structure changed to version_dynamic_pricing
                // It now uses version_id/departure_id instead of tour_id/pricing_option_id
                // Temporarily disabled until we update the logic
                /*
                if (!empty($dynamicPricing) && !empty($optionLabelToIdMap)) {
                    foreach ($dynamicPricing as $dp) {
                        $optionLabel = $dp['option_label'] ?? '';
                        if (isset($optionLabelToIdMap[$optionLabel])) {
                            $optionId = $optionLabelToIdMap[$optionLabel];
                            $dynamicPricingModel->insert([
                                'version_id' => ...,
                                'departure_id' => ...,
                                'apply_type' => 'discount',
                                'amount' => ...,
                                'amount_type' => 'cash',
                                'start_date' => $dp['start_date'] ?: null,
                                'end_date' => $dp['end_date'] ?: null,
                                'notes' => $dp['notes'] ?? '',
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                }
                */
            }


            // 3. INSERT ITINERARIES
            if (!empty($itineraries)) {
                $itineraryModel = new TourItinerary();
                foreach ($itineraries as $index => $item) {
                    // Tính day_number từ day_label (VD: "Ngày 1" → 1)
                    $dayNumber = $index + 1;
                    if (preg_match('/Ngày\s+(\d+)/i', $item['day'], $matches)) {
                        $dayNumber = (int)$matches[1];
                    }

                    $itineraryModel->insert([
                        'tour_id' => $tourId,
                        'day_label' => $item['day'] ?? '',
                        'day_number' => $dayNumber,
                        'time_start' => $item['time_start'] ?? null,
                        'time_end' => $item['time_end'] ?? null,
                        'title' => $item['title'] ?? '',
                        'description' => $item['description'] ?? '',
                        'activities' => '', // Có thể mở rộng sau
                        'image_url' => '', // Có thể mở rộng sau
                    ]);
                }
            }

            // 4. INSERT IMAGES
            if (!empty($uploadedImages)) {
                $imageModel = new TourImage();
                foreach ($uploadedImages as $index => $image) {
                    $imageModel->insert([
                        'tour_id' => $tourId,
                        'main_img' => $image['is_main'] ? 1 : 0,
                        'image_url' => $image['path'],
                        'caption' => '',
                        'sort_order' => $index + 1,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            // 5. INSERT PARTNERS
            if (!empty($partners)) {
                $partnerModel = new TourPartner();
                foreach ($partners as $partner) {
                    $partnerModel->insert([
                        'tour_id' => $tourId,
                        'service_type' => $partner['service_type'] ?? 'other',
                        'partner_name' => $partner['name'] ?? '',
                        'contact' => $partner['contact'] ?? '',
                        'notes' => $partner['notes'] ?? '',
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            // 6. INSERT POLICIES
            if (!empty($policyIds)) {
                $policyAssignmentModel = new TourPolicyAssignment();
                foreach ($policyIds as $policyId) {
                    $policyAssignmentModel->insert([
                        'tour_id' => $tourId,
                        'policy_id' => $policyId,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            // 7. INSERT VERSIONS
            if (!empty($versions) && is_array($versions)) {
                $versionModel = new TourVersion();
                foreach ($versions as $v) {
                    if (!empty($v['name'])) {
                        $versionModel->insert([
                            'tour_id' => $tourId,
                            'name' => $v['name'],
                            'start_date' => !empty($v['start_date']) ? $v['start_date'] : null,
                            'end_date' => !empty($v['end_date']) ? $v['end_date'] : null,
                            'price' => (float)($v['price'] ?? 0),
                            'notes' => $v['notes'] ?? '',
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }

            $this->commit(); // COMMIT TRANSACTION
            return $tourId;
        } catch (Exception $e) {
            $this->rollBack(); // ROLLBACK NẾU CÓ LỖI
            throw $e;
        }
    }


    public function deleteById($id)
    {
        return $this->delete('id = :id', ['id' => $id]);
    }

    /**
     * Remove a tour and all related data and files safely inside a transaction.
     * Returns true on success, false otherwise.
     */
    public function removeTour($id)
    {
        $this->beginTransaction();
        try {
            // Load related models
            $imageModel = new TourImage();
            $pricingModel = new TourPricing();
            $dynamicPricingModel = new TourDynamicPricing();
            $itineraryModel = new TourItinerary();
            $partnerModel = new TourPartner();
            $versionModel = new TourVersion();
            $policyAssignmentModel = new TourPolicyAssignment();
            $bookingModel = new Booking();
            $bookingCustomerModel = new BookingCustomer();

            // 1. Delete image files from disk
            $images = $imageModel->getByTourId($id);
            foreach ($images as $img) {
                $path = PATH_ASSETS_UPLOADS . ($img['image_url'] ?? '');
                if (!empty($img['image_url']) && file_exists($path)) {
                    @unlink($path);
                }
            }

            // 2. Delete DB records in dependent tables
            $imageModel->deleteByTourId($id);
            $pricingModel->delete('tour_id = :tour_id', ['tour_id' => $id]);
            // TODO: version_dynamic_pricing table doesn't have tour_id column
            // $dynamicPricingModel->delete('tour_id = :tour_id', ['tour_id' => $id]);
            $itineraryModel->delete('tour_id = :tour_id', ['tour_id' => $id]);
            $partnerModel->delete('tour_id = :tour_id', ['tour_id' => $id]);
            // Note: tour_versions table doesn't have tour_id column, so we don't delete versions here
            // Versions should be deleted separately if needed via their own logic
            $policyAssignmentModel->delete('tour_id = :tour_id', ['tour_id' => $id]);


            // 3. Delete bookings and their customers
            $bookings = $bookingModel->select('*', 'tour_id = :tour_id', ['tour_id' => $id]);
            foreach ($bookings as $b) {
                $bookingCustomerModel->deleteByBooking($b['id']);
                $bookingModel->delete('id = :id', ['id' => $b['id']]);
            }

            // 4. Finally delete the tour record itself
            $this->delete('id = :id', ['id' => $id]);

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    public function getOngoingTours()
    {
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(DISTINCT t.id) as count 
                FROM {$this->table} t
                INNER JOIN tour_departures td ON t.id = td.tour_id
                WHERE td.departure_date >= :today 
                AND td.status IN ('open', 'guaranteed')";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['today' => $today]);
        $data = $stmt->fetch();
        return $data['count'] ?? 0;
    }

    /**
     * Get active tours with optimized query
     */
    public function getActiveTours($limit = 10)
    {
        $sql = "SELECT t.*, tc.name as category_name,
                COALESCE(tf.avg_rating, 0) as avg_rating,
                COALESCE(tb.booking_count, 0) as booking_count,
                MAX(CASE WHEN tgi.main_img = 1 THEN tgi.image_url END) AS main_image
                FROM {$this->table} t
                LEFT JOIN tour_categories tc ON t.category_id = tc.id
                LEFT JOIN (
                    SELECT tour_id, AVG(rating) as avg_rating
                    FROM tour_feedbacks
                    GROUP BY tour_id
                ) tf ON t.id = tf.tour_id
                LEFT JOIN (
                    SELECT tour_id, COUNT(*) as booking_count
                    FROM bookings
                    GROUP BY tour_id
                ) tb ON t.id = tb.tour_id
                LEFT JOIN tour_gallery_images tgi ON t.id = tgi.tour_id
                GROUP BY t.id, tc.name, tf.avg_rating, tb.booking_count
                ORDER BY t.created_at DESC
                LIMIT :limit";

        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Toggle tour status
     */
    public function toggleStatus($id)
    {
        $tour = $this->findById($id);
        if (!$tour) {
            return false;
        }

        // Toggle status between 'active' and 'inactive'
        $currentStatus = $tour['status'] ?? 'active';
        $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';

        $result = $this->update(
            ['status' => $newStatus],
            'id = :id',
            ['id' => $id]
        );

        return $result > 0;
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured($id)
    {
        $tour = $this->findById($id);
        if (!$tour) {
            return false;
        }

        // Toggle featured between 0 and 1
        $currentFeatured = $tour['featured'] ?? 0;
        $newFeatured = $currentFeatured ? 0 : 1;

        $result = $this->update(
            ['featured' => $newFeatured],
            'id = :id',
            ['id' => $id]
        );

        return $result > 0;
    }

    /**
     * Get tour statistics
     */
    public function getStatistics()
    {
        $stats = [];

        // Total tours
        $stats['total'] = $this->count();

        // Active tours
        $stats['active'] = $this->count("status = 'active'");

        // Featured tours
        $stats['featured'] = $this->count("featured = 1");

        // Ongoing tours
        $stats['ongoing'] = $this->getOngoingTours();

        // By category
        $sql = "SELECT tc.name, COUNT(t.id) as count 
                FROM tour_categories tc 
                LEFT JOIN tours t ON tc.id = t.category_id
                GROUP BY tc.id, tc.name 
                ORDER BY count DESC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();
        $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // By difficulty (since difficulty_level column doesn't exist, return empty array)
        $stats['by_difficulty'] = [];

        return $stats;
    }

    /**
     * Bulk update tour status
     */
    public function bulkUpdateStatus($ids, $status)
    {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE {$this->table} SET status = ? WHERE id IN ($placeholders)";

        $stmt = self::$pdo->prepare($sql);
        $params = array_merge([$status], $ids);

        return $stmt->execute($params);
    }

    /**
     * Bulk delete tours
     */
    public function bulkDelete($ids)
    {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }

        $this->beginTransaction();
        try {
            foreach ($ids as $id) {
                $this->removeTour($id);
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Search tours with advanced filters
     */
    public function searchTours($keyword, $filters = [], $limit = 20)
    {
        $whereConditions = ["(t.name LIKE :keyword OR t.description LIKE :keyword)"];
        $params = ['keyword' => '%' . $keyword . '%'];

        // Add filters
        if (!empty($filters['category_id'])) {
            $whereConditions[] = "t.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['status'])) {
            // Since status column doesn't exist in database, skip this filter
            // $whereConditions[] = "t.status = :status";
            // $params['status'] = $filters['status'];
        }

        if (!empty($filters['difficulty_level'])) {
            // Since difficulty_level column doesn't exist, skip this filter
            // $whereConditions[] = "t.difficulty_level = :difficulty_level";
            // $params['difficulty_level'] = $filters['difficulty_level'];
        }

        if (!empty($filters['price_min'])) {
            $whereConditions[] = "t.base_price >= :price_min";
            $params['price_min'] = $filters['price_min'];
        }

        if (!empty($filters['price_max'])) {
            $whereConditions[] = "t.base_price <= :price_max";
            $params['price_max'] = $filters['price_max'];
        }

        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

        $sql = "SELECT t.*, tc.name as category_name,
                COALESCE(tf.avg_rating, 0) as avg_rating,
                COALESCE(tb.booking_count, 0) as booking_count,
                MAX(CASE WHEN tgi.main_img = 1 THEN tgi.image_url END) AS main_image
                FROM {$this->table} t
                LEFT JOIN tour_categories tc ON t.category_id = tc.id
                LEFT JOIN (
                    SELECT tour_id, AVG(rating) as avg_rating
                    FROM tour_feedbacks
                    GROUP BY tour_id
                ) tf ON t.id = tf.tour_id
                LEFT JOIN (
                    SELECT tour_id, COUNT(*) as booking_count
                    FROM bookings
                    GROUP BY tour_id
                ) tb ON t.id = tb.tour_id
                LEFT JOIN tour_gallery_images tgi ON t.id = tgi.tour_id
                {$whereClause}
                GROUP BY t.id, tc.name, tf.avg_rating, tb.booking_count
                ORDER BY t.created_at DESC, t.name ASC
                LIMIT :limit";

        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get tours by status
     */
    public function getByStatus($status, $limit = null)
    {
        $sql = "SELECT t.*, tc.name as category_name,
                COALESCE(tf.avg_rating, 0) as avg_rating,
                COALESCE(tb.booking_count, 0) as booking_count,
                MAX(CASE WHEN tgi.main_img = 1 THEN tgi.image_url END) AS main_image
                FROM {$this->table} t
                LEFT JOIN tour_categories tc ON t.category_id = tc.id
                LEFT JOIN (
                    SELECT tour_id, AVG(rating) as avg_rating
                    FROM tour_feedbacks
                    GROUP BY tour_id
                ) tf ON t.id = tf.tour_id
                LEFT JOIN (
                    SELECT tour_id, COUNT(*) as booking_count
                    FROM bookings
                    GROUP BY tour_id
                ) tb ON t.id = tb.tour_id
                LEFT JOIN tour_gallery_images tgi ON t.id = tgi.tour_id
                GROUP BY t.id, tc.name, tf.avg_rating, tb.booking_count
                ORDER BY t.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT :limit";
        }

        $stmt = self::$pdo->prepare($sql);
        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
