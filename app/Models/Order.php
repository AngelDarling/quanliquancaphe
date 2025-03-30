<?php

namespace App\Models;

use PDO;

class Order
{
    private PDO $db;

    public int $id = -1;
    public int $status_id;
    public int $delivery_order_id;
    public int $admin_id;
    public int $payment_method_id;
    public ?int $promotion_id = null;
    public int $cart_id;
    public string $order_date;
    public float $total_amount;
    public ?string $cancellation_reason = null;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function where(string $column, string $value): ?Order
    {
        $statement = $this->db->prepare("SELECT * FROM `order` WHERE $column = :value");
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
                'UPDATE `order` SET status_id = :status_id, delivery_order_id = :delivery_order_id, 
                 admin_id = :admin_id, payment_method_id = :payment_method_id, promotion_id = :promotion_id, 
                 cart_id = :cart_id, order_date = :order_date, total_amount = :total_amount, 
                 cancellation_reason = :cancellation_reason WHERE id = :id'
            );
            $result = $statement->execute([
                'id' => $this->id,
                'status_id' => $this->status_id,
                'delivery_order_id' => $this->delivery_order_id,
                'admin_id' => $this->admin_id,
                'payment_method_id' => $this->payment_method_id,
                'promotion_id' => $this->promotion_id,
                'cart_id' => $this->cart_id,
                'order_date' => $this->order_date,
                'total_amount' => $this->total_amount,
                'cancellation_reason' => $this->cancellation_reason
            ]);
        } else {
            $statement = $this->db->prepare(
                'INSERT INTO `order` (status_id, delivery_order_id, admin_id, payment_method_id, promotion_id, 
                 cart_id, order_date, total_amount, cancellation_reason) 
                 VALUES (:status_id, :delivery_order_id, :admin_id, :payment_method_id, :promotion_id, 
                 :cart_id, :order_date, :total_amount, :cancellation_reason)'
            );
            $result = $statement->execute([
                'status_id' => $this->status_id,
                'delivery_order_id' => $this->delivery_order_id,
                'admin_id' => $this->admin_id,
                'payment_method_id' => $this->payment_method_id,
                'promotion_id' => $this->promotion_id,
                'cart_id' => $this->cart_id,
                'order_date' => $this->order_date,
                'total_amount' => $this->total_amount,
                'cancellation_reason' => $this->cancellation_reason
            ]);
            if ($result) {
                $this->id = $this->db->lastInsertId();
            }
        }
        return $result;
    }

    public function fill(array $data): Order
    {
        $this->status_id = filter_var($data['status_id'], FILTER_VALIDATE_INT) ?? null;
        $this->delivery_order_id = filter_var($data['delivery_order_id'], FILTER_VALIDATE_INT) ?? null;
        $this->admin_id = filter_var($data['admin_id'], FILTER_VALIDATE_INT) ?? null;
        $this->payment_method_id = filter_var($data['payment_method_id'], FILTER_VALIDATE_INT) ?? null;
        $this->promotion_id = filter_var($data['promotion_id'], FILTER_VALIDATE_INT) ?? null;
        $this->cart_id = filter_var($data['cart_id'], FILTER_VALIDATE_INT) ?? null;
        $this->order_date = $data['order_date'] ?? date('Y-m-d');
        $this->total_amount = filter_var($data['total_amount'], FILTER_VALIDATE_FLOAT) ?? 0;
        $this->cancellation_reason = $data['cancellation_reason'] ?? null;
        return $this;
    }

    private function fillFromDbRow(array $row)
    {
        $this->id = $row['id'];
        $this->status_id = $row['status_id'];
        $this->delivery_order_id = $row['delivery_order_id'];
        $this->admin_id = $row['admin_id'];
        $this->payment_method_id = $row['payment_method_id'];
        $this->promotion_id = $row['promotion_id'];
        $this->cart_id = $row['cart_id'];
        $this->order_date = $row['order_date'];
        $this->total_amount = $row['total_amount'];
        $this->cancellation_reason = $row['cancellation_reason'];
    }

    public function validate(array $data): array
    {
        $errors = [];
        if (empty($data['status_id']) || !is_numeric($data['status_id'])) {
            $errors['status_id'] = 'Status is required.';
        }
        if (empty($data['cart_id']) || !is_numeric($data['cart_id'])) {
            $errors['cart_id'] = 'Cart is required.';
        }
        if (empty($data['payment_method_id']) || !is_numeric($data['payment_method_id'])) {
            $errors['payment_method_id'] = 'Payment method is required.';
        }
        if (empty($data['order_date']) || !strtotime($data['order_date'])) {
            $errors['order_date'] = 'Order date is invalid.';
        }
        if (empty($data['total_amount']) || $data['total_amount'] <= 0) {
            $errors['total_amount'] = 'Total amount must be greater than 0.';
        }
        return $errors;
    }

    // Thêm phương thức all (đã có trong câu trả lời trước)
    public function all(): array
    {
        $statement = $this->db->query("SELECT o.*, s.name as status_name, do.address as delivery_address, 
                                   a.name as admin_name, pm.name as payment_method_name, 
                                   p.discount_value as promotion_discount, c.id as cart_id 
                                   FROM `order` o 
                                   LEFT JOIN status s ON o.status_id = s.id 
                                   LEFT JOIN delivery_order do ON o.delivery_order_id = do.id 
                                   LEFT JOIN admin a ON o.admin_id = a.id 
                                   LEFT JOIN payment_method pm ON o.payment_method_id = pm.id 
                                   LEFT JOIN promotion p ON o.promotion_id = p.id 
                                   LEFT JOIN cart c ON o.cart_id = c.id");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thêm phương thức delete
    public function delete(): bool
    {
        if ($this->id >= 0) {
            $statement = $this->db->prepare("DELETE FROM `order` WHERE id = :id");
            return $statement->execute(['id' => $this->id]);
        }
        return false;
    }

    public function getOrderDetails(): array
    {
        $statement = $this->db->prepare("SELECT od.*, p.name as product_name, p.price 
                                        FROM order_detail od 
                                        LEFT JOIN product p ON od.product_id = p.id 
                                        WHERE od.order_id = :order_id");
        $statement->execute(['order_id' => $this->id]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatus(): ?Status
    {
        return (new Status($this->db))->where('id', $this->status_id);
    }

    public function getDeliveryOrder(): ?DeliveryOrder
    {
        return (new DeliveryOrder($this->db))->where('id', $this->delivery_order_id);
    }

    public function getAdmin(): ?Admin
    {
        return (new Admin($this->db))->where('id', $this->admin_id);
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return (new PaymentMethod($this->db))->where('id', $this->payment_method_id);
    }

    public function getPromotion(): ?Promotion
    {
        if ($this->promotion_id) {
            return (new Promotion($this->db))->where('id', $this->promotion_id);
        }
        return null;
    }

    public function getCart(): ?Cart
    {
        return (new Cart($this->db))->where('id', $this->cart_id);
    }
}
