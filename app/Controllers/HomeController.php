<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Http\Get;

class HomeController
{
    #[Get('/', 'home')]
    public function index(Request $request): Response
    {
        return Response::view('welcome', [
            'stats' => [
                'version' => '1.0.0',
                'php_version' => '8.3',
                'features' => '20+',
                'stars' => '1,000+',
            ]
        ]);
    }

    #[Get('/api/hello', 'api.hello')]
    public function apiHello(Request $request): Response
    {
        return Response::json([
            'message' => 'Hello from Nexus API!',
            'framework' => 'Nexus',
            'version' => '1.0.0'
        ]);
    }

    #[Get('/user/{id}', 'user.show')]
    public function showUser(Request $request): Response
    {
        $id = $request->route('id');

        return Response::json([
            'user_id' => $id,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    #[Get('/error/401')]
    public function test401(): never
    {
        abort(401, 'You need to log in to access this resource');
    }

    #[Get('/error/403')]
    public function test403(): never
    {
        abort(403, 'You do not have permission to access this resource');
    }

    #[Get('/error/500')]
    public function test500(): never
    {
        throw new \Exception('This is a test server error');
    }

    #[Get('/error/503')]
    public function test503(): never
    {
        abort(503, 'The service is temporarily unavailable for maintenance');
    }
}
