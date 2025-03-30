<?php

namespace App\Models;

use PDO;

class User
{
    private $conn;
    public $id;
    public $name;
    public $address;
    public $phone;
    public $date;
    public $email;
    public $gender;
    public $created_at;
    public $password;
    public $avatar;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /**
     * Tìm kiếm một user theo một cột cụ thể
     *
     * @param string $column Tên cột (email, id, v.v.)
     * @param mixed $value Giá trị cần tìm
     * @return User|null
     */
    public function where(string $column, $value): ?User
    {
        $query = "SELECT * FROM users WHERE $column = :value LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':value', $value);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $user = new self($this->conn);
            $user->fill($data);
            return $user;
        }

        return null;
    }

    /**
     * Tìm user theo ID
     *
     * @param int $id
     * @return User|null
     */
    public function find(int $id): ?User
    {
        return $this->where('id', $id);
    }

    /**
     * Điền dữ liệu từ mảng vào các thuộc tính của đối tượng
     *
     * @param array $data
     */
    private function fill(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->address = $data['address'] ?? null;
        $this->phone = $data['phone'] ?? null;
        $this->date = $data['date'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->gender = $data['gender'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->avatar = $data['avatar'] ?? null;
    }

    /**
     * Lấy tất cả user
     *
     * @return array
     */
    public function all(): array
    {
        $query = "SELECT * FROM users";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $users = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = new self($this->conn);
            $user->fill($data);
            $users[] = $user;
        }

        return $users;
    }
}
