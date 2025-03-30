<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\DeliveryOrder;
use App\Models\DeliveryProvider;

class DeliveryController extends Controller
{
    public function index()
    {
        $pdo = PDO();
        $deliveries = (new DeliveryOrder($pdo))->all();
        $providers = (new DeliveryProvider($pdo))->all();

        $data = [
            'deliveries' => $deliveries,
            'providers' => $providers,
            'title' => 'Manage Deliveries - Quán cà phê',
            'active' => 'deliveries'
        ];

        $this->sendPage('admin/deliveries/index', $data);
    }

    public function create()
    {
        $pdo = PDO();
        $providers = (new DeliveryProvider($pdo))->all();

        $data = [
            'providers' => $providers,
            'title' => 'Add Delivery - Quán cà phê',
            'active' => 'deliveries'
        ];

        $this->sendPage('admin/deliveries/create', $data);
    }

    public function store()
    {
        $pdo = PDO();
        $delivery = new DeliveryOrder($pdo);
        $data = $this->filterDeliveryData($_POST);

        $errors = $delivery->validate($data);
        if (empty($errors)) {
            $delivery->fill($data)->save();
            $_SESSION['messages']['success'] = 'Thêm đơn vận chuyển thành công!';
            redirect('/admin/deliveries');
        }

        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $data;
        redirect('/admin/deliveries/create');
    }

    public function edit($id)
    {
        $pdo = PDO();
        $delivery = (new DeliveryOrder($pdo))->where('id', $id);
        $providers = (new DeliveryProvider($pdo))->all();

        if (!$delivery) {
            $_SESSION['errors']['general'] = 'Đơn vận chuyển không tồn tại.';
            redirect('/admin/deliveries');
        }

        $data = [
            'delivery' => $delivery,
            'providers' => $providers,
            'title' => 'Edit Delivery - Quán cà phê',
            'active' => 'deliveries'
        ];

        $this->sendPage('admin/deliveries/edit', $data);
    }

    public function update($id)
    {
        $pdo = PDO();
        $delivery = (new DeliveryOrder($pdo))->where('id', $id);

        if (!$delivery) {
            $_SESSION['errors']['general'] = 'Đơn vận chuyển không tồn tại.';
            redirect('/admin/deliveries');
        }

        $data = $this->filterDeliveryData($_POST);
        $errors = $delivery->validate($data);

        if (empty($errors)) {
            $delivery->fill($data)->save();
            $_SESSION['messages']['success'] = 'Cập nhật đơn vận chuyển thành công!';
            redirect('/admin/deliveries');
        }

        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $data;
        redirect('/admin/deliveries/edit/' . $id);
    }

    public function delete($id)
    {
        $pdo = PDO();
        $delivery = (new DeliveryOrder($pdo))->where('id', $id);

        if ($delivery) {
            $delivery->delete(); // Giả định có phương thức delete trong DeliveryOrder model
            $_SESSION['messages']['success'] = 'Xóa đơn vận chuyển thành công!';
        } else {
            $_SESSION['errors']['general'] = 'Đơn vận chuyển không tồn tại.';
        }
        redirect('/admin/deliveries');
    }

    protected function filterDeliveryData(array $data): array
    {
        return [
            'delivery_provider_id' => filter_var($data['delivery_provider_id'], FILTER_VALIDATE_INT) ?? null,
            'address' => $data['address'] ?? '',
            'start_date' => $data['start_date'] ?? '',
            'completion_date' => $data['completion_date'] ?? ''
        ];
    }
}
