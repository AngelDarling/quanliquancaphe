<?php

namespace App\Models;

use PDO;

class Product
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function all()
    {
        $query = "SELECT p.*, c.name as category_name FROM product p JOIN category c ON p.category_id = c.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($category_id, $name, $price, $stock_quantity, $image, $description)
    {
        $query = "INSERT INTO product (category_id, name, price, stock_quantity, image, description) 
                  VALUES (:category_id, :name, :price, :stock_quantity, :image, :description)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock_quantity', $stock_quantity);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':description', $description);
        return $stmt->execute();
    }

    public function update($id, $category_id, $name, $price, $stock_quantity, $image, $description)
    {
        $query = "UPDATE product 
                  SET category_id = :category_id, name = :name, price = :price, 
                      stock_quantity = :stock_quantity, image = :image, description = :description 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock_quantity', $stock_quantity);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updateQuantity($id, $quantity)
    {
        $query = "UPDATE product SET stock_quantity = stock_quantity + :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
