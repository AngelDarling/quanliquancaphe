<?php

namespace App\Models;

use PDO;

class PurchaseOrderDetail
{
    private PDO $db;

    public int $product_id;
    public int $purchase_order_id;
    public int $supplier_id;
    public int $quantity;
    public float $price;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function where(string $column, string $value): ?PurchaseOrderDetail
    {
        $statement = $this->db->prepare("SELECT * FROM purchase_order_detail WHERE $column = :value");
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
            'INSERT INTO purchase_order_detail (product_id, purchase_order_id, supplier_id, quantity, price) 
            VALUES (:product_id, :purchase_order_id, :supplier_id, :quantity, :price)'
        );
        return $statement->execute([
            'product_id' => $this->product_id,
            'purchase_order_id' => $this->purchase_order_id,
            'supplier_id' => $this->supplier_id,
            'quantity' => $this->quantity,
            'price' => $this->price
        ]);
    }

    public function fill(array $data): PurchaseOrderDetail
    {
        $this->product_id = (int)($data['product_id'] ?? 0);
        $this->purchase_order_id = (int)($data['purchase_order_id'] ?? 0);
        $this->supplier_id = (int)($data['supplier_id'] ?? 0);
        $this->quantity = (int)($data['quantity'] ?? 0);
        $this->price = (float)($data['price'] ?? 0);
        return $this;
    }

    private function fillFromDbRow(array $row)
    {
        $this->product_id = $row['product_id'];
        $this->purchase_order_id = $row['purchase_order_id'];
        $this->supplier_id = $row['supplier_id'];
        $this->quantity = $row['quantity'];
        $this->price = $row['price'];
    }

    public function validate(array $data): array
    {
        $errors = [];
        if (empty($data['product_id']) || !is_numeric($data['product_id'])) {
            $errors['product_id'] = 'Product is required.';
        }
        if (empty($data['purchase_order_id']) || !is_numeric($data['purchase_order_id'])) {
            $errors['purchase_order_id'] = 'Purchase order is required.';
        }
        if (empty($data['supplier_id']) || !is_numeric($data['supplier_id'])) {
            $errors['supplier_id'] = 'Supplier is required.';
        }
        if (empty($data['quantity']) || $data['quantity'] <= 0) {
            $errors['quantity'] = 'Quantity must be greater than 0.';
        }
        if (empty($data['price']) || $data['price'] <= 0) {
            $errors['price'] = 'Price must be greater than 0.';
        }
        return $errors;
    }

    public function all(): array
    {
        $statement = $this->db->query("SELECT pod.*, p.name as product_name, po.purchase_date, s.name as supplier_name FROM purchase_order_detail pod LEFT JOIN product p ON pod.product_id = p.id LEFT JOIN purchase_order po ON pod.purchase_order_id = po.id LEFT JOIN supplier s ON pod.supplier_id = s.id");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPurchaseOrder(): ?PurchaseOrder
    {
        return (new PurchaseOrder($this->db))->where('id', $this->purchase_order_id);
    }

    public function getProduct(): ?Product
    {
        return (new Product($this->db))->where('id', $this->product_id);
    }

    public function getSupplier(): ?Supplier
    {
        return (new Supplier($this->db))->where('id', $this->supplier_id);
    }
}
