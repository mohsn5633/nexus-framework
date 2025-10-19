<?php

namespace Nexus\Session;

use Nexus\Core\Config;
use Nexus\Session\Drivers\FileSessionDriver;
use Nexus\Session\Drivers\DatabaseSessionDriver;
use Nexus\Session\Drivers\ArraySessionDriver;

class SessionManager
{
    protected ?SessionDriverInterface $driver = null;
    protected string $sessionId;
    protected array $data = [];
    protected bool $started = false;
    protected array $flashData = [];

    public function __construct(
        protected Config $config
    ) {
    }

    /**
     * Start the session
     */
    public function start(): bool
    {
        if ($this->started) {
            return true;
        }

        $this->driver = $this->createDriver();
        $this->sessionId = $this->generateSessionId();

        // Load existing session data
        $this->data = $this->driver->read($this->sessionId);

        // Process flash data
        $this->ageFlashData();

        $this->started = true;

        return true;
    }

    /**
     * Get a session value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Set a session value
     */
    public function put(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Check if session has a key
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]) && $this->data[$key] !== null;
    }

    /**
     * Remove a session value
     */
    public function forget(string $key): void
    {
        unset($this->data[$key]);
    }

    /**
     * Flash data for the next request
     */
    public function flash(string $key, mixed $value): void
    {
        $this->put($key, $value);
        $this->put('_flash.new.' . $key, true);
    }

    /**
     * Reflash all flash data
     */
    public function reflash(): void
    {
        $this->mergeNewFlashes($this->data);
        $this->put('_flash.old', []);
    }

    /**
     * Keep specific flash keys
     */
    public function keep(array $keys): void
    {
        foreach ($keys as $key) {
            $this->put('_flash.new.' . $key, true);
        }
    }

    /**
     * Get all session data
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Check if the session has been started
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * Get the session ID
     */
    public function getId(): string
    {
        return $this->sessionId;
    }

    /**
     * Set the session ID
     */
    public function setId(string $id): void
    {
        $this->sessionId = $id;
    }

    /**
     * Regenerate the session ID
     */
    public function regenerate(bool $destroy = false): bool
    {
        if ($destroy) {
            $this->driver->destroy($this->sessionId);
        }

        $this->sessionId = $this->generateSessionId();

        return true;
    }

    /**
     * Save the session data
     */
    public function save(): void
    {
        if (!$this->started) {
            return;
        }

        $this->ageFlashData();
        $this->driver->write($this->sessionId, $this->data);
        $this->started = false;
    }

    /**
     * Destroy the session
     */
    public function destroy(): bool
    {
        $this->data = [];
        $this->driver->destroy($this->sessionId);
        $this->started = false;

        return true;
    }

    /**
     * Flush all session data
     */
    public function flush(): void
    {
        $this->data = [];
    }

    /**
     * Get the CSRF token
     */
    public function token(): string
    {
        if (!$this->has('_token')) {
            $this->regenerateToken();
        }

        return $this->get('_token');
    }

    /**
     * Regenerate the CSRF token
     */
    public function regenerateToken(): void
    {
        $this->put('_token', bin2hex(random_bytes(32)));
    }

    /**
     * Create the session driver
     */
    protected function createDriver(): SessionDriverInterface
    {
        $driver = $this->config->get('session.driver', 'file');

        return match ($driver) {
            'file' => new FileSessionDriver($this->config),
            'database' => new DatabaseSessionDriver($this->config),
            'array' => new ArraySessionDriver(),
            default => throw new \InvalidArgumentException("Unsupported session driver [{$driver}]")
        };
    }

    /**
     * Generate a new session ID
     */
    protected function generateSessionId(): string
    {
        // Check for existing session cookie
        $cookieName = $this->config->get('session.cookie', 'nexus_session');

        if (isset($_COOKIE[$cookieName])) {
            return $_COOKIE[$cookieName];
        }

        return bin2hex(random_bytes(32));
    }

    /**
     * Age the flash data
     */
    protected function ageFlashData(): void
    {
        // Get old flash keys and remove them
        foreach ($this->get('_flash.old', []) as $key => $value) {
            $this->forget($key);
            $this->forget('_flash.old.' . $key);
        }

        // Move new flash keys to old
        $newFlash = [];
        foreach ($this->data as $key => $value) {
            if (str_starts_with($key, '_flash.new.')) {
                $flashKey = substr($key, strlen('_flash.new.'));
                $newFlash[$flashKey] = true;
                $this->forget($key);
            }
        }

        $this->put('_flash.old', $newFlash);
    }

    /**
     * Merge new flashes
     */
    protected function mergeNewFlashes(array $data): void
    {
        $newFlash = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, '_flash.old.')) {
                $flashKey = substr($key, strlen('_flash.old.'));
                $newFlash[$flashKey] = true;
                $this->put('_flash.new.' . $flashKey, true);
            }
        }
    }

    /**
     * Set the session cookie
     */
    public function setCookie(): void
    {
        $config = [
            'expires' => time() + ($this->config->get('session.lifetime', 120) * 60),
            'path' => $this->config->get('session.path', '/'),
            'domain' => $this->config->get('session.domain', ''),
            'secure' => $this->config->get('session.secure', false),
            'httponly' => $this->config->get('session.http_only', true),
            'samesite' => $this->config->get('session.same_site', 'lax')
        ];

        setcookie(
            $this->config->get('session.cookie', 'nexus_session'),
            $this->sessionId,
            $config
        );
    }
}
