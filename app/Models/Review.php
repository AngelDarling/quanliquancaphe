<?php

namespace App\Models;

use PDO;

class Review
{
    private PDO $db;

    public int $id = -1;
    public int $user_id;
    public ?int $product_id = null;
    public string $content;
    public string $feedback;
    public int $rating;
    public string $date;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function where(string $column, string $value): ?Review
    {
        $statement = $this->db->prepare("SELECT * FROM review WHERE $column = :value");
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
                'UPDATE review SET user_id = :user_id, product_id = :product_id, content = :content, 
                 feedback = :feedback, rating = :rating, date = :date WHERE id = :id'
            );
            $result = $statement->execute([
                'id' => $this->id,
                'user_id' => $this->user_id,
                'product_id' => $this->product_id,
                'content' => $this->content,
                'feedback' => $this->feedback,
                'rating' => $this->rating,
                'date' => $this->date
            ]);
        } else {
            $statement = $this->db->prepare(
                'INSERT INTO review (user_id, product_id, content, feedback, rating, date) 
                 VALUES (:user_id, :product_id, :content, :feedback, :rating, :date)'
            );
            $result = $statement->execute([
                'user_id' => $this->user_id,
                'product_id' => $this->product_id,
                'content' => $this->content,
                'feedback' => $this->feedback,
                'rating' => $this->rating,
                'date' => $this->date
            ]);
            if ($result) {
                $this->id = $this->db->lastInsertId();
            }
        }
        return $result;
    }

    public function fill(array $data): Review
    {
        $this->user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT) ?? null;
        $this->product_id = filter_var($data['product_id'], FILTER_VALIDATE_INT) ?? null;
        $this->content = $data['content'] ?? '';
        $this->feedback = $data['feedback'] ?? '';
        $this->rating = filter_var($data['rating'], FILTER_VALIDATE_INT) ?? 0;
        $this->date = $data['date'] ?? date('Y-m-d H:i:s');
        return $this;
    }

    private function fillFromDbRow(array $row)
    {
        $this->id = $row['id'];
        $this->user_id = $row['user_id'];
        $this->product_id = $row['product_id'];
        $this->content = $row['content'];
        $this->feedback = $row['feedback'];
        $this->rating = $row['rating'];
        $this->date = $row['date'];
    }

    public function validate(array $data): array
    {
        $errors = [];
        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            $errors['user_id'] = 'User is required.';
        }
        if (empty($data['content'])) {
            $errors['content'] = 'Content is required.';
        }
        if (empty($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            $errors['rating'] = 'Rating must be between 1 and 5.';
        }
        return $errors;
    }

    public function all(): array
    {
        $statement = $this->db->query("SELECT r.*, u.name as user_name, p.name as product_name 
                                      FROM review r 
                                      LEFT JOIN users u ON r.user_id = u.id 
                                      LEFT JOIN product p ON r.product_id = p.id");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUser(): ?User
    {
        return (new User($this->db))->where('id', $this->user_id);
    }

    public function getProduct(): ?Product
    {
        if ($this->product_id) {
            return (new Product($this->db))->where('id', $this->product_id);
        }
        return null;
    }
}
