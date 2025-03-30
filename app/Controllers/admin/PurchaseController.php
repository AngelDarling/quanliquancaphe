<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Admin;

class PurchaseController extends Controller
{
    public function index()
    {
        $pdo = PDO();
        $purchases = (new PurchaseOrder($pdo))->all();

        $data = [
            'purchases' => $purchases,
            'title' => 'Manage Purchases - Quán cà phê',
            'active' => 'purchases'
        ];

        $this->sendPage('admin/purchases/index', $data);
    }

    public function create()
    {
        $pdo = PDO();
        $admins = (new Admin($pdo))->all();
        $products = (new Product($pdo))->all();
        $suppliers = (new Supplier($pdo))->all();

        $data = [
            'admins' => $admins,
            'products' => $products,
            'suppliers' => $suppliers,
            'title' => 'Add Purchase - Quán cà phê',
            'active' => 'purchases'
        ];

        $this->sendPage('admin/purchases/create', $data);
    }

    public function store()
    {
        $pdo = PDO();
        $purchase = new PurchaseOrder($pdo);
        $data = $this->filterPurchaseData($_POST);

        $errors = $purchase->validate($data);
        if (empty($errors)) {
            $purchase->fill($data)->save();
            $purchaseId = $pdo->lastInsertId();

            // Xử lý chi tiết đơn nhập (PurchaseOrderDetail)
            if (isset($_POST['products']) && is_array($_POST['products'])) {
                foreach ($_POST['products'] as $productData) {
                    $purchaseDetail = new PurchaseOrderDetail($pdo);
                    $purchaseDetail->fill([
                        'product_id' => $productData['product_id'],
                        'purchase_order_id' => $purchaseId,
                        'supplier_id' => $productData['supplier_id'],
                        'quantity' => $productData['quantity'],
                        'price' => $productData['price']
                    ])->save();

                    // Cập nhật số lượng tồn kho sản phẩm
                    $product = (new Product($pdo))->where('id', $productData['product_id']);
                    if ($product) {
                        $product->stock_quantity += $productData['quantity'];
                        $product->save();
                    }
                }
            }

            $_SESSION['messages']['success'] = 'Thêm đơn nhập thành công!';
            redirect('/admin/purchases');
        }

        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $data;
        redirect('/admin/purchases/create');
    }

    public function edit($id)
    {
        $pdo = PDO();
        $purchase = (new PurchaseOrder($pdo))->where('id', $id);
        $admins = (new Admin($pdo))->all();
        $products = (new Product($pdo))->all();
        $suppliers = (new Supplier($pdo))->all();
        $purchaseDetails = (new PurchaseOrderDetail($pdo))->all();

        if (!$purchase) {
            $_SESSION['errors']['general'] = 'Đơn nhập không tồn tại.';
            redirect('/admin/purchases');
        }

        $data = [
            'purchase' => $purchase,
            'admins' => $admins,
            'products' => $products,
            'suppliers' => $suppliers,
            'purchase_details' => $purchaseDetails,
            'title' => 'Edit Purchase - Quán cà phê',
            'active' => 'purchases'
        ];

        $this->sendPage('admin/purchases/edit', $data);
    }

    public function update($id)
    {
        $pdo = PDO();
        $purchase = (new PurchaseOrder($pdo))->where('id', $id);

        if (!$purchase) {
            $_SESSION['errors']['general'] = 'Đơn nhập không tồn tại.';
            redirect('/admin/purchases');
        }

        $data = $this->filterPurchaseData($_POST);
        $errors = $purchase->validate($data);

        if (empty($errors)) {
            $purchase->fill($data)->save();

            // Xóa chi tiết cũ và thêm chi tiết mới (giả định logic cập nhật)
            $statement = $this->$pdo->prepare("DELETE FROM purchase_order_detail WHERE purchase_order_id = :id");
            $statement->execute(['id' => $id]);

            if (isset($_POST['products']) && is_array($_POST['products'])) {
                foreach ($_POST['products'] as $productData) {
                    $purchaseDetail = new PurchaseOrderDetail($pdo);
                    $purchaseDetail->fill([
                        'product_id' => $productData['product_id'],
                        'purchase_order_id' => $id,
                        'supplier_id' => $productData['supplier_id'],
                        'quantity' => $productData['quantity'],
                        'price' => $productData['price']
                    ])->save();

                    // Cập nhật số lượng tồn kho sản phẩm
                    $product = (new Product($pdo))->where('id', $productData['product_id']);
                    if ($product) {
                        $product->stock_quantity += $productData['quantity'];
                        $product->save();
                    }
                }
            }

            $_SESSION['messages']['success'] = 'Cập nhật đơn nhập thành công!';
            redirect('/admin/purchases');
        }

        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $data;
        redirect('/admin/purchases/edit/' . $id);
    }

    public function delete($id)
    {
        $pdo = PDO();
        $purchase = (new PurchaseOrder($pdo))->where('id', $id);

        if ($purchase) {
            $purchase->delete(); // Giả định có phương thức delete trong PurchaseOrder model
            $_SESSION['messages']['success'] = 'Xóa đơn nhập thành công!';
        } else {
            $_SESSION['errors']['general'] = 'Đơn nhập không tồn tại.';
        }
        redirect('/admin/purchases');
    }

    protected function filterPurchaseData(array $data): array
    {
        return [
            'admin_id' => filter_var($data['admin_id'], FILTER_VALIDATE_INT) ?? null,
            'purchase_date' => $data['purchase_date'] ?? date('Y-m-d')
        ];
    }
}
