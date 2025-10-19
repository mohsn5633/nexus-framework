<?php
namespace Nexus\Http;

use Nexus\Core\Container;
use Nexus\Http\Exceptions\NotFoundException;
use Closure;

class Router
{
    protected array $routes = [];
    protected array $namedRoutes = [];
    protected array $middleware = [];
    protected array $middlewareAliases = [];
    protected array $groupStack = [];

    public function __construct(
        protected Container $container
    ) {
    }

    /**
     * Register a middleware alias
     */
    public function middleware(string $name, string $class): void
    {
        $this->middlewareAliases[$name] = $class;
    }

    /**
     * Get middleware class by alias
     */
    public function getMiddleware(string $name): ?string
    {
        return $this->middlewareAliases[$name] ?? null;
    }

    /**
     * Resolve middleware (supports both aliases and class names)
     */
    public function resolveMiddleware(string $middleware): string
    {
        return $this->middlewareAliases[$middleware] ?? $middleware;
    }

    /**
     * Register a GET route
     */
    public function get(string $path, Closure|array|string $action): RouteRegistrar
    {
        return $this->addRoute('GET', $path, $action);
    }

    /**
     * Register a POST route
     */
    public function post(string $path, Closure|array|string $action): RouteRegistrar
    {
        return $this->addRoute('POST', $path, $action);
    }

    /**
     * Register a PUT route
     */
    public function put(string $path, Closure|array|string $action): RouteRegistrar
    {
        return $this->addRoute('PUT', $path, $action);
    }

    /**
     * Register a PATCH route
     */
    public function patch(string $path, Closure|array|string $action): RouteRegistrar
    {
        return $this->addRoute('PATCH', $path, $action);
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $path, Closure|array|string $action): RouteRegistrar
    {
        return $this->addRoute('DELETE', $path, $action);
    }

    /**
     * Register a route for any HTTP method
     */
    public function any(string $path, Closure|array|string $action): RouteRegistrar
    {
        return $this->addRoute('ANY', $path, $action);
    }

    /**
     * Add a route to the collection
     */
    protected function addRoute(string $method, string $path, Closure|array|string $action): RouteRegistrar
    {
        $path = $this->prefix($path);
        $middleware = $this->gatherMiddleware();

        $route = new RouteRegistrar($method, $path, $action, $middleware);
        $this->routes[$method][$path] = $route;

        return $route;
    }

    /**
     * Create a route group
     */
    public function group(array $attributes, Closure $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    /**
     * Apply prefix from group stack
     */
    protected function prefix(string $path): string
    {
        $prefix = '';

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
        }

        return $prefix . '/' . trim($path, '/');
    }

    /**
     * Gather middleware from group stack
     */
    protected function gatherMiddleware(): array
    {
        $middleware = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['middleware'])) {
                $middleware = array_merge($middleware, (array) $group['middleware']);
            }
        }

        return $middleware;
    }

    /**
     * Auto-discover routes from controller attributes
     */
    public function discoverRoutes(string $controllerNamespace, string $controllersPath): void
    {
        $files = glob($controllersPath . '/*.php');

        foreach ($files as $file) {
            $className = $controllerNamespace . '\\' . basename($file, '.php');

            if (!class_exists($className)) {
                continue;
            }

            $reflection = new \ReflectionClass($className);

            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                // Use IS_INSTANCEOF flag to find Route and all its subclasses (Get, Post, etc.)
                $attributes = $method->getAttributes(Route::class, \ReflectionAttribute::IS_INSTANCEOF);

                foreach ($attributes as $attribute) {
                    $route = $attribute->newInstance();
                    $registrar = $this->addRoute(
                        $route->method,
                        $route->path,
                        [$className, $method->getName()]
                    );

                    if ($route->name) {
                        $registrar->name($route->name);
                    }

                    if (!empty($route->middleware)) {
                        $registrar->middleware($route->middleware);
                    }
                }
            }
        }
    }

    /**
     * Dispatch the current request
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = '/' . trim($request->path(), '/');

        // Try exact match
        if (isset($this->routes[$method][$path])) {
            return $this->runRoute($request, $this->routes[$method][$path]);
        }

        // Try ANY method
        if (isset($this->routes['ANY'][$path])) {
            return $this->runRoute($request, $this->routes['ANY'][$path]);
        }

        // Try pattern matching
        foreach ($this->routes[$method] ?? [] as $routePath => $route) {
            if ($params = $this->matchRoute($routePath, $path)) {
                $request->setRouteParams($params);
                return $this->runRoute($request, $route);
            }
        }

        // Try ANY method with patterns
        foreach ($this->routes['ANY'] ?? [] as $routePath => $route) {
            if ($params = $this->matchRoute($routePath, $path)) {
                $request->setRouteParams($params);
                return $this->runRoute($request, $route);
            }
        }

        throw new NotFoundException("Route not found: {$method} {$path}");
    }

    /**
     * Match a route pattern with path
     */
    protected function matchRoute(string $pattern, string $path): array|false
    {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $path, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return false;
    }

    /**
     * Run a route
     */
    protected function runRoute(Request $request, RouteRegistrar $route): Response
    {
        // Execute middleware pipeline
        $pipeline = array_reduce(
            array_reverse($route->getMiddleware()),
            fn($next, $middleware) => fn($req) => $this->container->make($this->resolveMiddleware($middleware))->handle($req, $next),
            fn($req) => $this->executeAction($req, $route->getAction())
        );

        $result = $pipeline($request);

        return $result instanceof Response ? $result : new Response($result);
    }

    /**
     * Execute the route action
     */
    protected function executeAction(Request $request, Closure|array|string $action): mixed
    {
        if ($action instanceof Closure) {
            return $this->container->call($action, ['request' => $request]);
        }

        if (is_string($action) && str_contains($action, '@')) {
            $action = explode('@', $action);
        }

        if (is_array($action)) {
            [$controller, $method] = $action;
            $controller = is_string($controller) ? $this->container->make($controller) : $controller;

            return $this->container->call([$controller, $method], ['request' => $request]);
        }

        throw new \Exception("Invalid route action");
    }

    /**
     * Get all registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
