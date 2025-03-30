<?php

namespace App\Models;

use PDO;

class CartDetail
{
    private PDO $db;

    public int $product_id;
    public int $cart_id;
    public int $quantity;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function where(string $column, string $value): ?CartDetail
    {
        $statement = $this->db->prepare("SELECT * FROM cart_detail WHERE $column = :value");
        $statement->execute(['value' => $value]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->fillFromDbRow($row);
            return $this;
        }
        return null;
    }

    public function save(): bool
    {
        $statement = $this->db->prepare(
            'INSERT INTO cart_detail (product_id, cart_id, quantity) 
             VALUES (:product_id, :cart_id, :quantity) 
             ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)'
        );
        return $statement->execute([
            'product_id' => $this->product_id,
            'cart_id' => $this->cart_id,
            'quantity' => $this->quantity
        ]);
    }

    public function fill(array $data): CartDetail
    {
        $this->product_id = filter_var($data['product_id'], FILTER_VALIDATE_INT) ?? null;
        $this->cart_id = filter_var($data['cart_id'], FILTER_VALIDATE_INT) ?? null;
        $this->quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT) ?? 0;
        return $this;
    }

    private function fillFromDbRow(array $row)
    {
        $this->product_id = $row['product_id'];
        $this->cart_id = $row['cart_id'];
        $this->quantity = $row['quantity'];
    }

    public function validate(array $data): array
    {
        $errors = [];
        if (empty($data['product_id']) || !is_numeric($data['product_id'])) {
            $errors['product_id'] = 'Product is required.';
        }
        if (empty($data['cart_id']) || !is_numeric($data['cart_id'])) {
            $errors['cart_id'] = 'Cart is required.';
        }
        if (empty($data['quantity']) || $data['quantity'] <= 0) {
            $errors['quantity'] = 'Quantity must be greater than 0.';
        }
        return $errors;
    }

    public function all(): array
    {
        $statement = $this->db->query("SELECT cd.*, p.name as product_name, c.user_id 
                                      FROM cart_detail cd 
                                      LEFT JOIN product p ON cd.product_id = p.id 
                                      LEFT JOIN cart c ON cd.cart_id = c.id");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProduct(): ?Product
    {
        return (new Product($this->db))->where('id', $this->product_id);
    }

    public function getCart(): ?Cart
    {
        return (new Cart($this->db))->where('id', $this->cart_id);
    }
}
