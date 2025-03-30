<?php

namespace App\Models;

use PDO;

class role
{
    private PDO $db;

    public int $id = -1;
    public string $name;
    public string $schedule;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function where(string $column, string $value): ?role
    {
        $statement = $this->db->prepare("SELECT * FROM role WHERE $column = :value");
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
                'UPDATE role SET name = :name, schedule = :schedule WHERE id = :id'
            );
            $result = $statement->execute([
                'id' => $this->id,
                'name' => $this->name,
                'schedule' => $this->schedule
            ]);
        } else {
            $statement = $this->db->prepare(
                'INSERT INTO role (name, schedule) VALUES (:name, :schedule)'
            );
            $result = $statement->execute([
                'name' => $this->name,
                'schedule' => $this->schedule
            ]);
            if ($result) {
                $this->id = $this->db->lastInsertId();
            }
        }
        return $result;
    }

    public function fill(array $data): role
    {
        $this->name = $data['name'] ?? null;
        $this->schedule = $data['schedule'] ?? null;
        return $this;
    }

    private function fillFromDbRow(array $row)
    {
        $this->id = $row['id'];
        $this->name = $row['name'];
        $this->schedule = $row['schedule'];
    }

    public function validate(array $data): array
    {
        $errors = [];
        if (empty($data['name']) || strlen($data['name']) < 2) {
            $errors['name'] = 'role name must be at least 2 characters.';
        }
        return $errors;
    }

    public function all(): array
    {
        $statement = $this->db->query("SELECT r.*, COUNT(e.id) as admin_count 
                                      FROM role r 
                                      LEFT JOIN admin e ON r.id = e.role_id 
                                      GROUP BY r.id, r.name, r.schedule");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
