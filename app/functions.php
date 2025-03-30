<?php

if (!function_exists('PDO')) {
    function PDO(): PDO
    {
        global $PDO;
        return $PDO;
    }
}

if (!function_exists('ADMIN_GUARD')) {
    function ADMIN_GUARD(): App\AdminSessionGuard
    {
        global $ADMIN_GUARD;
        if (!isset($ADMIN_GUARD)) {
            $ADMIN_GUARD = new App\AdminSessionGuard();
        }
        return $ADMIN_GUARD;
    }
}

if (!function_exists('USER_GUARD')) {
    function USER_GUARD(): App\UserSessionGuard
    {
        global $USER_GUARD;
        if (!isset($USER_GUARD)) {
            $USER_GUARD = new App\UserSessionGuard();
        }
        return $USER_GUARD;
    }
}

if (!function_exists('dd')) {
    function dd($var)
    {
        var_dump($var);
        exit();
    }
}

if (!function_exists('redirect')) {
    // Chuyển hướng đến một trang khác
    function redirect($location, array $data = [])
    {
        foreach ($data as $key => $value) {
            $_SESSION[$key] = $value;
        }

        header('Location: ' . $location, true, 302);
        exit();
    }
}

if (!function_exists('session_get_once')) {
    // Đọc và xóa một biến trong $_SESSION
    function session_get_once($name, $default = null)
    {
        $value = $default;
        if (isset($_SESSION[$name])) {
            $value = $_SESSION[$name];
            unset($_SESSION[$name]);
        }
        return $value;
    }
}
