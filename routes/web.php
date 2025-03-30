<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Bramus\Router\Router;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Middleware\AdminMiddleware;

// Khởi tạo Twig
$loader = new FilesystemLoader(__DIR__ . '/../app/Views'); // Thư mục chứa file .twig
$twig = new Environment($loader, [
    'cache' => __DIR__ . '/../storage/cache', // Có thể đặt null để không cache
]);

// Khởi tạo router
$router = new Router();
// $router->before('GET|POST', '/admin/.*', [new AdminMiddleware(), 'handle']);
// Admin routes
$router->get('/admin/login', '\App\Controllers\Auth\AdminLoginController@create');
$router->post('/admin/login', '\App\Controllers\Auth\AdminLoginController@store');
$router->post('admin/logout', '\App\Controllers\auth\AdminLoginController@destroy');

// User routes
$router->get('/login', '\App\Controllers\Auth\LoginController@create');
$router->post('/login', '\App\Controllers\Auth\LoginController@store');
$router->post('/register', '\App\Controllers\Auth\RegisterController@store');
$router->post('/logout', '\App\Controllers\Auth\LoginController@destroy');

// Admin panel routes
$router->get('/admin/home', '\App\Controllers\Admin\HomeController@index');
// $router->get('/admin/categories', '\App\Controllers\Admin\CategoryController@index');
// $router->get('/admin/categories/create', '\App\Controllers\Admin\CategoryController@create');
// $router->post('/admin/categories/store', '\App\Controllers\Admin\CategoryController@store');
// $router->get('/admin/categories/edit/{id}', '\App\Controllers\Admin\CategoryController@edit');
// $router->post('/admin/categories/update/{id}', '\App\Controllers\Admin\CategoryController@update');
// $router->get('/admin/categories/delete/{id}', '\App\Controllers\Admin\CategoryController@delete');

// Xử lý trang 404
$router->set404('\App\Controllers\Controller@sendNotFound');

// Chạy router
$router->run();
