<?php
class ClientTourController
{
    protected $model;

    public function __construct()
    {
        $this->model = new Tour();
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
            SELECT t.*, tc.name as category_name, s.name as supplier_name
            FROM tours t 
            LEFT JOIN tour_categories tc ON t.category_id = tc.id 
            LEFT JOIN suppliers s ON t.supplier_id = s.id
            WHERE t.id = :id
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

        // Check for success message from booking
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['success']);

        require_once PATH_VIEW_CLIENT . 'pages/tours/detail.php';
    }
}
