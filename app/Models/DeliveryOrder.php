<?php

namespace App\Models;

use PDO;

class DeliveryOrder
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function all()
    {
        $query = "SELECT do.*, dp.name as provider_name 
                  FROM delivery_order do 
                  JOIN delivery_provider dp ON do.delivery_provider_id = dp.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($provider_id, $address, $start_date, $completion_date)
    {
        $query = "INSERT INTO delivery_order (delivery_provider_id, address, start_date, completion_date) 
                  VALUES (:provider_id, :address, :start_date, :completion_date)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':provider_id', $provider_id);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':completion_date', $completion_date);
        return $stmt->execute();
    }

    public function update($id, $address, $start_date, $completion_date)
    {
        $query = "UPDATE delivery_order 
                  SET address = :address, start_date = :start_date, completion_date = :completion_date 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':completion_date', $completion_date);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
