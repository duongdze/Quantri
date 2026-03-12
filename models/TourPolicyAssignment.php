<?php
require_once 'BaseModel.php';

class TourPolicyAssignment extends BaseModel
{
    protected $table = 'tour_policy_assignments';
    protected $columns = [
        'id',
        'tour_id',
        'policy_id',
        'created_at',
    ];

    public function getByTourId($tourId)
    {
        $sql = "SELECT tpa.*, tp.name as policy_name, tp.description as policy_description
                FROM {$this->table} tpa
                JOIN tour_policies tp ON tpa.policy_id = tp.id
                WHERE tpa.tour_id = :tour_id
                ORDER BY tpa.id ASC";
        
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['tour_id' => $tourId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByPolicyId($policyId)
    {
        $sql = "SELECT tpa.*, t.name as tour_name
                FROM {$this->table} tpa
                JOIN tours t ON tpa.tour_id = t.id
                WHERE tpa.policy_id = :policy_id
                ORDER BY tpa.id ASC";
        
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['policy_id' => $policyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
