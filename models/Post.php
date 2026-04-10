<?php
require_once 'models/BaseModel.php';

class Post extends BaseModel
{
    protected $table = 'posts';

    /**
     * Lấy danh sách bài viết với phân trang & lọc (admin)
     */
    public function getAll($page = 1, $perPage = 15, $filters = [])
    {
        $page    = max(1, (int)$page);
        $perPage = max(5, min(50, (int)$perPage));
        $offset  = ($page - 1) * $perPage;

        $where  = ['p.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['keyword'])) {
            $where[] = '(p.title LIKE :kw OR p.excerpt LIKE :kw)';
            $params[':kw'] = '%' . $filters['keyword'] . '%';
        }
        if (!empty($filters['status'])) {
            $where[] = 'p.status = :status';
            $params[':status'] = $filters['status'];
        }

        $whereStr = 'WHERE ' . implode(' AND ', $where);

        $count = self::$pdo->prepare("SELECT COUNT(*) FROM posts p $whereStr");
        $count->execute($params);
        $total = (int)$count->fetchColumn();

        $sql = "SELECT p.*, u.full_name as author_name
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.user_id
                $whereStr
                ORDER BY p.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();

        return [
            'data'        => $stmt->fetchAll(),
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int)ceil($total / $perPage),
        ];
    }

    /**
     * Lấy danh sách đã xuất bản cho frontend
     */
    public function getPublished($page = 1, $perPage = 9, $keyword = '')
    {
        $page    = max(1, (int)$page);
        $perPage = max(3, min(30, (int)$perPage));
        $offset  = ($page - 1) * $perPage;

        $where  = ["p.status = 'published'", 'p.deleted_at IS NULL'];
        $params = [];

        if (!empty($keyword)) {
            $where[] = '(p.title LIKE :kw OR p.excerpt LIKE :kw)';
            $params[':kw'] = '%' . $keyword . '%';
        }

        $whereStr = 'WHERE ' . implode(' AND ', $where);

        $count = self::$pdo->prepare("SELECT COUNT(*) FROM posts p $whereStr");
        $count->execute($params);
        $total = (int)$count->fetchColumn();

        $sql = "SELECT p.*, u.full_name as author_name
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.user_id
                $whereStr
                ORDER BY p.published_at DESC, p.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();

        return [
            'data'        => $stmt->fetchAll(),
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int)ceil($total / $perPage),
        ];
    }

    /**
     * Lấy chi tiết bài viết theo slug (frontend)
     */
    public function getBySlug($slug)
    {
        $sql = "SELECT p.*, u.full_name as author_name
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.user_id
                WHERE p.slug = :slug AND p.status = 'published' AND p.deleted_at IS NULL
                LIMIT 1";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch();
    }

    /**
     * Lấy theo ID (admin)
     */
    public function getById($id)
    {
        $sql = "SELECT p.*, u.full_name as author_name
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.user_id
                WHERE p.id = :id AND p.deleted_at IS NULL
                LIMIT 1";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Tăng lượt xem
     */
    public function incrementViews($id)
    {
        $sql = "UPDATE posts SET views = views + 1 WHERE id = :id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    /**
     * Tạo slug từ title
     */
    public static function makeSlug($title)
    {
        $slug = mb_strtolower($title, 'UTF-8');
        $map  = [
            'à'=>'a','á'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a',
            'ă'=>'a','ắ'=>'a','ặ'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a',
            'â'=>'a','ấ'=>'a','ầ'=>'a','ẩ'=>'a','ẫ'=>'a','ậ'=>'a',
            'è'=>'e','é'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e',
            'ê'=>'e','ế'=>'e','ề'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e',
            'ì'=>'i','í'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i',
            'ò'=>'o','ó'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o',
            'ô'=>'o','ố'=>'o','ồ'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o',
            'ơ'=>'o','ớ'=>'o','ờ'=>'o','ở'=>'o','ỡ'=>'o','ợ'=>'o',
            'ù'=>'u','ú'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u',
            'ư'=>'u','ứ'=>'u','ừ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u',
            'ỳ'=>'y','ý'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y',
            'đ'=>'d',
        ];
        $slug = strtr($slug, $map);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Kiểm tra slug có tồn tại không (dùng khi tạo/sửa)
     */
    public function slugExists($slug, $excludeId = null)
    {
        $sql    = "SELECT COUNT(*) FROM posts WHERE slug = :slug AND deleted_at IS NULL";
        $params = [':slug' => $slug];
        if ($excludeId) {
            $sql    .= " AND id != :eid";
            $params[':eid'] = $excludeId;
        }
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Soft delete
     */
    public function softDelete($id)
    {
        return $this->update(
            ['deleted_at' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $id]
        );
    }

    /**
     * Bài viết nổi bật cho homepage
     */
    public function getFeatured($limit = 3)
    {
        $sql = "SELECT p.*, u.full_name as author_name
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.user_id
                WHERE p.status = 'published' AND p.featured = 1 AND p.deleted_at IS NULL
                ORDER BY p.published_at DESC
                LIMIT :limit";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Bài viết liên quan (cùng tác giả hoặc từ khoá)
     */
    public function getRelated($postId, $limit = 3)
    {
        $sql = "SELECT p.*, u.full_name as author_name
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.user_id
                WHERE p.status = 'published' AND p.deleted_at IS NULL AND p.id != :pid
                ORDER BY p.published_at DESC
                LIMIT :limit";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindValue(':pid', $postId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
