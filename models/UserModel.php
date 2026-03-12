<?php
class UserModel extends BaseModel
{
    protected $table = 'users';

    /**
     * Kiểm tra đăng nhập cho admin.
     * @param string $email
     * @param string $password
     * @return array|false
     */
    public function checkLogin($email, $password)
    {
        $sql = "SELECT * FROM users WHERE email = :email AND (role = 'admin' OR role = 'guide') LIMIT 1";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        // DB column is `password` (may be plain text for old data or a hash)
        $stored = $user['password_hash'] ?? '';

        // Nếu mật khẩu trong DB là hash và khớp
        if (!empty($stored) && password_verify($password, $stored)) {
            return $user;
        }

        // Nếu mật khẩu trong DB lưu plaintext (ví dụ dữ liệu mẫu) -> so sánh trực tiếp
        if ($stored === $password) {
            // Hash lại và cập nhật DB để chuyển sang lưu hash
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $this->update(['password_hash' => $newHash], 'user_id = :id', ['id' => $user['user_id']]);
                // Cập nhật mảng user để session lưu đúng trạng thái
                $user['password_hash'] = $newHash;
            } catch (Exception $e) {
                // Nếu cập nhật thất bại, vẫn cho phép đăng nhập nhưng ghi log
                error_log('Failed to update user password hash: ' . $e->getMessage());
            }
            return $user;
        }

        // Không khớp
        return false;
    }

    /**
     * Lấy số lượng khách hàng mới trong tháng
     * @param int $month Tháng (1-12)
     * @param int $year Năm
     * @return int Số lượng khách hàng mới
     */
    public function getNewCustomersThisMonth($month, $year)
    {
        $startDate = "$year-$month-01 00:00:00";
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE role = 'customer' 
                AND created_at BETWEEN :start_date AND :end_date";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }
}
