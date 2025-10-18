<?php

namespace Nexus\Http;

use Closure;

class RouteRegistrar
{
    protected ?string $name = null;

    public function __construct(
        protected string $method,
        protected string $path,
        protected Closure|array|string $action,
        protected array $middleware = []
    ) {
    }

    /**
     * Set the route name
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Add middleware to the route
     */
    public function middleware(string|array $middleware): self
    {
        $this->middleware = array_merge($this->middleware, (array) $middleware);
        return $this;
    }

    /**
     * Get the route action
     */
    public function getAction(): Closure|array|string
    {
        return $this->action;
    }

    /**
     * Get the route middleware
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Get the route name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the route path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get the route method
     */
    public function getMethod(): string
    {
        return $this->method;
    }
}
