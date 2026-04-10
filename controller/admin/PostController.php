<?php
require_once 'models/Post.php';

class PostController
{
    private $model;

    public function __construct()
    {
        $this->model = new Post();
        // Chỉ admin mới truy cập
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            $_SESSION['error'] = 'Không có quyền truy cập.';
            header('Location: ' . BASE_URL_ADMIN . '&action=/');
            exit;
        }
    }

    public function index()
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $filters = [];
        if (!empty($_GET['keyword'])) $filters['keyword'] = trim($_GET['keyword']);
        if (!empty($_GET['status']))  $filters['status']  = $_GET['status'];

        $result     = $this->model->getAll($page, $perPage, $filters);
        $posts      = $result['data'];
        $pagination = $result;

        require_once PATH_VIEW_ADMIN . 'pages/posts/index.php';
    }

    public function create()
    {
        require_once PATH_VIEW_ADMIN . 'pages/posts/form.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL_ADMIN . '&action=posts');
            exit;
        }

        $title   = trim($_POST['title']   ?? '');
        $content = trim($_POST['content'] ?? '');
        $status  = $_POST['status']  ?? 'draft';

        if (empty($title) || empty($content)) {
            $_SESSION['error'] = 'Tiêu đề và nội dung không được để trống.';
            header('Location: ' . BASE_URL_ADMIN . '&action=posts/create');
            exit;
        }

        // Generate slug
        $slug = Post::makeSlug($title);
        $origSlug = $slug;
        $i = 1;
        while ($this->model->slugExists($slug)) {
            $slug = $origSlug . '-' . $i++;
        }

        // Handle thumbnail upload
        $thumbnail = '';
        if (!empty($_FILES['thumbnail']['tmp_name'])) {
            $uploadDir = PATH_ASSETS_UPLOADS . 'posts/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext       = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
            $newName   = 'post_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadDir . $newName)) {
                $thumbnail = 'posts/' . $newName;
            }
        }

        $data = [
            'title'        => $title,
            'slug'         => $slug,
            'excerpt'      => trim($_POST['excerpt'] ?? ''),
            'content'      => $content,
            'thumbnail'    => $thumbnail,
            'author_id'    => $_SESSION['user']['user_id'],
            'status'       => $status,
            'featured'     => !empty($_POST['featured']) ? 1 : 0,
            'published_at' => ($status === 'published') ? date('Y-m-d H:i:s') : null,
        ];

        $this->model->insert($data);
        $_SESSION['success'] = 'Đăng bài viết thành công!';
        header('Location: ' . BASE_URL_ADMIN . '&action=posts');
        exit;
    }

    public function edit()
    {
        $id   = (int)($_GET['id'] ?? 0);
        $post = $this->model->getById($id);
        if (!$post) {
            $_SESSION['error'] = 'Không tìm thấy bài viết.';
            header('Location: ' . BASE_URL_ADMIN . '&action=posts');
            exit;
        }
        require_once PATH_VIEW_ADMIN . 'pages/posts/form.php';
    }

    public function update()
    {
        $id   = (int)($_POST['id'] ?? 0);
        $post = $this->model->getById($id);
        if (!$post) {
            $_SESSION['error'] = 'Không tìm thấy bài viết.';
            header('Location: ' . BASE_URL_ADMIN . '&action=posts');
            exit;
        }

        $title   = trim($_POST['title']   ?? '');
        $content = trim($_POST['content'] ?? '');
        $status  = $_POST['status'] ?? 'draft';

        if (empty($title) || empty($content)) {
            $_SESSION['error'] = 'Tiêu đề và nội dung không được để trống.';
            header('Location: ' . BASE_URL_ADMIN . '&action=posts/edit&id=' . $id);
            exit;
        }

        // Slug
        $slug = Post::makeSlug($title);
        if ($slug !== $post['slug']) {
            $orig = $slug; $i = 1;
            while ($this->model->slugExists($slug, $id)) {
                $slug = $orig . '-' . $i++;
            }
        }

        // Thumbnail update
        $thumbnail = $post['thumbnail'];
        if (!empty($_FILES['thumbnail']['tmp_name'])) {
            $uploadDir = PATH_ASSETS_UPLOADS . 'posts/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            // xóa ảnh cũ
            if (!empty($thumbnail) && file_exists(PATH_ASSETS_UPLOADS . $thumbnail)) {
                @unlink(PATH_ASSETS_UPLOADS . $thumbnail);
            }
            $ext     = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
            $newName = 'post_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadDir . $newName)) {
                $thumbnail = 'posts/' . $newName;
            }
        }

        $wasPublished = $post['status'] === 'published';
        $nowPublished = $status === 'published';

        $data = [
            'title'        => $title,
            'slug'         => $slug,
            'excerpt'      => trim($_POST['excerpt'] ?? ''),
            'content'      => $content,
            'thumbnail'    => $thumbnail,
            'status'       => $status,
            'featured'     => !empty($_POST['featured']) ? 1 : 0,
            'updated_at'   => date('Y-m-d H:i:s'),
        ];
        // Đặt published_at lần đầu được publish
        if ($nowPublished && !$wasPublished) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        $this->model->update($data, 'id = :id', ['id' => $id]);
        $_SESSION['success'] = 'Cập nhật bài viết thành công!';
        header('Location: ' . BASE_URL_ADMIN . '&action=posts');
        exit;
    }

    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->softDelete($id);
        $_SESSION['success'] = 'Đã xóa bài viết.';
        header('Location: ' . BASE_URL_ADMIN . '&action=posts');
        exit;
    }
}
