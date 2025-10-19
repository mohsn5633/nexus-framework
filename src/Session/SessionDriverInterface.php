<?php

namespace Nexus\Session;

interface SessionDriverInterface
{
    /**
     * Read session data
     */
    public function read(string $sessionId): array;

    /**
     * Write session data
     */
    public function write(string $sessionId, array $data): bool;

    /**
     * Destroy a session
     */
    public function destroy(string $sessionId): bool;

    /**
     * Garbage collection
     */
    public function gc(int $maxLifetime): bool;
}
