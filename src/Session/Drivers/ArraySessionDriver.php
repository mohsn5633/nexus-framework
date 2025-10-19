<?php

namespace Nexus\Session\Drivers;

use Nexus\Session\SessionDriverInterface;

class ArraySessionDriver implements SessionDriverInterface
{
    protected array $storage = [];

    /**
     * Read session data
     */
    public function read(string $sessionId): array
    {
        return $this->storage[$sessionId] ?? [];
    }

    /**
     * Write session data
     */
    public function write(string $sessionId, array $data): bool
    {
        $this->storage[$sessionId] = $data;
        return true;
    }

    /**
     * Destroy a session
     */
    public function destroy(string $sessionId): bool
    {
        unset($this->storage[$sessionId]);
        return true;
    }

    /**
     * Garbage collection
     */
    public function gc(int $maxLifetime): bool
    {
        // Array driver doesn't need GC
        return true;
    }
}
