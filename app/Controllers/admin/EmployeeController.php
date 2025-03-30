<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\admin;
use App\Models\Role;

class adminController extends Controller
{
    public function index()
    {
        $pdo = PDO();
        $admins = (new admin($pdo))->all();
        $roles = (new Role($pdo))->all();

        $data = [
            'admins' => $admins,
            'roles' => $roles,
            'title' => 'Manage admins - Quán cà phê',
            'active' => 'admins'
        ];

        $this->sendPage('admin/admins/index', $data);
    }

    public function create()
    {
        $pdo = PDO();
        $roles = (new Role($pdo))->all();

        $data = [
            'roles' => $roles,
            'title' => 'Add admin - Quán cà phê',
            'active' => 'admins'
        ];

        $this->sendPage('admin/admins/create', $data);
    }

    public function store()
    {
        $pdo = PDO();
        $admin = new admin($pdo);
        $data = $this->filteradminData($_POST);

        $errors = $admin->validate($data);
        if (empty($errors)) {
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $data['avatar'] = $admin->uploadAvatar($_FILES['avatar']);
            }
            $admin->fill($data)->save();
            $_SESSION['messages']['success'] = 'Thêm nhân viên thành công!';
            redirect('/admin/admins');
        }

        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $data;
        redirect('/admin/admins/create');
    }

    public function edit($id)
    {
        $pdo = PDO();
        $admin = (new admin($pdo))->where('id', $id);
        $roles = (new Role($pdo))->all();

        if (!$admin) {
            $_SESSION['errors']['general'] = 'Nhân viên không tồn tại.';
            redirect('/admin/admins');
        }

        $data = [
            'admin' => $admin,
            'roles' => $roles,
            'title' => 'Edit admin - Quán cà phê',
            'active' => 'admins'
        ];

        $this->sendPage('admin/admins/edit', $data);
    }

    public function update($id)
    {
        $pdo = PDO();
        $admin = (new admin($pdo))->where('id', $id);

        if (!$admin) {
            $_SESSION['errors']['general'] = 'Nhân viên không tồn tại.';
            redirect('/admin/admins');
        }

        $data = $this->filteradminData($_POST);
        $errors = $admin->validate($data);

        if (empty($errors)) {
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $data['avatar'] = $admin->uploadAvatar($_FILES['avatar']);
            }
            if (!empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                unset($data['password']); // Giữ mật khẩu cũ nếu không đổi
            }
            $admin->fill($data)->save();
            $_SESSION['messages']['success'] = 'Cập nhật nhân viên thành công!';
            redirect('/admin/admins');
        }

        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $data;
        redirect('/admin/admins/edit/' . $id);
    }

    public function delete($id)
    {
        $pdo = PDO();
        $admin = (new admin($pdo))->where('id', $id);

        if ($admin) {
            $admin->delete(); // Giả định có phương thức delete trong admin model
            $_SESSION['messages']['success'] = 'Xóa nhân viên thành công!';
        } else {
            $_SESSION['errors']['general'] = 'Nhân viên không tồn tại.';
        }
        redirect('/admin/admins');
    }

    protected function filteradminData(array $data): array
    {
        return [
            'name' => $data['name'] ?? '',
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'address' => $data['address'] ?? '',
            'birth_date' => $data['birth_date'] ?? '',
            'gender' => in_array($data['gender'], ['male', 'female']) ? $data['gender'] : 'male',
            'role_id' => filter_var($data['role_id'], FILTER_VALIDATE_INT) ?? 1,
            'username' => $data['username'] ?? '',
            'password' => $data['password'] ?? '',
            'password_confirmation' => $data['password_confirmation'] ?? '',
            'avatar' => $data['avatar'] ?? 'default.jpg'
        ];
    }
}
