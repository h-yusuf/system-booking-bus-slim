<?php
/*
|--------------------------------------------------------------------------
| Web Routes - Bookings
|--------------------------------------------------------------------------
| Here is where you can register web routes for your bookings . 
*/

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Controllers\Admin\BookingsController;

$app->group('', function ($route) {
    $route->get('/bookings', BookingsController::class . ':index')->setName('bookings.index');
    $route->get('/bookings/datatable', BookingsController::class . ':datatable')->setName('bookings.datatable');
    $route->get('/bookings/show/{id}', BookingsController::class . ':show')->setName('bookings.show');
    $route->get('/bookings/create', BookingsController::class . ':create')->setName('bookings.create');
    $route->post('/bookings/store', BookingsController::class . ':store')->setName('bookings.store');
    $route->post('/api/createBookings', BookingsController::class . ':storeApi')->setName('bookings.storeApi');
    $route->get('/bookings/edit/{id}', BookingsController::class . ':edit')->setName('bookings.edit');
    $route->post('/bookings/update', BookingsController::class . ':update')->setName('bookings.update');
    $route->post('/bookings/destroy/{id}', BookingsController::class . ':destroy')->setName('bookings.destroy');
    $route->get('/bookings/delete/{id}', BookingsController::class . ':deleteFile')->setName('bookings.delete.file');
    $route->get('/bookings/export/{type}', BookingsController::class . ':export')->setName('bookings.export');
})->add(new AuthMiddleware($container));

$app->group('', function ($route) {
    $route->get('/api/bookings', BookingsController::class . ':indexApi')->setName('bookings.indexApi');
    $route->get('/api/bookings/show/{id}', BookingsController::class . ':showApi')->setName('bookings.showApi');
});
