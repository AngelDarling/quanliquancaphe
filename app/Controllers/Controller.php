<?php

namespace App\Controllers;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Controller
{
    protected $view;

    public function __construct()
    {
        $loader = new FilesystemLoader(ROOTDIR . 'app/views');
        $this->view = new Environment($loader, [
            'auto_reload' => true, // Tự động reload khi file thay đổi
        ]);
    }

    public function sendPage($page, array $data = [])
    {
        // Luôn truyền CSRF token vào mọi trang
        $data['csrf_token'] = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
        exit($this->view->render($page . '.twig', $data));
    }

    // Lưu các giá trị của form được cho trong $data vào $_SESSION 
    protected function saveFormValues(array $data, array $except = [])
    {
        $form = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, $except, true)) {
                $form[$key] = $value;
            }
        }
        $_SESSION['form'] = $form;
    }

    protected function getSavedFormValues()
    {
        return session_get_once('form', []);
    }

    public function sendNotFound()
    {
        http_response_code(404);

        try {
            exit($this->view->render('errors/404'));
        } catch (\Twig\Error\LoaderError $e) {
            exit('<h1>404 - Not Found</h1>');
        }
    }
}
