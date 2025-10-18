<?php

namespace Nexus\Http\Middleware;

use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class JsonMiddleware implements Middleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        $response = $next($request);

        // Convert array responses to JSON
        if (is_array($response->getContent())) {
            return Response::json($response->getContent(), $response->getStatus());
        }

        return $response;
    }
}
