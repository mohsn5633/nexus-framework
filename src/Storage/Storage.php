<?php

namespace Nexus\Storage;

use RuntimeException;

class Storage
{
    protected static ?string $defaultDisk = null;
    protected static array $disks = [];

    public function __construct(
        protected string $disk,
        protected array $config
    ) {
    }

    public static function disk(?string $name = null): static
    {
        $name = $name ?? static::getDefaultDisk();

        if (!isset(static::$disks[$name])) {
            $config = config("filesystems.disks.{$name}");

            if (!$config) {
                throw new RuntimeException("Disk [{$name}] does not have a configured disk.");
            }

            static::$disks[$name] = new static($name, $config);
        }

        return static::$disks[$name];
    }

    public static function getDefaultDisk(): string
    {
        return static::$defaultDisk ?? config('filesystems.default', 'local');
    }

    public static function setDefaultDisk(string $disk): void
    {
        static::$defaultDisk = $disk;
    }

    public function put(string $path, mixed $contents, array $options = []): bool|string
    {
        $fullPath = $this->path($path);
        $directory = dirname($fullPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $result = file_put_contents($fullPath, $contents);

        return $result !== false ? $path : false;
    }

    public function putFile(string $path, mixed $file, array $options = []): bool|string
    {
        if (is_array($file) && isset($file['tmp_name'])) {
            // Handle $_FILES array
            return $this->putUploadedFile($path, $file, $options);
        }

        return $this->put($path, file_get_contents($file), $options);
    }

    protected function putUploadedFile(string $path, array $file, array $options = []): bool|string
    {
        $name = $options['name'] ?? $file['name'];
        $fullPath = $this->path($path . '/' . $name);
        $directory = dirname($fullPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            return $path . '/' . $name;
        }

        return false;
    }

    public function putFileAs(string $path, mixed $file, string $name, array $options = []): bool|string
    {
        if (is_array($file) && isset($file['tmp_name'])) {
            $file['name'] = $name;
            return $this->putUploadedFile($path, $file, $options);
        }

        return $this->put($path . '/' . $name, file_get_contents($file), $options);
    }

    public function get(string $path): string|false
    {
        $fullPath = $this->path($path);

        if (!$this->exists($path)) {
            return false;
        }

        return file_get_contents($fullPath);
    }

    public function exists(string $path): bool
    {
        return file_exists($this->path($path));
    }

    public function missing(string $path): bool
    {
        return !$this->exists($path);
    }

    public function delete(string|array $paths): bool
    {
        $paths = is_array($paths) ? $paths : func_get_args();
        $success = true;

        foreach ($paths as $path) {
            $fullPath = $this->path($path);

            if (!file_exists($fullPath)) {
                continue;
            }

            if (!unlink($fullPath)) {
                $success = false;
            }
        }

        return $success;
    }

    public function copy(string $from, string $to): bool
    {
        $fromPath = $this->path($from);
        $toPath = $this->path($to);

        if (!file_exists($fromPath)) {
            return false;
        }

        $directory = dirname($toPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return copy($fromPath, $toPath);
    }

    public function move(string $from, string $to): bool
    {
        $fromPath = $this->path($from);
        $toPath = $this->path($to);

        if (!file_exists($fromPath)) {
            return false;
        }

        $directory = dirname($toPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return rename($fromPath, $toPath);
    }

    public function size(string $path): int|false
    {
        $fullPath = $this->path($path);

        if (!file_exists($fullPath)) {
            return false;
        }

        return filesize($fullPath);
    }

    public function lastModified(string $path): int|false
    {
        $fullPath = $this->path($path);

        if (!file_exists($fullPath)) {
            return false;
        }

        return filemtime($fullPath);
    }

    public function files(?string $directory = null): array
    {
        $directory = $this->path($directory ?? '');

        if (!is_dir($directory)) {
            return [];
        }

        $files = [];
        $items = scandir($directory);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_file($fullPath)) {
                $files[] = $item;
            }
        }

        return $files;
    }

    public function allFiles(?string $directory = null): array
    {
        $directory = $this->path($directory ?? '');

        if (!is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $files[] = $item->getPathname();
            }
        }

        return $files;
    }

    public function directories(?string $directory = null): array
    {
        $directory = $this->path($directory ?? '');

        if (!is_dir($directory)) {
            return [];
        }

        $directories = [];
        $items = scandir($directory);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($fullPath)) {
                $directories[] = $item;
            }
        }

        return $directories;
    }

    public function makeDirectory(string $path): bool
    {
        $fullPath = $this->path($path);

        if (is_dir($fullPath)) {
            return true;
        }

        return mkdir($fullPath, 0755, true);
    }

    public function deleteDirectory(string $directory): bool
    {
        $fullPath = $this->path($directory);

        if (!is_dir($fullPath)) {
            return false;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }

        return rmdir($fullPath);
    }

    public function url(string $path): string
    {
        if (isset($this->config['url'])) {
            return rtrim($this->config['url'], '/') . '/' . ltrim($path, '/');
        }

        return $path;
    }

    public function path(string $path = ''): string
    {
        $root = $this->config['root'] ?? '';

        if (empty($path)) {
            return $root;
        }

        return rtrim($root, '/\\') . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
    }

    public function mimeType(string $path): string|false
    {
        $fullPath = $this->path($path);

        if (!file_exists($fullPath)) {
            return false;
        }

        return mime_content_type($fullPath);
    }

    public function extension(string $path): string|false
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    public function __call(string $method, array $parameters): mixed
    {
        return static::disk()->{$method}(...$parameters);
    }

    public static function __callStatic(string $method, array $parameters): mixed
    {
        return static::disk()->{$method}(...$parameters);
    }
}
