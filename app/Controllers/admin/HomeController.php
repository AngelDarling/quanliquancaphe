<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $pdo = PDO();
        $orders = (new Order($pdo))->all();
        $products = (new Product($pdo))->all();

        $data = [
            'orders' => $orders,
            'products' => $products,
            'title' => 'Dashboard - Quán cà phê',
            'active' => 'home'
        ];

        $this->sendPage('admin/home', $data);
    }
}
