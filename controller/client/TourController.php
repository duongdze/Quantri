<?php
require_once 'models/TourCategory.php';
require_once 'models/Review.php';

class ClientTourController
{
    protected $model;

    public function __construct()
    {
        $this->model = new Tour();
    }

    /**
     * Trang chủ – listing tours với filter & pagination
     */
    public function index()
    {
        $filters = [
            'keyword'     => trim($_GET['keyword']     ?? ''),
            'category_id' => (int)($_GET['category_id'] ?? 0) ?: null,
            'price_min'   => (float)($_GET['price_min']  ?? 0) ?: null,
            'price_max'   => (float)($_GET['price_max']  ?? 0) ?: null,
            'sort_by'     => $_GET['sort_by']  ?? 'created_at',
            'sort_dir'    => $_GET['sort_dir'] ?? 'DESC',
        ];

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 9;

        $result = $this->model->getAllTours($page, $perPage, array_filter($filters, fn($v) => $v !== null && $v !== ''));

        $tours       = $result['data'];
        $totalPages  = $result['total_pages'];
        $total       = $result['total'];

        // Lấy danh mục để hiển thị filter
        $categoryModel = new TourCategory();
        $categories    = $categoryModel->getAllCategories();

        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['success']);

        require_once PATH_VIEW_CLIENT . 'pages/tours/index.php';
    }

    public function detail()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL);
            exit;
        }

        // Fetch tour details using the same logic as Admin but for display
        $pdo = BaseModel::getPdo();
        $stmt = $pdo->prepare("
            SELECT t.*, tc.name as category_name, s.name as supplier_name,
                   COALESCE(tf.avg_rating, 0) as avg_rating,
                   COALESCE(tf.review_count, 0) as review_count
            FROM tours t 
            LEFT JOIN tour_categories tc ON t.category_id = tc.id 
            LEFT JOIN suppliers s ON t.supplier_id = s.id
            LEFT JOIN (
                SELECT tour_id, AVG(rating) as avg_rating, COUNT(*) as review_count
                FROM tour_feedbacks
                GROUP BY tour_id
            ) tf ON t.id = tf.tour_id
            WHERE t.id = :id AND t.deleted_at IS NULL
        ");
        $stmt->execute(['id' => $id]);
        $tour = $stmt->fetch();

        if (!$tour) {
            // Tour not found or not active
            header('Location: ' . BASE_URL);
            exit;
        }

        // Fetch related data
        $pricingModel = new TourPricing();
        $pricingOptions = $pricingModel->getByTourId($id);

        $itineraryModel = new TourItinerary();
        $itinerarySchedule = $itineraryModel->select('*', 'tour_id = :tid', ['tid' => $id], 'day_number ASC');

        $imageModel = new TourImage();
        $images = $imageModel->getByTourId($id);

        $policyAssignmentModel = new TourPolicyAssignment();
        $assignedPolicies = $policyAssignmentModel->getByTourId($id);
        
        $policyModel = new TourPolicy();
        $policies = [];
        foreach ($assignedPolicies as $ap) {
            $p = $policyModel->findById($ap['policy_id']);
            if ($p) {
                $policies[] = $p;
            }
        }

        // Departures
        $departureModel = new TourDeparture();
        $departures = $departureModel->select('*', 'tour_id = :tid', ['tid' => $id], 'departure_date ASC');

        if (empty($tour['duration_days'])) {
            $tour['duration_days'] = count($itinerarySchedule) ?: 'N/A';
        }

        // Reviews từ bảng tour_reviews (chỉ approved)
        $reviewModel  = new Review();
        $reviews      = $reviewModel->getApprovedByTour($id);
        $ratingSummary = $reviewModel->getRatingSummary($id);

        // Kiểm tra user hiện tại có thể review không
        $canReview        = false;
        $alreadyReviewed  = false;
        if (!empty($_SESSION['user'])) {
            $uid = (int)$_SESSION['user']['user_id'];
            $alreadyReviewed = $reviewModel->hasReviewed($id, $uid);
            $canReview       = !$alreadyReviewed && $reviewModel->hasBookedTour($id, $uid);
        }

        // SEO
        $pageTitle = $tour['name'] . ' – VietTour';
        $metaDescription = mb_substr(strip_tags($tour['description']), 0, 160) . '...';
        $ogImage = !empty($images) ? (BASE_ASSETS_UPLOADS . $images[0]['image_url']) : null;

        // Check for success message from booking
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['success']);

        require_once PATH_VIEW_CLIENT . 'pages/tours/detail.php';
    }
}
