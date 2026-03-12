<?php

class User extends BaseModel
{
    protected $table = 'users';

    /**
     * Get all users with optional filters
     */
    public function getAll($filters = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        // Filter by role
        if (!empty($filters['role'])) {
            $sql .= " AND role = :role";
            $params[':role'] = $filters['role'];
        }

        // Search by name or email
        if (!empty($filters['search'])) {
            $sql .= " AND (full_name LIKE :search OR email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user by ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get user by email
     */
    public function getByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new user
     */
    public function create($data)
    {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql = "INSERT INTO {$this->table} (full_name, email, phone, password_hash, role, created_at) 
                VALUES (:full_name, :email, :phone, :password_hash, :role, NOW())";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?? null,
            ':password_hash' => $data['password'],
            ':role' => $data['role']
        ]);

        return self::$pdo->lastInsertId();
    }

    /**
     * Update user
     */
    public function updateUser($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['full_name'])) {
            $fields[] = "full_name = :full_name";
            $params[':full_name'] = $data['full_name'];
        }

        if (isset($data['email'])) {
            $fields[] = "email = :email";
            $params[':email'] = $data['email'];
        }

        if (isset($data['phone'])) {
            $fields[] = "phone = :phone";
            $params[':phone'] = $data['phone'];
        }

        if (isset($data['role'])) {
            $fields[] = "role = :role";
            $params[':role'] = $data['role'];
        }

        // Update password only if provided
        if (!empty($data['password'])) {
            $fields[] = "password_hash = :password_hash";
            $params[':password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = NOW()";
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE user_id = :id";

        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute($params);
    }
    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :id";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get user statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as customers,
                    SUM(CASE WHEN role = 'guide' THEN 1 ELSE 0 END) as guides,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_users
                FROM {$this->table}";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";
        $params = [':email' => $email];

        if ($excludeId) {
            $sql .= " AND user_id != :id";
            $params[':id'] = $excludeId;
        }

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get users by role
     */
    public function getByRole($role)
    {
        $sql = "SELECT * FROM {$this->table} WHERE role = :role ORDER BY full_name ASC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':role' => $role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if user can create role
     */
    public function canCreateRole($currentUserRole, $targetRole)
    {
        // Admin can create customer and guide
        if ($currentUserRole === 'admin') {
            return in_array($targetRole, ['customer', 'guide']);
        }

        // Guide can only create customer
        if ($currentUserRole === 'guide') {
            return $targetRole === 'customer';
        }

        return false;
    }

    /**
     * Check if user can edit another user
     */
    public function canEdit($currentUserId, $currentUserRole, $targetUserId, $targetUserRole)
    {
        // Cannot edit self
        if ($currentUserId == $targetUserId) {
            return true; // Can edit own profile
        }

        // Admin can edit customers and guides, but not other admins
        if ($currentUserRole === 'admin') {
            return in_array($targetUserRole, ['customer', 'guide']);
        }

        // Guide cannot edit other users
        return false;
    }

    /**
     * Check if user can delete another user
     */
    public function canDelete($currentUserId, $currentUserRole, $targetUserId, $targetUserRole)
    {
        // Cannot delete self
        if ($currentUserId == $targetUserId) {
            return false;
        }

        // Cannot delete admin
        if ($targetUserRole === 'admin') {
            return false;
        }

        // Admin can delete customers and guides
        if ($currentUserRole === 'admin') {
            return in_array($targetUserRole, ['customer', 'guide']);
        }

        // Guide can only delete customers
        if ($currentUserRole === 'guide') {
            return $targetUserRole === 'customer';
        }

        return false;
    }
}
