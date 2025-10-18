<?php

namespace Nexus\Http;

interface Middleware
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, \Closure $next): Response;
}
