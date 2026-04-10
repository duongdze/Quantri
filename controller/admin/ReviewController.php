<?php
require_once 'models/Review.php';
require_once 'models/Tour.php';

class ReviewController
{
    private $model;

    public function __construct()
    {
        $this->model = new Review();
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            $_SESSION['error'] = 'Không có quyền truy cập.';
            header('Location: ' . BASE_URL_ADMIN . '&action=/');
            exit;
        }
    }

    public function index()
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = [];
        if (!empty($_GET['status']))  $filters['status']  = $_GET['status'];
        if (!empty($_GET['tour_id'])) $filters['tour_id'] = (int)$_GET['tour_id'];
        if (!empty($_GET['rating']))  $filters['rating']  = (int)$_GET['rating'];

        $result     = $this->model->getAllForAdmin($page, 20, $filters);
        $reviews    = $result['data'];
        $pagination = $result;

        // Lấy danh sách tour để filter
        $tourModel = new Tour();
        $tours     = $tourModel->select('id, name', 'deleted_at IS NULL', [], 'name ASC');

        require_once PATH_VIEW_ADMIN . 'pages/reviews/index.php';
    }

    /**
     * AJAX: Cập nhật trạng thái review (approve/reject)
     */
    public function updateStatus()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $id     = (int)($_POST['id']     ?? 0);
        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['pending', 'approved', 'rejected'])) {
            echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
            return;
        }

        $result = $this->model->updateStatus($id, $status);
        echo json_encode(['success' => (bool)$result]);
    }

    /**
     * Xóa hẳn review
     */
    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete('id = :id', ['id' => $id]);
        $_SESSION['success'] = 'Đã xóa đánh giá.';
        header('Location: ' . BASE_URL_ADMIN . '&action=reviews');
        exit;
    }
}
