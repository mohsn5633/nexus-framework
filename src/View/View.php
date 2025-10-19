<?php

namespace Nexus\View;

use Nexus\Core\Application;

class View
{
    protected static ?ViewCompiler $compiler = null;

    /**
     * Render a view
     */
    public static function make(string $view, array $data = []): string
    {
        $compiler = static::getCompiler();

        $viewPath = static::findView($view);

        if (!$viewPath) {
            throw new \Exception("View [{$view}] not found.");
        }

        return $compiler->compile($viewPath, $data);
    }

    /**
     * Check if a view exists
     */
    public static function exists(string $view): bool
    {
        return static::findView($view) !== null;
    }

    /**
     * Find a view file
     */
    protected static function findView(string $view): ?string
    {
        $app = Application::getInstance();

        // Check if this is a package view (e.g., 'authentication::login')
        if (str_contains($view, '::')) {
            [$package, $viewPath] = explode('::', $view, 2);

            // Convert dot notation to path
            $path = str_replace('.', '/', $viewPath);

            // Capitalize first letter of package name for directory
            $packageDir = ucfirst($package);

            // Check for .blade.php extension in package
            $bladePath = $app->basePath("packages/{$packageDir}/views/{$path}.blade.php");
            if (file_exists($bladePath)) {
                return $bladePath;
            }

            // Check for .php extension in package
            $phpPath = $app->basePath("packages/{$packageDir}/views/{$path}.php");
            if (file_exists($phpPath)) {
                return $phpPath;
            }

            return null;
        }

        // Convert dot notation to path
        $path = str_replace('.', '/', $view);

        // Check for .blade.php extension
        $bladePath = $app->basePath("app/Views/{$path}.blade.php");
        if (file_exists($bladePath)) {
            return $bladePath;
        }

        // Check for .php extension
        $phpPath = $app->basePath("app/Views/{$path}.php");
        if (file_exists($phpPath)) {
            return $phpPath;
        }

        return null;
    }

    /**
     * Get the view compiler instance
     */
    protected static function getCompiler(): ViewCompiler
    {
        if (static::$compiler === null) {
            $app = Application::getInstance();
            $cachePath = $app->basePath('storage/framework/views');
            static::$compiler = new ViewCompiler($cachePath);
        }

        return static::$compiler;
    }

    /**
     * Clear the view cache
     */
    public static function clearCache(): void
    {
        $compiler = static::getCompiler();
        $compiler->clearCache();
    }
}
