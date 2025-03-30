<?php

namespace App\Models;

use PDO;

class Cart
{
    private PDO $db;

    public int $id = -1;
    public int $user_id;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function where(string $column, string $value): ?Cart
    {
        $statement = $this->db->prepare("SELECT * FROM cart WHERE $column = :value");
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
                'UPDATE cart SET user_id = :user_id WHERE id = :id'
            );
            $result = $statement->execute([
                'id' => $this->id,
                'user_id' => $this->user_id
            ]);
        } else {
            $statement = $this->db->prepare(
                'INSERT INTO cart (user_id) VALUES (:user_id)'
            );
            $result = $statement->execute([
                'user_id' => $this->user_id
            ]);
            if ($result) {
                $this->id = $this->db->lastInsertId();
            }
        }
        return $result;
    }

    public function fill(array $data): Cart
    {
        $this->user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT) ?? null;
        return $this;
    }

    private function fillFromDbRow(array $row)
    {
        $this->id = $row['id'];
        $this->user_id = $row['user_id'];
    }

    public function validate(array $data): array
    {
        $errors = [];
        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            $errors['user_id'] = 'User is required.';
        }
        return $errors;
    }

    public function all(): array
    {
        $statement = $this->db->query("SELECT c.*, u.name as user_name FROM cart c LEFT JOIN users u ON c.user_id = u.id");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCartDetails(): array
    {
        $statement = $this->db->prepare("SELECT cd.*, p.name as product_name, p.price 
                                        FROM cart_detail cd 
                                        LEFT JOIN product p ON cd.product_id = p.id 
                                        WHERE cd.cart_id = :cart_id");
        $statement->execute(['cart_id' => $this->id]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUser(): ?User
    {
        return (new User($this->db))->where('id', $this->user_id);
    }
}
