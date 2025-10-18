<?php

namespace Nexus\Http\Middleware;

use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class AuthMiddleware implements Middleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        // Validate token here
        // For example: verify JWT, check session, etc.

        return $next($request);
    }
}
