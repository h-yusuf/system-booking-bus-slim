<?php
/*
|--------------------------------------------------------------------------
| Web Routes - Payments
|--------------------------------------------------------------------------
| Here is where you can register web routes for your payments . 
*/

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Controllers\Admin\PaymentsController;

$app->group('', function ($route) {
    $route->get('/payments', PaymentsController::class . ':index')->setName('payments.index');
    $route->get('/api/payments', PaymentsController::class . ':indexApi')->setName('payments.indexApi');
    $route->get('/payments/datatable', PaymentsController::class . ':datatable')->setName('payments.datatable');
    $route->get('/payments/show/{id}', PaymentsController::class . ':show')->setName('payments.show');
    $route->get('/payments/create', PaymentsController::class . ':create')->setName('payments.create');
    $route->post('/payments/store', PaymentsController::class . ':store')->setName('payments.store');
    $route->get('/payments/edit/{id}', PaymentsController::class . ':edit')->setName('payments.edit');
    $route->post('/payments/update', PaymentsController::class . ':update')->setName('payments.update');
    $route->post('/payments/destroy/{id}', PaymentsController::class . ':destroy')->setName('payments.destroy');
    $route->get('/payments/delete/{id}', PaymentsController::class . ':deleteFile')->setName('payments.delete.file');
    $route->get('/payments/export/{type}', PaymentsController::class . ':export')->setName('payments.export');
})->add(new AuthMiddleware($container));
