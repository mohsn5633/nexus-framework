<?php

namespace Nexus\Support;

class View
{
    public function __construct(
        protected string $viewsPath
    ) {
    }

    /**
     * Render a view
     */
    public function render(string $name, array $data = []): string
    {
        $file = $this->findView($name);

        if (!$file) {
            throw new \Exception("View [$name] not found.");
        }

        return $this->renderFile($file, $data);
    }

    /**
     * Find a view file
     */
    protected function findView(string $name): ?string
    {
        // Convert dot notation to path
        $path = str_replace('.', DIRECTORY_SEPARATOR, $name);

        // Try .php extension
        $file = $this->viewsPath . DIRECTORY_SEPARATOR . $path . '.php';
        if (file_exists($file)) {
            return $file;
        }

        // Try .html extension
        $file = $this->viewsPath . DIRECTORY_SEPARATOR . $path . '.html';
        if (file_exists($file)) {
            return $file;
        }

        return null;
    }

    /**
     * Render a file with data
     */
    protected function renderFile(string $file, array $data): string
    {
        extract($data);

        ob_start();
        include $file;
        return ob_get_clean();
    }

    /**
     * Check if a view exists
     */
    public function exists(string $name): bool
    {
        return $this->findView($name) !== null;
    }
}
