<?php

namespace App\Models;

use PDO;

class Feedback
{
    private PDO $db;

    public int $id = -1;
    public int $review_id;
    public int $admin_id;
    public string $content;
    public string $feedback_date;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function where(string $column, string $value): ?Feedback
    {
        $statement = $this->db->prepare("SELECT * FROM feedback WHERE $column = :value");
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
                'UPDATE feedback SET review_id = :review_id, admin_id = :admin_id, content = :content, 
                 feedback_date = :feedback_date WHERE id = :id'
            );
            $result = $statement->execute([
                'id' => $this->id,
                'review_id' => $this->review_id,
                'admin_id' => $this->admin_id,
                'content' => $this->content,
                'feedback_date' => $this->feedback_date
            ]);
        } else {
            $statement = $this->db->prepare(
                'INSERT INTO feedback (review_id, admin_id, content, feedback_date) 
                 VALUES (:review_id, :admin_id, :content, :feedback_date)'
            );
            $result = $statement->execute([
                'review_id' => $this->review_id,
                'admin_id' => $this->admin_id,
                'content' => $this->content,
                'feedback_date' => $this->feedback_date
            ]);
            if ($result) {
                $this->id = $this->db->lastInsertId();
            }
        }
        return $result;
    }

    public function fill(array $data): Feedback
    {
        $this->review_id = filter_var($data['review_id'], FILTER_VALIDATE_INT) ?? null;
        $this->admin_id = filter_var($data['admin_id'], FILTER_VALIDATE_INT) ?? null;
        $this->content = $data['content'] ?? '';
        $this->feedback_date = $data['feedback_date'] ?? date('Y-m-d H:i:s');
        return $this;
    }

    private function fillFromDbRow(array $row)
    {
        $this->id = $row['id'];
        $this->review_id = $row['review_id'];
        $this->admin_id = $row['admin_id'];
        $this->content = $row['content'];
        $this->feedback_date = $row['feedback_date'];
    }

    public function validate(array $data): array
    {
        $errors = [];
        if (empty($data['review_id']) || !is_numeric($data['review_id'])) {
            $errors['review_id'] = 'Review is required.';
        }
        if (empty($data['admin_id']) || !is_numeric($data['admin_id'])) {
            $errors['admin_id'] = 'Admin is required.';
        }
        if (empty($data['content'])) {
            $errors['content'] = 'Content is required.';
        }
        return $errors;
    }

    public function all(): array
    {
        $statement = $this->db->query("SELECT f.*, r.content as review_content, a.name as admin_name 
                                      FROM feedback f 
                                      LEFT JOIN review r ON f.review_id = r.id 
                                      LEFT JOIN admin a ON f.admin_id = a.id");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReview(): ?Review
    {
        return (new Review($this->db))->where('id', $this->review_id);
    }

    public function getAdmin(): ?Admin
    {
        return (new Admin($this->db))->where('id', $this->admin_id);
    }
}
