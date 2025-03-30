<?php

namespace App\Controllers\Auth;

use App\Models\User;
use App\Controllers\Controller;

class RegisterController extends Controller
{
    public function __construct()
    {
        // Kiểm tra nếu người dùng đã đăng nhập, chuyển hướng đến trang chính
        if (USER_GUARD()->isUserLoggedIn()) {
            redirect('/home');
        }

        parent::__construct();
    }

    public function create()
    {
        // Lấy giá trị đã lưu từ form và các lỗi (nếu có)
        $data = [
            'old' => $this->getSavedFormValues(),
            'errors' => $_SESSION['errors'] ?? null,
        ];

        // Xóa lỗi khỏi session sau khi đã lấy để tránh hiển thị lại lần sau
        unset($_SESSION['errors']);

        // Gửi trang đăng ký với template Twig
        $this->sendPage('auth/user_login_register', $data);
    }

    public function store()
    {
        // Lưu giá trị từ form, ngoại trừ mật khẩu
        $this->saveFormValues($_POST, ['password', 'password_confirmation']);

        // Lọc dữ liệu người dùng từ POST
        $data = $this->filterUserData($_POST);
        $newUser = new User(PDO());

        // Xác thực dữ liệu người dùng
        $model_errors = $newUser->validate($data);

        // Xử lý upload avatar
        if (empty($model_errors) && isset($_FILES['avatar'])) {
            $avatarName = $newUser->uploadAvatar($_FILES['avatar']);
            if ($avatarName === null) {
                $model_errors['avatar'] = 'Failed to upload avatar.';
            } else {
                $data['avatar'] = $avatarName;
            }
        }

        if (empty($model_errors)) {
            // Nếu không có lỗi, điền thông tin và lưu người dùng mới
            $newUser->fillUser($data)->save();

            // Gửi thông báo thành công và chuyển hướng đến trang login
            $_SESSION['messages'] = ['success' => 'Tài khoản đã được tạo thành công.'];
            redirect('/login');
        }

        // Nếu có lỗi, lưu thông báo lỗi vào session và chuyển hướng về trang login
        $_SESSION['errors'] = $model_errors;
        redirect('/login');
    }

    protected function filterUserData(array $data)
    {
        return [
            'name' => $data['name'] ?? null,
            'email' => filter_var($data['email'], FILTER_VALIDATE_EMAIL),
            'password' => $data['password'] ?? null,
            'password_confirmation' => $data['password_confirmation'] ?? null,
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'date' => $data['date'] ?? null,
            'gender' => in_array($data['gender'], ['male', 'female']) ? $data['gender'] : null,
            'avatar' => null // Mặc định là null, sẽ được cập nhật nếu upload thành công
        ];
    }
}
