<?php

namespace Nexus\Socket;

use Exception;

/**
 * Socket Client
 *
 * Provides TCP/UDP socket communication
 */
class Socket
{
    protected mixed $socket = null;
    protected string $type;
    protected ?string $host = null;
    protected ?int $port = null;
    protected bool $connected = false;
    protected int $timeout = 30;
    protected array $options = [];

    public const TYPE_TCP = 'tcp';
    public const TYPE_UDP = 'udp';
    public const TYPE_SSL = 'ssl';
    public const TYPE_TLS = 'tls';

    /**
     * Create a new socket instance
     */
    public function __construct(string $type = self::TYPE_TCP, array $options = [])
    {
        $this->type = $type;
        $this->options = $options;
    }

    /**
     * Create a TCP socket
     */
    public static function tcp(array $options = []): self
    {
        return new self(self::TYPE_TCP, $options);
    }

    /**
     * Create a UDP socket
     */
    public static function udp(array $options = []): self
    {
        return new self(self::TYPE_UDP, $options);
    }

    /**
     * Create an SSL socket
     */
    public static function ssl(array $options = []): self
    {
        return new self(self::TYPE_SSL, $options);
    }

    /**
     * Create a TLS socket
     */
    public static function tls(array $options = []): self
    {
        return new self(self::TYPE_TLS, $options);
    }

    /**
     * Set connection timeout
     */
    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * Connect to a remote host
     */
    public function connect(string $host, int $port, ?int $timeout = null): self
    {
        $this->host = $host;
        $this->port = $port;
        $timeout = $timeout ?? $this->timeout;

        $scheme = $this->getScheme();
        $address = "{$scheme}://{$host}:{$port}";

        $errno = 0;
        $errstr = '';

        $this->socket = @stream_socket_client(
            $address,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $this->createStreamContext()
        );

        if (!$this->socket) {
            throw new Exception("Failed to connect to {$address}: {$errstr} ({$errno})");
        }

        stream_set_timeout($this->socket, $timeout);
        $this->connected = true;

        return $this;
    }

    /**
     * Send data to the socket
     */
    public function send(string $data): int
    {
        if (!$this->connected) {
            throw new Exception("Socket is not connected");
        }

        $bytes = @fwrite($this->socket, $data);

        if ($bytes === false) {
            throw new Exception("Failed to write to socket");
        }

        return $bytes;
    }

    /**
     * Receive data from the socket
     */
    public function receive(int $length = 8192): string|false
    {
        if (!$this->connected) {
            throw new Exception("Socket is not connected");
        }

        return @fread($this->socket, $length);
    }

    /**
     * Read a line from the socket
     */
    public function readLine(int $length = 8192): string|false
    {
        if (!$this->connected) {
            throw new Exception("Socket is not connected");
        }

        return @fgets($this->socket, $length);
    }

    /**
     * Read all available data
     */
    public function readAll(): string
    {
        if (!$this->connected) {
            throw new Exception("Socket is not connected");
        }

        $data = '';
        while (!feof($this->socket)) {
            $chunk = fread($this->socket, 8192);
            if ($chunk === false) {
                break;
            }
            $data .= $chunk;
        }

        return $data;
    }

    /**
     * Check if socket is connected
     */
    public function isConnected(): bool
    {
        return $this->connected && $this->socket !== null && !feof($this->socket);
    }

    /**
     * Close the socket connection
     */
    public function close(): void
    {
        if ($this->socket) {
            @fclose($this->socket);
            $this->socket = null;
            $this->connected = false;
        }
    }

    /**
     * Get socket metadata
     */
    public function getMetadata(): array
    {
        if (!$this->socket) {
            return [];
        }

        return stream_get_meta_data($this->socket);
    }

    /**
     * Enable crypto on the socket
     */
    public function enableCrypto(int $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT): bool
    {
        if (!$this->socket) {
            throw new Exception("Socket is not initialized");
        }

        return stream_socket_enable_crypto($this->socket, true, $cryptoMethod);
    }

    /**
     * Set socket option
     */
    public function setOption(string $key, mixed $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * Get the scheme for the socket type
     */
    protected function getScheme(): string
    {
        return match ($this->type) {
            self::TYPE_TCP => 'tcp',
            self::TYPE_UDP => 'udp',
            self::TYPE_SSL => 'ssl',
            self::TYPE_TLS => 'tls',
            default => 'tcp'
        };
    }

    /**
     * Create stream context with options
     */
    protected function createStreamContext(): mixed
    {
        $contextOptions = [];

        if (in_array($this->type, [self::TYPE_SSL, self::TYPE_TLS])) {
            $contextOptions['ssl'] = array_merge([
                'verify_peer' => $this->options['verify_peer'] ?? true,
                'verify_peer_name' => $this->options['verify_peer_name'] ?? true,
                'allow_self_signed' => $this->options['allow_self_signed'] ?? false,
            ], $this->options['ssl'] ?? []);
        }

        return stream_context_create($contextOptions);
    }

    /**
     * Destructor - ensure socket is closed
     */
    public function __destruct()
    {
        $this->close();
    }
}
