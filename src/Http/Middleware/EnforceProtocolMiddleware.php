<?php

namespace Nexus\Http\Middleware;

use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

/**
 * Enforce Protocol Middleware
 *
 * Enforces HTTP or HTTPS protocol based on configuration
 */
class EnforceProtocolMiddleware extends Middleware
{
    /**
     * Handle the request
     */
    public function handle(Request $request, \Closure $next): Response
    {
        $requiredProtocol = config('app.protocol', 'auto');

        // Auto mode - no enforcement
        if ($requiredProtocol === 'auto') {
            return $next($request);
        }

        $isSecure = $request->isSecure();

        // Enforce HTTPS
        if ($requiredProtocol === 'https' && !$isSecure) {
            return $this->redirectToHttps($request);
        }

        // Enforce HTTP (for development)
        if ($requiredProtocol === 'http' && $isSecure) {
            return $this->redirectToHttp($request);
        }

        return $next($request);
    }

    /**
     * Redirect to HTTPS
     */
    protected function redirectToHttps(Request $request): Response
    {
        $url = 'https://' . $request->getHost() . $request->getRequestUri();

        return Response::redirect($url, 301);
    }

    /**
     * Redirect to HTTP
     */
    protected function redirectToHttp(Request $request): Response
    {
        $url = 'http://' . $request->getHost() . $request->getRequestUri();

        return Response::redirect($url, 301);
    }
}
