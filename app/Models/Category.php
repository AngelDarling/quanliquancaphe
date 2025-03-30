<?php

namespace App\Models;

use PDO;

class Category
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function all()
    {
        $query = "SELECT * FROM category";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($name, $image)
    {
        $query = "INSERT INTO category (name, image) VALUES (:name, :image)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':image', $image);
        return $stmt->execute();
    }

    public function update($id, $name)
    {
        $query = "UPDATE category SET name = :name WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
