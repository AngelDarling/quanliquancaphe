<?php

namespace App;

use App\Models\Admin;

class AdminSessionGuard
{
    protected $admin;

    public function login(Admin $admin, array $credentials)
    {
        if (!$admin || !$admin->password) {
            return false;
        }

        $verified = password_verify($credentials['password'], $admin->password);
        if ($verified) {
            $_SESSION['admin_id'] = $admin->id;
        }
        return $verified;
    }

    public function admin()
    {
        if (!$this->admin && $this->isAdminLoggedIn()) {
            $this->admin = (new Admin(PDO()))->where('id', $_SESSION['admin_id']);
        }
        return $this->admin;
    }

    public function logout()
    {
        $this->admin = null;
        session_unset();
        session_destroy();
    }
    public function isAdminLoggedIn(): bool
    {
        return isset($_SESSION['admin_id']);
    }
}
