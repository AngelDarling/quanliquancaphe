<?php

namespace App\Models;

use PDO;

class Status
{
    private PDO $db;

    public int $id = -1;
    public string $name;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function where(string $column, string $value): ?Status
    {
        $statement = $this->db->prepare("SELECT * FROM status WHERE $column = :value");
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
                'UPDATE status SET name = :name WHERE id = :id'
            );
            $result = $statement->execute([
                'id' => $this->id,
                'name' => $this->name
            ]);
        } else {
            $statement = $this->db->prepare(
                'INSERT INTO status (name) VALUES (:name)'
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

    public function fill(array $data): Status
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
            $errors['name'] = 'Status name is required.';
        }
        return $errors;
    }

    public function all(): array
    {
        $statement = $this->db->query("SELECT * FROM status");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrders(): array
    {
        $statement = $this->db->prepare("SELECT o.* FROM `order` o WHERE o.status_id = :id");
        $statement->execute(['id' => $this->id]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
