<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;

class ProductController extends Controller
{
    private $productModel;
    private $categoryModel;
    private $supplierModel;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product(pdo());
        $this->categoryModel = new Category(pdo());
        $this->supplierModel = new Supplier(pdo());
    }

    public function index()
    {
        $products = $this->productModel->all();
        return $this->sendPage('admin/products/index', [
            'pageTitle' => 'Quản lý sản phẩm',
            'products' => $products
        ]);
    }

    public function create()
    {
        $categories = $this->categoryModel->all();
        $suppliers = $this->supplierModel->all();
        $formValues = $this->getSavedFormValues();
        return $this->sendPage('admin/products/add', [
            'pageTitle' => 'Thêm sản phẩm mới',
            'categories' => $categories,
            'suppliers' => $suppliers,
            'formValues' => $formValues
        ]);
    }

    public function store()
    {
        $category_id = $_POST['category_id'] ?? '';
        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? 0;
        $stock_quantity = $_POST['stock_quantity'] ?? 0;
        $description = $_POST['description'] ?? '';
        $imageFile = $_FILES['image'] ?? null;

        $image = $this->uploadImage($imageFile, '../public/assets/img/product_img/');
        if ($image === false) {
            $this->saveFormValues($_POST, ['image']);
            return redirect('/admin/products/add', ['error' => 'Lỗi khi tải lên hình ảnh']);
        }

        if ($this->productModel->create($category_id, $name, $price, $stock_quantity, $image, $description)) {
            unset($_SESSION['form']);
            return redirect('/admin/products');
        }

        $this->saveFormValues($_POST, ['image']);
        return redirect('/admin/products/add', ['error' => 'Không thể thêm sản phẩm']);
    }

    public function update()
    {
        $id = $_POST['id'] ?? '';
        $category_id = $_POST['category_id'] ?? '';
        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? 0;
        $stock_quantity = $_POST['stock_quantity'] ?? 0;
        $description = $_POST['description'] ?? '';
        $imageFile = $_FILES['image'] ?? null;

        $image = $imageFile['size'] > 0 ? $this->uploadImage($imageFile, '../public/assets/img/product_img/') : $_POST['old_image'] ?? 'default.png';
        if ($image === false && $imageFile['size'] > 0) {
            $this->saveFormValues($_POST, ['image']);
            return redirect('/admin/products', ['error' => 'Lỗi khi tải lên hình ảnh']);
        }

        if ($this->productModel->update($id, $category_id, $name, $price, $stock_quantity, $image, $description)) {
            return redirect('/admin/products');
        }

        return $this->index();
    }

    public function updateQuantity()
    {
        $id = $_POST['id'] ?? '';
        $quantity = $_POST['quantity'] ?? 0;

        if (!is_numeric($quantity) || $quantity < 0) {
            return redirect('/admin/products', ['error' => 'Số lượng không hợp lệ']);
        }

        if ($this->productModel->updateQuantity($id, $quantity)) {
            return redirect('/admin/products');
        }

        return redirect('/admin/products', ['error' => 'Không thể cập nhật số lượng']);
    }

    private function uploadImage($file, $targetDir)
    {
        if (!$file || $file['size'] == 0) {
            return 'default.png';
        }

        $fileName = basename($file['name']);
        $targetFile = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        if ($file['size'] > 30000000) {
            return false;
        }

        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg'])) {
            return false;
        }

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $fileName;
        }

        return false;
    }
}
