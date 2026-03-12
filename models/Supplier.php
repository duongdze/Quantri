<?php
require_once 'models/BaseModel.php';

class Supplier extends BaseModel
{
    protected $table = 'suppliers';
    protected $columns = [
        'id',
        'name',
        'type',
        'contact_person',
        'phone',
        'email',
        'address',
        'rating',
        'description'
    ];
}
