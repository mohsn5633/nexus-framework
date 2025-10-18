<?php

namespace Nexus\Core;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

class Container
{
    protected array $bindings = [];
    protected array $instances = [];
    protected array $aliases = [];

    /**
     * Bind a class or interface to the container
     */
    public function bind(string $abstract, Closure|string|null $concrete = null, bool $singleton = false): void
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton
        ];
    }

    /**
     * Bind a singleton instance
     */
    public function singleton(string $abstract, Closure|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Bind an existing instance to the container
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Create an alias for a binding
     */
    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }

    /**
     * Resolve a class from the container
     */
    public function make(string $abstract, array $parameters = []): mixed
    {
        $abstract = $this->getAlias($abstract);

        // Return existing singleton instance
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Get the concrete implementation
        $concrete = $this->getConcrete($abstract);

        // Build the object
        $object = $this->build($concrete, $parameters);

        // Store singleton instance
        if (isset($this->bindings[$abstract]['singleton']) && $this->bindings[$abstract]['singleton']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Get the concrete implementation
     */
    protected function getConcrete(string $abstract): Closure|string
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Build an instance of the given concrete type
     */
    protected function build(Closure|string $concrete, array $parameters = []): mixed
    {
        // If it's a closure, execute it
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw new \Exception("Target class [$concrete] does not exist.");
        }

        // Check if class is instantiable
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Target [$concrete] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        // If no constructor, return new instance
        if (is_null($constructor)) {
            return new $concrete;
        }

        // Resolve constructor dependencies
        $dependencies = $constructor->getParameters();
        $instances = $this->resolveDependencies($dependencies, $parameters);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve all dependencies for a method
     */
    protected function resolveDependencies(array $dependencies, array $parameters = []): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            // If parameter provided by name, use it
            if (array_key_exists($dependency->getName(), $parameters)) {
                $results[] = $parameters[$dependency->getName()];
                continue;
            }

            // Check if parameter provided by type
            $type = $dependency->getType();

            if ($type && !$type->isBuiltin()) {
                $typeName = $type->getName();

                // Check if an instance of this type was provided in parameters
                foreach ($parameters as $param) {
                    if (is_object($param) && $param instanceof $typeName) {
                        $results[] = $param;
                        continue 2;
                    }
                }

                // Otherwise, resolve from container
                $results[] = $this->make($typeName);
            } elseif ($dependency->isDefaultValueAvailable()) {
                $results[] = $dependency->getDefaultValue();
            } else {
                throw new \Exception("Unable to resolve dependency [{$dependency->getName()}]");
            }
        }

        return $results;
    }

    /**
     * Call a method with dependency injection
     */
    public function call(callable|array $callback, array $parameters = []): mixed
    {
        if (is_array($callback)) {
            [$class, $method] = $callback;

            if (is_string($class)) {
                $class = $this->make($class);
            }

            $reflector = new \ReflectionMethod($class, $method);
            $dependencies = $reflector->getParameters();
            $instances = $this->resolveDependencies($dependencies, $parameters);

            return $reflector->invokeArgs($class, $instances);
        }

        // Handle Closures and callables with dependency injection
        if ($callback instanceof \Closure) {
            $reflector = new \ReflectionFunction($callback);
            $dependencies = $reflector->getParameters();
            $instances = $this->resolveDependencies($dependencies, $parameters);

            return $reflector->invokeArgs($instances);
        }

        return $callback($this);
    }

    /**
     * Get the alias for an abstract
     */
    protected function getAlias(string $abstract): string
    {
        return $this->aliases[$abstract] ?? $abstract;
    }

    /**
     * Check if a binding exists
     */
    public function has(string $abstract): bool
    {
        $abstract = $this->getAlias($abstract);

        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * Remove a binding
     */
    public function forget(string $abstract): void
    {
        unset($this->bindings[$abstract], $this->instances[$abstract]);
    }
}
