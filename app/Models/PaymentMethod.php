<?php

namespace App\Models;

use PDO;

class PaymentMethod
{
    private PDO $db;

    public int $id = -1;
    public string $name;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function where(string $column, string $value): ?PaymentMethod
    {
        $statement = $this->db->prepare("SELECT * FROM payment_method WHERE $column = :value");
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
                'UPDATE payment_method SET name = :name WHERE id = :id'
            );
            $result = $statement->execute([
                'id' => $this->id,
                'name' => $this->name
            ]);
        } else {
            $statement = $this->db->prepare(
                'INSERT INTO payment_method (name) VALUES (:name)'
            );
            $result = $statement->execute([
                'name' => $this->name
            ]);
            if ($result) {
                $this->id = $this->db->lastInsertId();
            }
        }
        return $result;
    }

    public function fill(array $data): PaymentMethod
    {
        $this->name = $data['name'] ?? '';
        return $this;
    }

    private function fillFromDbRow(array $row)
    {
        $this->id = $row['id'];
        $this->name = $row['name'];
    }

    public function validate(array $data): array
    {
        $errors = [];
        if (empty($data['name'])) {
            $errors['name'] = 'Payment method name is required.';
        }
        return $errors;
    }

    public function all(): array
    {
        $statement = $this->db->query("SELECT * FROM payment_method");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
