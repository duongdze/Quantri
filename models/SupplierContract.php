<?php
require_once 'models/BaseModel.php';

class SupplierContract extends BaseModel
{
    protected $table = 'supplier_contracts';
    protected $columns = [
        'id',
        'supplier_id',
        'contract_name',
        'start_date',
        'end_date',
        'price_info',
        'status',
        'notes'
    ];

    public function getBySupplierId($supplierId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE supplier_id = :supplier_id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(['supplier_id' => $supplierId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
