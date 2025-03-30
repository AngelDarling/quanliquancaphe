<?php

namespace App\Models;

use PDO;

class PurchaseOrder
{
    private PDO $db;

    public int $id = -1;
    public int $admin_id;
    public string $purchase_date;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function where(string $column, string $value): ?PurchaseOrder
    {
        $statement = $this->db->prepare("SELECT * FROM purchase_order WHERE $column = :value");
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
        $result = false;
        if ($this->id >= 0) {
            $statement = $this->db->prepare(
                'UPDATE purchase_order SET admin_id = :admin_id, purchase_date = :purchase_date WHERE id = :id'
            );
            $result = $statement->execute([
                'id' => $this->id,
                'admin_id' => $this->admin_id,
                'purchase_date' => $this->purchase_date
            ]);
        } else {
            $statement = $this->db->prepare(
                'INSERT INTO purchase_order (admin_id, purchase_date) VALUES (:admin_id, :purchase_date)'
            );
            $result = $statement->execute([
                'admin_id' => $this->admin_id,
                'purchase_date' => $this->purchase_date
            ]);
            if ($result) {
                $this->id = $this->db->lastInsertId();
            }
        }
        return $result;
    }

    public function fill(array $data): PurchaseOrder
    {
        $this->admin_id = (int)($data['admin_id'] ?? 0);
        $this->purchase_date = $data['purchase_date'] ?? date('Y-m-d');
        return $this;
    }

    private function fillFromDbRow(array $row)
    {
        $this->id = $row['id'];
        $this->admin_id = $row['admin_id'];
        $this->purchase_date = $row['purchase_date'];
    }

    public function validate(array $data): array
    {
        $errors = [];
        if (empty($data['admin_id']) || !is_numeric($data['admin_id'])) {
            $errors['admin_id'] = 'Admin is required.';
        }
        if (empty($data['purchase_date']) || !strtotime($data['purchase_date'])) {
            $errors['purchase_date'] = 'Purchase date is invalid.';
        }
        return $errors;
    }

    public function all(): array
    {
        $statement = $this->db->query("SELECT po.*, a.name as admin_name FROM purchase_order po LEFT JOIN admin a ON po.admin_id = a.id");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAdmin(): ?Admin
    {
        return (new Admin($this->db))->where('id', $this->admin_id);
    }

    // Thêm phương thức delete
    public function delete(): bool
    {
        if ($this->id >= 0) {
            $statement = $this->db->prepare("DELETE FROM purchase_order WHERE id = :id");
            return $statement->execute(['id' => $this->id]);
        }
        return false;
    }
}
