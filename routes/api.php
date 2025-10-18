<?php

/** @var \Nexus\Http\Router $router */
$router = app('router');

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider.
|
*/

// API routes are typically prefixed with /api
$router->group(['prefix' => 'api'], function ($router) {

    // Example API endpoints
    // $router->get('/users', [App\Controllers\Api\UserController::class, 'index']);
    // $router->post('/users', [App\Controllers\Api\UserController::class, 'store']);
    // $router->get('/users/{id}', [App\Controllers\Api\UserController::class, 'show']);
    // $router->put('/users/{id}', [App\Controllers\Api\UserController::class, 'update']);
    // $router->delete('/users/{id}', [App\Controllers\Api\UserController::class, 'destroy']);

    // API with authentication middleware
    // $router->group(['middleware' => ['auth:api']], function ($router) {
    //     $router->get('/profile', [App\Controllers\Api\ProfileController::class, 'show']);
    //     $router->put('/profile', [App\Controllers\Api\ProfileController::class, 'update']);
    // });

});
