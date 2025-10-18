<?php

/** @var \Nexus\Http\Router $router */
$router = app('router');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider.
|
*/

// Homepage
$router->get('/', [App\Controllers\HomeController::class, 'index']);

// Example routes with middleware
// $router->get('/dashboard', [App\Controllers\DashboardController::class, 'index'])
//     ->middleware(['auth']);

// Example routes with parameters
// $router->get('/users/{id}', [App\Controllers\UserController::class, 'show']);
// $router->get('/posts/{slug}', [App\Controllers\PostController::class, 'show']);
