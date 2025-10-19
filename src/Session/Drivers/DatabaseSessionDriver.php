<?php

namespace Nexus\Session\Drivers;

use Nexus\Core\Config;
use Nexus\Database\Database;
use Nexus\Session\SessionDriverInterface;

class DatabaseSessionDriver implements SessionDriverInterface
{
    protected Database $db;
    protected string $table;
    protected int $lifetime;

    public function __construct(Config $config)
    {
        // Get database instance from container
        $this->db = app(Database::class);
        $this->table = $config->get('session.table', 'sessions');
        $this->lifetime = $config->get('session.lifetime', 120) * 60;
    }

    /**
     * Read session data
     */
    public function read(string $sessionId): array
    {
        $session = $this->db->table($this->table)
            ->where('id', '=', $sessionId)
            ->first();

        if (!$session) {
            return [];
        }

        // Check if session has expired
        if ($session['last_activity'] + $this->lifetime < time()) {
            $this->destroy($sessionId);
            return [];
        }

        $data = @unserialize($session['payload']);

        return is_array($data) ? $data : [];
    }

    /**
     * Write session data
     */
    public function write(string $sessionId, array $data): bool
    {
        $payload = serialize($data);

        $existing = $this->db->table($this->table)
            ->where('id', '=', $sessionId)
            ->first();

        if ($existing) {
            // Update existing session
            return $this->db->table($this->table)
                ->where('id', '=', $sessionId)
                ->update([
                    'payload' => $payload,
                    'last_activity' => time()
                ]) > 0;
        } else {
            // Insert new session
            return $this->db->table($this->table)
                ->insert([
                    'id' => $sessionId,
                    'payload' => $payload,
                    'last_activity' => time()
                ]) > 0;
        }
    }

    /**
     * Destroy a session
     */
    public function destroy(string $sessionId): bool
    {
        return $this->db->table($this->table)
            ->where('id', '=', $sessionId)
            ->delete() > 0;
    }

    /**
     * Garbage collection
     */
    public function gc(int $maxLifetime): bool
    {
        $expiration = time() - $maxLifetime;

        return $this->db->table($this->table)
            ->where('last_activity', '<', $expiration)
            ->delete() > 0;
    }
}
