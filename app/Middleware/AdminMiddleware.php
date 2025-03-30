<?php

namespace App\Middleware;

class AdminMiddleware
{
    public function handle()
    {
        if (!ADMIN_GUARD()->isAdminLoggedIn()) {
            $_SESSION['errors']['login'] = 'Vui lòng đăng nhập với tư cách admin.';
            redirect('/admin/login');
        }
    }
}
