<?php
require_once 'models/Review.php';

class ClientReviewController
{
    private $model;

    public function __construct()
    {
        $this->model = new Review();
    }

    /**
     * Xử lý submit đánh giá (POST)
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL);
            exit;
        }

        if (empty($_SESSION['user'])) {
            $_SESSION['error'] = 'Bạn phải đăng nhập để đánh giá.';
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        $userId  = (int)$_SESSION['user']['user_id'];
        $tourId  = (int)($_POST['tour_id'] ?? 0);
        $rating  = (int)($_POST['rating']  ?? 0);
        $comment = trim($_POST['comment']  ?? '');

        if ($tourId <= 0 || $rating < 1 || $rating > 5) {
            $_SESSION['error'] = 'Thông tin đánh giá không hợp lệ.';
            header('Location: ' . BASE_URL . '?action=tour-detail&id=' . $tourId);
            exit;
        }

        // Kiểm tra đã review chưa
        if ($this->model->hasReviewed($tourId, $userId)) {
            $_SESSION['error'] = 'Bạn đã đánh giá tour này rồi.';
            header('Location: ' . BASE_URL . '?action=tour-detail&id=' . $tourId);
            exit;
        }

        // Kiểm tra đã đặt tour chưa
        if (!$this->model->hasBookedTour($tourId, $userId)) {
            $_SESSION['error'] = 'Bạn chỉ có thể đánh giá tour sau khi đã đặt và hoàn thành tour.';
            header('Location: ' . BASE_URL . '?action=tour-detail&id=' . $tourId);
            exit;
        }

        $this->model->insert([
            'tour_id'    => $tourId,
            'user_id'    => $userId,
            'rating'     => $rating,
            'comment'    => $comment,
            'status'     => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $_SESSION['success'] = 'Cảm ơn bạn đã đánh giá! Đánh giá của bạn đang chờ duyệt.';
        header('Location: ' . BASE_URL . '?action=tour-detail&id=' . $tourId . '#reviews');
        exit;
    }
}
