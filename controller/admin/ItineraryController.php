<?php
require_once 'models/Itinerary.php';

class ItineraryController
{
    protected $model;

    public function __construct()
    {
        $this->model = new Itinerary();
    }

    public function index()
    {
        $tour_id = $_GET['tour_id'] ?? null;
        if (!$tour_id) {
            $_SESSION['error'] = 'Thiếu tour_id';
            header('Location: ?action=tours');
            return;
        }

        $items = $this->model->select('*', 'tour_id = :tour_id', ['tour_id' => $tour_id]);
        $title = 'Lịch trình Tour';
        require_once PATH_VIEW_ADMIN . 'pages/tours/itineraries.php';
    }

    public function create()
    {
        $tour_id = $_GET['tour_id'] ?? null;
        if (!$tour_id) {
            $_SESSION['error'] = 'Thiếu tour_id';
            header('Location: ?action=tours');
            return;
        }

        $title = 'Thêm lịch trình';
        require_once PATH_VIEW_ADMIN . 'pages/tours/itineraries_create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $tour_id = $_POST['tour_id'] ?? null;
        $day_number = $_POST['day_number'] ?? 1;
        $title = $_POST['title'] ?? '';
        $activities = $_POST['activities'] ?? '';
        $image_url = null;

        if (!$tour_id || !$title) {
            $_SESSION['error'] = 'Thiếu thông tin bắt buộc';
            header('Location: ?action=tours/itineraries&tour_id=' . $tour_id);
            return;
        }

        // Handle optional image upload
        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = PATH_ROOT . 'assets/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $tmp = $_FILES['image']['tmp_name'];
            $origName = $_FILES['image']['name'];
            $ext = pathinfo($origName, PATHINFO_EXTENSION);
            $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = $uploadDir . $newName;
            if (move_uploaded_file($tmp, $dest)) {
                $image_url = BASE_ASSETS_UPLOADS . $newName;
            }
        }

        try {
            $this->model->insert([
                'tour_id' => $tour_id,
                'day_number' => $day_number,
                'title' => $title,
                'activities' => $activities,
                'image_url' => $image_url
            ]);

            $_SESSION['success'] = 'Thêm lịch trình thành công';
            header('Location: ?action=tours/itineraries&tour_id=' . $tour_id);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
            header('Location: ?action=tours/itineraries&tour_id=' . $tour_id);
        }
    }

    public function delete()
    {
        $id = $_GET['id'] ?? null;
        $tour_id = $_GET['tour_id'] ?? null;
        if (!$id) {
            header('Location: ?action=tours');
            return;
        }

        try {
            $this->model->delete('id = :id', ['id' => $id]);
            $_SESSION['success'] = 'Xóa thành công';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }

        header('Location: ?action=tours/itineraries&tour_id=' . $tour_id);
    }
}
