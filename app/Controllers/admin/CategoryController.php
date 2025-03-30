<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    private $categoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->categoryModel = new Category(pdo());
    }

    public function index()
    {
        $categories = $this->categoryModel->all();
        return $this->sendPage('admin/categories/index', [
            'pageTitle' => 'Quản lý danh mục',
            'categories' => $categories
        ]);
    }

    public function create()
    {
        $formValues = $this->getSavedFormValues();
        return $this->sendPage('admin/categories/add', [
            'pageTitle' => 'Thêm danh mục mới',
            'formValues' => $formValues
        ]);
    }

    public function store()
    {
        $name = $_POST['name'] ?? '';
        $imageFile = $_FILES['image'] ?? null;

        $image = $this->uploadImage($imageFile, '../public/assets/img/category_img/');
        if ($image === false) {
            $this->saveFormValues($_POST, ['image']);
            return redirect('/admin/categories/add', ['error' => 'Lỗi khi tải lên hình ảnh']);
        }

        if ($this->categoryModel->create($name, $image)) {
            unset($_SESSION['form']);
            return redirect('/admin/categories');
        }

        $this->saveFormValues($_POST, ['image']);
        return redirect('/admin/categories/add', ['error' => 'Không thể thêm danh mục']);
    }

    public function update()
    {
        $id = $_POST['id'] ?? '';
        $name = $_POST['name'] ?? '';

        if ($this->categoryModel->update($id, $name)) {
            return redirect('/admin/categories');
        }

        return $this->index();
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
