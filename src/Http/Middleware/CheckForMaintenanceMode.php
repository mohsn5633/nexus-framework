<?php

namespace Nexus\Http\Middleware;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Core\Application;
use Closure;

class CheckForMaintenanceMode
{
    public function __construct(
        protected Application $app
    ) {
    }

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maintenanceFile = $this->app->basePath('storage/framework/down');

        if (!file_exists($maintenanceFile)) {
            return $next($request);
        }

        $data = json_decode(file_get_contents($maintenanceFile), true);

        // Start session if not started
        $this->startSession();

        // Check if secret is stored in session
        if ($this->hasValidSessionSecret($data)) {
            return $next($request);
        }

        // Check if request has valid bypass secret
        if ($this->hasValidSecret($request, $data)) {
            // Store secret in session for future requests
            $this->storeSecretInSession($data['secret']);
            return $next($request);
        }

        // Check if IP is allowed (optional for future enhancement)
        if ($this->inExceptArray($request)) {
            return $next($request);
        }

        // Application is in maintenance mode
        return $this->renderMaintenancePage($data);
    }

    /**
     * Start session if not already started
     */
    protected function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Check if session has valid bypass secret
     */
    protected function hasValidSessionSecret(array $data): bool
    {
        if (!isset($_SESSION['maintenance_bypass_secret']) || !isset($data['secret'])) {
            return false;
        }

        return hash_equals($data['secret'], $_SESSION['maintenance_bypass_secret']);
    }

    /**
     * Store secret in session
     */
    protected function storeSecretInSession(string $secret): void
    {
        $_SESSION['maintenance_bypass_secret'] = $secret;
    }

    /**
     * Check if request has valid bypass secret
     */
    protected function hasValidSecret(Request $request, array $data): bool
    {
        $secret = $request->query('secret');

        if (!$secret || !isset($data['secret'])) {
            return false;
        }

        return hash_equals($data['secret'], $secret);
    }

    /**
     * Check if the request path is in the exception array
     */
    protected function inExceptArray(Request $request): bool
    {
        $except = [
            // Add paths that should be accessible during maintenance
            // 'health-check',
            // 'api/status',
        ];

        $path = $request->path();

        foreach ($except as $pattern) {
            if ($path === $pattern || str_starts_with($path, $pattern . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render the maintenance mode page
     */
    protected function renderMaintenancePage(array $data): Response
    {
        $viewPath = $this->app->basePath('app/Views/errors/503.php');

        if (file_exists($viewPath)) {
            ob_start();
            extract([
                'message' => $data['message'] ?? 'Service Unavailable',
                'retry' => $data['retry'] ?? 60,
            ]);
            include $viewPath;
            $content = ob_get_clean();

            return new Response($content, 503);
        }

        // Fallback response
        return new Response('Service Unavailable', 503);
    }
}
