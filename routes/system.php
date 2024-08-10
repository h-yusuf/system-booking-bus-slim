<?php

use App\Controllers\Auth\AuthController;
use App\Controllers\Auth\PasswordController;
use App\Controllers\HomeController;
use App\Controllers\System\ProfileController;
use App\Controllers\System\UsersController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Controllers\Admin\PaymentsController;


$app->get('/', HomeController::class . ':index')->setName('index');
$app->get('/api/webhook', PaymentsController::class . ':webhook')->setName('webhook');
$app->get('/success', HomeController::class . ':success')->setName('success');
$app->get('/failure', HomeController::class . ':failure')->setName('failure');
$app->get('/api', HomeController::class . ':api')->setName('api');
$app->post('/api/login', AuthController::class . ':loginApi');
$app->post('/api/logout', AuthController::class . ':logoutApi');
$app->post('/api/register', AuthController::class . ':registerApi');
$app->post('/api/roleuser', AuthController::class . ':roleApi');
$app->get('/api/user', AuthController::class . ':detailUserApi');

$app->group('', function ($route) {
    $route->get('/register', AuthController::class . ':createRegister')->setName('register');
    $route->post('/register', AuthController::class . ':register');
    $route->get('/login', AuthController::class . ':createLogin')->setName('login');
    $route->post('/login', AuthController::class . ':login');
    // $route->post('/api/login', AuthController::class . ':loginApi');

    $route->get('/verify-email', AuthController::class . ':verifyEmail')->setName('verify.email');
    $route->get('/verify-email-resend', AuthController::class . ':verifyEmailResend')->setName('verify.email.resend');

    $route->get('/forgot-password', PasswordController::class . ':createForgotPassword')->setName('forgot.password');
    $route->post('/forgot-password', PasswordController::class . ':forgotPassword');
    $route->get('/reset-password', PasswordController::class . ':resetPassword')->setName('reset.password');
    $route->get('/update-password', PasswordController::class . ':createUpdatePassword')->setName('update.password');
    $route->post('/update-password', PasswordController::class . ':updatePassword');
})->add(new GuestMiddleware($container));


$app->group('', function ($route) {
    $route->get('/dashboard', HomeController::class . ':dashboard')->setName('home');
    $route->get('/logout', AuthController::class . ':logout')->setName('logout');
    //testing
    //$route->get('/test-data', HomeController::class .':creatTestData')->setName('test-data');
    //profile
    $route->get('/profile', ProfileController::class . ':index')->setName('profile');
    $route->post('/profile', ProfileController::class . ':update');
    $route->get('/change-password', ProfileController::class . ':createChangePassword')->setName('change.password');
    $route->post('/change-password', ProfileController::class . ':changePassword');
})->add(new AuthMiddleware($container));


$app->group('/admin', function ($route) {
    //users
    $route->get('/users', UsersController::class . ':index')->setName('user.index');
    $route->get('/users/datatable', UsersController::class . ':datatable')->setName('user.datatable');
    $route->get('/users/show/{id}', UsersController::class . ':show')->setName('user.show');
    $route->get('/users/create', UsersController::class . ':create')->setName('user.create');
    $route->post('/users/store', UsersController::class . ':store')->setName('user.store');
    $route->get('/users/edit/{id}', UsersController::class . ':edit')->setName('user.edit');
    $route->post('/users/update', UsersController::class . ':update')->setName('user.update');
    $route->post('/users/destroy/{id}', UsersController::class . ':destroy')->setName('user.destroy');
    $route->get('/users/password/{id}', UsersController::class . ':createPassword')->setName('user.password');
    $route->post('/users/password', UsersController::class . ':password')->setName('user.password.store');
    $route->get('/users/export/{type}', UsersController::class . ':export')->setName('user.export');
})->add(new AuthMiddleware($container));
