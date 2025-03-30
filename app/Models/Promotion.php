<?php

namespace App\Models;

use PDO;

class Promotion
{
    private PDO $db;

    public int $id = -1;
    public ?string $start_date = null;
    public ?string $end_date = null;
    public float $discount_value;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function where(string $column, string $value): ?Promotion
    {
        $statement = $this->db->prepare("SELECT * FROM promotion WHERE $column = :value");
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
                'UPDATE promotion SET start_date = :start_date, end_date = :end_date, 
                 discount_value = :discount_value WHERE id = :id'
            );
            $result = $statement->execute([
                'id' => $this->id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'discount_value' => $this->discount_value
            ]);
        } else {
            $statement = $this->db->prepare(
                'INSERT INTO promotion (start_date, end_date, discount_value) 
                 VALUES (:start_date, :end_date, :discount_value)'
            );
            $result = $statement->execute([
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'discount_value' => $this->discount_value
            ]);
            if ($result) {
                $this->id = $this->db->lastInsertId();
            }
        }
        return $result;
    }

    public function fill(array $data): Promotion
    {
        $this->start_date = $data['start_date'] ?? null;
        $this->end_date = $data['end_date'] ?? null;
        $this->discount_value = filter_var($data['discount_value'], FILTER_VALIDATE_FLOAT) ?? 0;
        return $this;
    }

    private function fillFromDbRow(array $row)
    {
        $this->id = $row['id'];
        $this->start_date = $row['start_date'];
        $this->end_date = $row['end_date'];
        $this->discount_value = $row['discount_value'];
    }

    public function validate(array $data): array
    {
        $errors = [];
        if (empty($data['discount_value']) || $data['discount_value'] < 0 || $data['discount_value'] > 1) {
            $errors['discount_value'] = 'Discount value must be between 0 and 1 (e.g., 0.1 for 10%).';
        }
        if (!empty($data['start_date']) && !strtotime($data['start_date'])) {
            $errors['start_date'] = 'Start date is invalid.';
        }
        if (!empty($data['end_date']) && !strtotime($data['end_date'])) {
            $errors['end_date'] = 'End date is invalid.';
        }
        if (!empty($data['start_date']) && !empty($data['end_date']) && strtotime($data['start_date']) > strtotime($data['end_date'])) {
            $errors['end_date'] = 'End date must be after start date.';
        }
        return $errors;
    }

    public function all(): array
    {
        $statement = $this->db->query("SELECT * FROM promotion");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrders(): array
    {
        $statement = $this->db->prepare("SELECT o.* FROM `order` o WHERE o.promotion_id = :id");
        $statement->execute(['id' => $this->id]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
