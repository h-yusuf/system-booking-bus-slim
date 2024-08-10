<?php
/*
|--------------------------------------------------------------------------
| Web Routes - Tbusers
|--------------------------------------------------------------------------
| Here is where you can register web routes for your tbusers . 
*/
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Controllers\Admin\TbusersController;

$app->group('', function ($route) {
    $route->get('/tbusers', TbusersController::class .':index')->setName('tbusers.index');
    $route->get('/tbusers/datatable', TbusersController::class .':datatable')->setName('tbusers.datatable');
    $route->get('/tbusers/show/{id}', TbusersController::class .':show')->setName('tbusers.show');
    $route->get('/tbusers/create', TbusersController::class .':create')->setName('tbusers.create');
    $route->post('/tbusers/store', TbusersController::class .':store')->setName('tbusers.store');
    $route->get('/tbusers/edit/{id}', TbusersController::class .':edit')->setName('tbusers.edit');
    $route->post('/tbusers/update', TbusersController::class .':update')->setName('tbusers.update');
    $route->post('/tbusers/destroy/{id}', TbusersController::class .':destroy')->setName('tbusers.destroy');
    $route->get('/tbusers/delete/{id}', TbusersController::class .':deleteFile')->setName('tbusers.delete.file');
    $route->get('/tbusers/export/{type}', TbusersController::class .':export')->setName('tbusers.export');
})->add(new AuthMiddleware($container));
