<?php
require_once 'models/Post.php';

class ClientPostController
{
    private $model;

    public function __construct()
    {
        $this->model = new Post();
    }

    /**
     * Trang danh sách tin tức / blog
     */
    public function index()
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $keyword = trim($_GET['keyword'] ?? '');
        $result  = $this->model->getPublished($page, 9, $keyword);
        $posts      = $result['data'];
        $pagination = $result;
        $featured   = $this->model->getFeatured(3);
        $pageTitle  = 'Tin Tức & Blog Du Lịch';

        require_once PATH_VIEW_CLIENT . 'pages/blog/index.php';
    }

    /**
     * Trang chi tiết bài viết
     */
    public function detail()
    {
        $slug = $_GET['slug'] ?? '';
        $post = $this->model->getBySlug($slug);

        if (!$post) {
            http_response_code(404);
            $_SESSION['error'] = 'Bài viết không tồn tại hoặc đã bị xóa.';
            header('Location: ' . BASE_URL . '?action=blog');
            exit;
        }

        // Tăng lượt xem
        $this->model->incrementViews($post['id']);
        $related   = $this->model->getRelated($post['id'], 3);
        $pageTitle = htmlspecialchars($post['title']) . ' | Blog Du Lịch';

        require_once PATH_VIEW_CLIENT . 'pages/blog/detail.php';
    }
}
