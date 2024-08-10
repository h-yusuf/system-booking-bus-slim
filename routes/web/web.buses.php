<?php
/*
|--------------------------------------------------------------------------
| Web Routes - Buses
|--------------------------------------------------------------------------
| Here is where you can register web routes for your buses . 
*/

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Controllers\Admin\BusesController;

$app->group('', function ($route) {
    $route->get('/buses', BusesController::class . ':index')->setName('buses.index');
    $route->get('/api/buses', BusesController::class . ':indexApi')->setName('buses.indexApi');
    $route->get('/buses/datatable', BusesController::class . ':datatable')->setName('buses.datatable');
    $route->get('/buses/show/{id}', BusesController::class . ':show')->setName('buses.show');
    $route->get('/api/buses/show/{id}', BusesController::class . ':showApi')->setName('buses.show');
    $route->get('/buses/create', BusesController::class . ':create')->setName('buses.create');
    $route->post('/buses/store', BusesController::class . ':store')->setName('buses.store');
    $route->post('/api/busesCreate', BusesController::class . ':storeApi')->setName('buses.storeApi');
    $route->get('/buses/edit/{id}', BusesController::class . ':edit')->setName('buses.edit');
    $route->post('/buses/update', BusesController::class . ':update')->setName('buses.update');
    $route->post('/buses/destroy/{id}', BusesController::class . ':destroy')->setName('buses.destroy');
    $route->get('/buses/delete/{id}', BusesController::class . ':deleteFile')->setName('buses.delete.file');
    $route->get('/buses/export/{type}', BusesController::class . ':export')->setName('buses.export');
})->add(new AuthMiddleware($container));

$app->group('', function ($route) {
$route->get('/api/busesUser', BusesController::class . ':indexApi')->setName('buses.busesUser');
});