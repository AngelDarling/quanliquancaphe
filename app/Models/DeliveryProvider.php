<?php

namespace App\Models;

use PDO;

class DeliveryProvider
{
    private PDO $db;

    public int $id = -1;
    public string $name;
    public string $description;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function where(string $column, string $value): ?DeliveryProvider
    {
        $statement = $this->db->prepare("SELECT * FROM delivery_provider WHERE $column = :value");
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
                'UPDATE delivery_provider SET name = :name, description = :description WHERE id = :id'
            );
            $result = $statement->execute([
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->description
            ]);
        } else {
            $statement = $this->db->prepare(
                'INSERT INTO delivery_provider (name, description) VALUES (:name, :description)'
            );
            $result = $statement->execute([
                'name' => $this->name,
                'description' => $this->description
            ]);
            if ($result) {
                $this->id = $this->db->lastInsertId();
            }
        }
        return $result;
    }

    public function fill(array $data): DeliveryProvider
    {
        $this->name = $data['name'] ?? null;
        $this->description = $data['description'] ?? null;
        return $this;
    }

    private function fillFromDbRow(array $row)
    {
        $this->id = $row['id'];
        $this->name = $row['name'];
        $this->description = $row['description'];
    }

    public function validate(array $data): array
    {
        $errors = [];
        if (empty($data['name']) || strlen($data['name']) < 2) {
            $errors['name'] = 'Delivery provider name must be at least 2 characters.';
        }
        return $errors;
    }

    // Thêm vào DeliveryProvider.php (nếu cần)
    public function all(): array
    {
        $statement = $this->db->query("SELECT * FROM delivery_provider");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
