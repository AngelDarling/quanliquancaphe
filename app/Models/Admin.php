<?php

namespace App\Models;

use PDO;

class Admin
{
    private $conn;
    public $id;
    public $name;
    public $address;
    public $phone;
    public $email;
    public $gender;
    public $date;
    public $password;
    public $hired_date;
    public $avatar;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /**
     * Tìm kiếm một admin theo một cột cụ thể
     *
     * @param string $column Tên cột (email, id, v.v.)
     * @param mixed $value Giá trị cần tìm
     * @return Admin|null
     */
    public function where(string $column, $value): ?Admin
    {
        // Danh sách các cột hợp lệ (không bao gồm role_id)
        $allowedColumns = ['id', 'name', 'address', 'phone', 'email', 'gender', 'date', 'password', 'hired_date', 'avatar'];

        // Kiểm tra cột hợp lệ
        if (!in_array($column, $allowedColumns)) {
            return null; // Trả về null nếu cột không hợp lệ
        }

        $query = "SELECT * FROM admin WHERE $column = :value LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':value', $value);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $admin = new self($this->conn);
            $admin->fill($data);
            return $admin;
        }

        return null;
    }

    /**
     * Tìm admin theo ID
     *
     * @param int $id
     * @return Admin|null
     */
    public function find(int $id): ?Admin
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
        $this->email = $data['email'] ?? null;
        $this->gender = $data['gender'] ?? null;
        $this->date = $data['date'] ? $data['date'] : null; // Xử lý '0000-00-00'
        $this->password = $data['password'] ?? null;
        $this->hired_date = $data['hired_date'] ? $data['hired_date'] : null; // Xử lý '0000-00-00'
        $this->avatar = $data['avatar'] ?? null;
    }

    /**
     * Lấy tất cả admin
     *
     * @return array
     */
    public function all(): array
    {
        $query = "SELECT * FROM admin";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $admins = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $admin = new self($this->conn);
            $admin->fill($data);
            $admins[] = $admin;
        }

        return $admins;
    }
}
