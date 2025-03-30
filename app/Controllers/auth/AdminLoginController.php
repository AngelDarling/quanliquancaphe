<?php

namespace App\Controllers\Auth;

use App\Models\Admin;
use App\Controllers\Controller;

class AdminLoginController extends Controller
{
    public function create()
    {
        if (ADMIN_GUARD()->isAdminLoggedIn()) {
            redirect('/admin/home'); // Chuyển hướng đến trang admin sau khi đăng nhập
        }

        $data = [
            'messages' => $_SESSION['messages'] ?? null,
            'old' => $this->getSavedFormValues(),
            'errors' => $_SESSION['errors'] ?? null,
        ];

        // Xóa thông báo và lỗi khỏi session sau khi lấy
        unset($_SESSION['messages'], $_SESSION['errors']);

        $this->sendPage('auth/admin_login_register', $data);
    }

    public function store()
    {
        $this->saveFormValues($_POST, ['password']);

        $credentials = $this->filterAdminCredentials($_POST);

        $errors = [];
        $admin = (new Admin(PDO()))->where('email', $credentials['email']);
        if (!$admin) {
            $errors['email'] = 'Email hoặc mật khẩu không đúng.';
        } elseif (!ADMIN_GUARD()->login($admin, $credentials)) {
            $errors['password'] = 'Email hoặc mật khẩu không đúng.';
        } else {
            redirect('/admin/home');
        }

        $_SESSION['errors'] = $errors;
        redirect('/admin/login');
    }

    public function destroy()
    {
        ADMIN_GUARD()->logout();
        redirect('/admin/login');
    }

    protected function filterAdminCredentials(array $data)
    {
        return [
            'email' => filter_var($data['email'], FILTER_VALIDATE_EMAIL),
            'password' => $data['password'] ?? null
        ];
    }
}
