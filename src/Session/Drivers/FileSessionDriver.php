<?php

namespace Nexus\Session\Drivers;

use Nexus\Core\Config;
use Nexus\Session\SessionDriverInterface;

class FileSessionDriver implements SessionDriverInterface
{
    protected string $path;
    protected int $lifetime;

    public function __construct(Config $config)
    {
        $this->path = $config->get('session.files');
        $this->lifetime = $config->get('session.lifetime', 120) * 60;

        // Ensure directory exists
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    /**
     * Read session data
     */
    public function read(string $sessionId): array
    {
        $file = $this->getFilePath($sessionId);

        if (!file_exists($file)) {
            return [];
        }

        // Check if session has expired
        if (filemtime($file) + $this->lifetime < time()) {
            $this->destroy($sessionId);
            return [];
        }

        $data = file_get_contents($file);

        if ($data === false) {
            return [];
        }

        $unserialized = @unserialize($data);

        return is_array($unserialized) ? $unserialized : [];
    }

    /**
     * Write session data
     */
    public function write(string $sessionId, array $data): bool
    {
        $file = $this->getFilePath($sessionId);

        $serialized = serialize($data);

        return file_put_contents($file, $serialized, LOCK_EX) !== false;
    }

    /**
     * Destroy a session
     */
    public function destroy(string $sessionId): bool
    {
        $file = $this->getFilePath($sessionId);

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    /**
     * Garbage collection
     */
    public function gc(int $maxLifetime): bool
    {
        $files = glob($this->path . '/sess_*');

        if ($files === false) {
            return false;
        }

        $now = time();

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) + $maxLifetime < $now) {
                @unlink($file);
            }
        }

        return true;
    }

    /**
     * Get the file path for a session
     */
    protected function getFilePath(string $sessionId): string
    {
        return $this->path . '/sess_' . $sessionId;
    }
}
