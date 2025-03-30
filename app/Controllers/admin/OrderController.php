<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\Order;
use App\Models\Status;

class OrderController extends Controller
{
    public function index()
    {
        $pdo = PDO();
        $orders = (new Order($pdo))->all();
        $statuses = (new Status($pdo))->all();

        $data = [
            'orders' => $orders,
            'statuses' => $statuses,
            'title' => 'Manage Orders - Quán cà phê',
            'active' => 'orders'
        ];

        $this->sendPage('admin/orders/index', $data);
    }

    public function show($id)
    {
        $pdo = PDO();
        $order = (new Order($pdo))->where('id', $id);
        $statuses = (new Status($pdo))->all();

        if (!$order) {
            $_SESSION['errors']['general'] = 'Đơn hàng không tồn tại.';
            redirect('/admin/orders');
        }

        $data = [
            'order' => $order,
            'order_details' => $order->getOrderDetails(),
            'statuses' => $statuses,
            'title' => 'Order Details - Quán cà phê',
            'active' => 'orders'
        ];

        $this->sendPage('admin/orders/show', $data);
    }

    public function updateStatus($id)
    {
        $pdo = PDO();
        $order = (new Order($pdo))->where('id', $id);

        if (!$order) {
            $_SESSION['errors']['general'] = 'Đơn hàng không tồn tại.';
            redirect('/admin/orders');
        }

        $status_id = filter_var($_POST['status_id'], FILTER_VALIDATE_INT);
        $reason = $_POST['cancellation_reason'] ?? null;
        $delivery_order_id = filter_var($_POST['delivery_order_id'], FILTER_VALIDATE_INT) ?? null;

        if ($status_id <= 0) {
            $_SESSION['errors']['status_id'] = 'Trạng thái không hợp lệ.';
            redirect('/admin/orders/show/' . $id);
        }

        $order->status_id = $status_id;
        $order->cancellation_reason = $reason;
        $order->delivery_order_id = $delivery_order_id;
        $order->save();

        $_SESSION['messages']['success'] = 'Cập nhật trạng thái đơn hàng thành công!';
        redirect('/admin/orders');
    }

    public function delete($id)
    {
        $pdo = PDO();
        $order = (new Order($pdo))->where('id', $id);

        if ($order) {
            $order->delete(); // Giả định có phương thức delete trong Order model
            $_SESSION['messages']['success'] = 'Xóa đơn hàng thành công!';
        } else {
            $_SESSION['errors']['general'] = 'Đơn hàng không tồn tại.';
        }
        redirect('/admin/orders');
    }
}
