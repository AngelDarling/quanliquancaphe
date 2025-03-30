<?php

namespace App\Controllers\Auth;

use App\Models\User;
use App\Controllers\Controller;

class LoginController extends Controller
{
    public function create()
    {
        if (USER_GUARD()->isUserLoggedIn()) {
            redirect('/home');
        }

        $data = [
            'messages' => session_get_once('messages'),
            'old' => $this->getSavedFormValues(),
            'errors' => session_get_once('errors')
        ];

        $this->sendPage('auth/user_login_register', $data); // Chỉnh sang file view gộp
    }

    public function store()
    {
        $user_credentials = $this->filterUserCredentials($_POST);

        $errors = [];
        $user = (new User(PDO()))->where('email', $user_credentials['email']);
        if (!$user) {
            $errors['email'] = 'Invalid email or password.';
        } else if (USER_GUARD()->login($user, $user_credentials)) {
            redirect('/home');
        } else {
            $errors['password'] = 'Invalid email or password.';
        }

        $this->saveFormValues($_POST, ['password']);
        redirect('/login', ['errors' => $errors]);
    }

    public function destroy()
    {
        USER_GUARD()->logout();
        redirect('/login');
    }

    protected function filterUserCredentials(array $data)
    {
        return [
            'email' => filter_var($data['email'], FILTER_VALIDATE_EMAIL),
            'password' => $data['password'] ?? null
        ];
    }
}
