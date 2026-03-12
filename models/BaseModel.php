<?php

class BaseModel
{
    protected $table;
    protected static $pdo;

    // Kết nối CSDL
    public function __construct()
    {
        // Chỉ kết nối nếu chưa có kết nối
        if (self::$pdo === null) {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8', DB_HOST, DB_PORT, DB_NAME);

            try {
                self::$pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, DB_OPTIONS);
            } catch (PDOException $e) {
                // Xử lý lỗi kết nối
                die("Kết nối cơ sở dữ liệu thất bại: {$e->getMessage()}. Vui lòng thử lại sau.");
            }
        }
    }

    // Hủy kết nối CSDL
    public function __destruct()
    {
        // Không hủy kết nối ở đây vì nó là static và có thể được sử dụng bởi các đối tượng khác
        // self::$pdo = null;
    }

    public function beginTransaction()
    {
        self::$pdo->beginTransaction();
    }

    public function commit()
    {
        self::$pdo->commit();
    }

    public function rollBack()
    {
        self::$pdo->rollBack();
    }

    public static function getPdo()
    {
        return self::$pdo;
    }

    /**
     * Hàm lấy danh sách
     * 
     * @param string $columns Mặc định lấy tất cả các cột, truyền cột phân cách nhau bằng dấu phẩy
     * @param string $conditions Mệnh đề điều kiện đặt ở đây
     * @param array  $params giá trị của các tham số ảo trong $conditions
     * @param string $orderBy Mệnh đề sắp xếp
     * @param int    $limit Giới hạn số bản ghi
     * @return array
     * 
     * Khi dùng: $obj->select('id, name', 'id > :id', ['id' => 3], 'id DESC', 5)
     */
    public function select($columns = '*', $conditions = null, $params = [], $orderBy = null, $limit = null)
    {
        $sql = "SELECT $columns FROM {$this->table}";

        if ($conditions) {
            $sql .= " WHERE $conditions";
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = self::$pdo->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Hàm đếm bản ghi
     * 
     * @param string $conditions Mệnh đề điều kiện đặt ở đây
     * @param array $params giá trị của các tham số ảo trong $conditions
     * @return int
     * 
     * Khi dùng: $obj->count('id > :id', ['id' => 5])
     */
    public function count($conditions = null, $params = [])
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";

        if ($conditions) {
            $sql .= " WHERE $conditions";
        }

        $stmt = self::$pdo->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    /**
     * Hàm lấy danh sách có phân trang
     * 
     * @param int $page Trang hiện tại
     * @param int $perPage Số bản ghi trên 1 trang
     * @param string $columns Mặc định lấy tất cả các cột, truyền cột phân cách nhau bằng dấu phẩy
     * @param string $conditions Mệnh đề điều kiện đặt ở đây
     * @param array $params giá trị của các tham số ảo trong $conditions
     * @return array
     * 
     * Khi dùng: $obj->paginate(1, 3, 'id, name', 'id > :id AND price > :price', ['id' => 3, 'price' => 36000])
     */
    public function paginate($page = 1, $perPage = 5, $columns = '*', $conditions = null, $params = [])
    {
        $sql = "SELECT $columns FROM {$this->table}";

        if ($conditions) {
            $sql .= " WHERE $conditions";
        }

        $offset = ($page - 1) * $perPage;

        // PDO không hỗ trợ trực tiếp bindParam cho LIMIT và OFFSET, 
        // vì vậy ta phải sử dụng bindValue or truyền thẳng giá trị luôn cũng được.
        $sql .= " LIMIT $perPage OFFSET $offset";

        $stmt = self::$pdo->prepare($sql);

        // Chỉ dùng cách này được khi KHÔNG CÓ param của limit và offset
        // Nếu có param của limit và offset thì phải dùng hàm bindParam cho từng param 1.
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Hàm lấy 1 bản ghi
     * 
     * @param string $columns Mặc định lấy tất cả các cột, truyền cột phân cách nhau bằng dấu phẩy
     * @param string $conditions Mệnh đề điều kiện đặt ở đây
     * @param array $params giá trị của các tham số ảo trong $conditions
     * @return array
     * 
     * Khi dùng: $obj->find('id, name', 'id > :id AND price > :price', ['id' => 3, 'price' => 36000])
     */
    public function find($columns = '*', $conditions = null, $params = [])
    {
        $sql = "SELECT $columns FROM {$this->table}";

        if ($conditions) {
            $sql .= " WHERE $conditions";
        }

        $stmt = self::$pdo->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetch();
    }

    /**
     * Hàm thêm dữ liệu
     * 
     * @param array $data 
     * @return int
     * 
     * Khi dùng: $obj->insert([
     *                          'name' => 'Example',
     *                          'price' => 50000
     *                       ])
     */
    public function insert($data)
    {
        $keys = array_keys($data);

        $columns = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";

        $stmt = self::$pdo->prepare($sql);

        $stmt->execute($data);

        return self::$pdo->lastInsertId();
    }

    /**
     * Hàm cập nhật dữ liệu
     * 
     * @param array $data 
     * @param string $conditions Mệnh đề điều kiện đặt ở đây
     * @param array $params giá trị của các tham số ảo trong $conditions
     * @return int
     * 
     * Khi dùng: $obj->update([
     *                          'name' => 'Example',
     *                          'price' => 50000
     *                        ], 'id = :id', ['id' => 1])
     */
    public function update($data, $conditions = null, $params = [])
    {
        $keys = array_keys($data);

        $arraySets = array_map(fn($key) => "$key = :set_$key", $keys);

        $sets = implode(', ', $arraySets);

        $sql = "UPDATE {$this->table} SET $sets";

        if ($conditions) {
            $sql .= " WHERE $conditions";
        }

        $stmt = self::$pdo->prepare($sql);

        // bindParam trong set
        foreach ($data as $key => &$value) {
            $stmt->bindParam(":set_$key", $value);
        }

        // bindParam trong where
        foreach ($params as $key => &$value) {
            $stmt->bindParam(":$key", $value);
        }

        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * Hàm xóa theo điều kiện
     * 
     * @param string $conditions Mệnh đề điều kiện đặt ở đây
     * @param array $params giá trị của các tham số ảo trong $conditions
     * @return int
     * 
     * Khi dùng: $obj->delete('id = :id', ['id' => 1])
     */
    public function delete($conditions = null, $params = [])
    {
        $sql = "DELETE FROM {$this->table}";

        if ($conditions) {
            $sql .= " WHERE $conditions";
        }

        $stmt = self::$pdo->prepare($sql);

        $stmt->execute($params);

        return $stmt->rowCount();
    }
}
