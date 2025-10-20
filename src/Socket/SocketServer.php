<?php

namespace Nexus\Socket;

use Exception;
use Closure;

/**
 * Socket Server
 *
 * Creates a socket server to accept incoming connections
 */
class SocketServer
{
    protected mixed $socket = null;
    protected string $host;
    protected int $port;
    protected string $type;
    protected bool $running = false;
    protected array $clients = [];
    protected array $options = [];

    /**
     * Create a new socket server
     */
    public function __construct(
        string $host = '0.0.0.0',
        int $port = 8080,
        string $type = Socket::TYPE_TCP,
        array $options = []
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->type = $type;
        $this->options = $options;
    }

    /**
     * Create a TCP server
     */
    public static function tcp(string $host = '0.0.0.0', int $port = 8080, array $options = []): self
    {
        return new self($host, $port, Socket::TYPE_TCP, $options);
    }

    /**
     * Create a UDP server
     */
    public static function udp(string $host = '0.0.0.0', int $port = 8080, array $options = []): self
    {
        return new self($host, $port, Socket::TYPE_UDP, $options);
    }

    /**
     * Start the server
     */
    public function listen(?Closure $callback = null): void
    {
        $scheme = $this->type;
        $address = "{$scheme}://{$this->host}:{$this->port}";

        $errno = 0;
        $errstr = '';

        $this->socket = @stream_socket_server(
            $address,
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            $this->createStreamContext()
        );

        if (!$this->socket) {
            throw new Exception("Failed to create server on {$address}: {$errstr} ({$errno})");
        }

        $this->running = true;

        echo "Server listening on {$this->host}:{$this->port}\n";

        if ($callback) {
            $this->run($callback);
        }
    }

    /**
     * Run the server with a callback
     */
    public function run(Closure $callback): void
    {
        if (!$this->socket) {
            throw new Exception("Server is not listening");
        }

        while ($this->running) {
            // Accept incoming connection
            $client = @stream_socket_accept($this->socket, -1, $peerName);

            if ($client === false) {
                continue;
            }

            echo "New connection from {$peerName}\n";

            // Handle client in callback
            try {
                $callback($client, $peerName, $this);
            } catch (Exception $e) {
                echo "Error handling client: {$e->getMessage()}\n";
            }

            // Close client connection if not kept alive
            if (is_resource($client)) {
                @fclose($client);
            }
        }
    }

    /**
     * Accept a single connection
     */
    public function accept(?int $timeout = null): mixed
    {
        if (!$this->socket) {
            throw new Exception("Server is not listening");
        }

        $client = @stream_socket_accept($this->socket, $timeout ?? -1, $peerName);

        if ($client === false) {
            return false;
        }

        return [
            'socket' => $client,
            'peer' => $peerName
        ];
    }

    /**
     * Send data to a client
     */
    public function send(mixed $client, string $data): int
    {
        if (!is_resource($client)) {
            throw new Exception("Invalid client socket");
        }

        $bytes = @fwrite($client, $data);

        if ($bytes === false) {
            throw new Exception("Failed to write to client");
        }

        return $bytes;
    }

    /**
     * Receive data from a client
     */
    public function receive(mixed $client, int $length = 8192): string|false
    {
        if (!is_resource($client)) {
            throw new Exception("Invalid client socket");
        }

        return @fread($client, $length);
    }

    /**
     * Broadcast to all connected clients
     */
    public function broadcast(string $data): void
    {
        foreach ($this->clients as $client) {
            if (is_resource($client)) {
                @fwrite($client, $data);
            }
        }
    }

    /**
     * Add a client to tracked clients
     */
    public function addClient(mixed $client): void
    {
        $this->clients[] = $client;
    }

    /**
     * Remove a client from tracked clients
     */
    public function removeClient(mixed $client): void
    {
        $key = array_search($client, $this->clients, true);
        if ($key !== false) {
            unset($this->clients[$key]);
        }
    }

    /**
     * Get all connected clients
     */
    public function getClients(): array
    {
        return $this->clients;
    }

    /**
     * Stop the server
     */
    public function stop(): void
    {
        $this->running = false;

        // Close all client connections
        foreach ($this->clients as $client) {
            if (is_resource($client)) {
                @fclose($client);
            }
        }

        $this->clients = [];

        // Close server socket
        if ($this->socket) {
            @fclose($this->socket);
            $this->socket = null;
        }
    }

    /**
     * Check if server is running
     */
    public function isRunning(): bool
    {
        return $this->running && $this->socket !== null;
    }

    /**
     * Get server address
     */
    public function getAddress(): string
    {
        return "{$this->host}:{$this->port}";
    }

    /**
     * Create stream context with options
     */
    protected function createStreamContext(): mixed
    {
        $contextOptions = [];

        if (in_array($this->type, [Socket::TYPE_SSL, Socket::TYPE_TLS])) {
            $contextOptions['ssl'] = array_merge([
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ], $this->options['ssl'] ?? []);
        }

        return stream_context_create($contextOptions);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->stop();
    }
}
