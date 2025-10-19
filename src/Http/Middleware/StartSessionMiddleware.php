<?php

namespace Nexus\Http\Middleware;

use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Session\SessionManager;

class StartSessionMiddleware extends Middleware
{
    public function __construct(
        protected SessionManager $session
    ) {
    }

    /**
     * Handle the incoming request
     */
    public function handle(Request $request, \Closure $next): Response
    {
        // Start the session
        $this->session->start();

        // Set the session cookie
        $this->session->setCookie();

        // Process the request
        $response = $next($request);

        // Save the session data
        $this->session->save();

        return $response;
    }
}
