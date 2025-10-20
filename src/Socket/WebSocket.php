<?php

namespace Nexus\Socket;

use Exception;
use Closure;

/**
 * WebSocket Server
 *
 * Implements WebSocket protocol (RFC 6455)
 */
class WebSocket
{
    protected SocketServer $server;
    protected array $clients = [];
    protected array $callbacks = [];
    protected bool $running = false;

    public const OPCODE_CONTINUATION = 0x0;
    public const OPCODE_TEXT = 0x1;
    public const OPCODE_BINARY = 0x2;
    public const OPCODE_CLOSE = 0x8;
    public const OPCODE_PING = 0x9;
    public const OPCODE_PONG = 0xA;

    /**
     * Create a new WebSocket server
     */
    public function __construct(string $host = '0.0.0.0', int $port = 8080)
    {
        $this->server = new SocketServer($host, $port, Socket::TYPE_TCP);
    }

    /**
     * Listen for connections
     */
    public function listen(): void
    {
        $this->running = true;

        $this->server->listen(function ($client, $peerName) {
            $this->handleConnection($client, $peerName);
        });
    }

    /**
     * Handle incoming connection
     */
    protected function handleConnection(mixed $client, string $peerName): void
    {
        // Read HTTP handshake
        $request = '';
        while ($line = fgets($client)) {
            $request .= $line;
            if (trim($line) === '') {
                break;
            }
        }

        // Perform WebSocket handshake
        if (!$this->performHandshake($client, $request)) {
            fclose($client);
            return;
        }

        // Add client to tracked clients
        $clientId = uniqid('client_', true);
        $this->clients[$clientId] = [
            'socket' => $client,
            'peer' => $peerName,
            'id' => $clientId
        ];

        echo "WebSocket client connected: {$clientId} from {$peerName}\n";

        // Trigger onConnect callback
        $this->triggerCallback('connect', $clientId, $client);

        // Read messages from client
        $this->readMessages($client, $clientId);
    }

    /**
     * Perform WebSocket handshake
     */
    protected function performHandshake(mixed $client, string $request): bool
    {
        // Extract Sec-WebSocket-Key
        if (!preg_match('/Sec-WebSocket-Key: (.+)\r\n/', $request, $matches)) {
            return false;
        }

        $key = trim($matches[1]);
        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));

        // Send handshake response
        $response = "HTTP/1.1 101 Switching Protocols\r\n";
        $response .= "Upgrade: websocket\r\n";
        $response .= "Connection: Upgrade\r\n";
        $response .= "Sec-WebSocket-Accept: {$acceptKey}\r\n\r\n";

        fwrite($client, $response);

        return true;
    }

    /**
     * Read messages from client
     */
    protected function readMessages(mixed $client, string $clientId): void
    {
        while ($this->running && is_resource($client) && !feof($client)) {
            $frame = $this->readFrame($client);

            if ($frame === false) {
                break;
            }

            // Handle different opcodes
            switch ($frame['opcode']) {
                case self::OPCODE_TEXT:
                case self::OPCODE_BINARY:
                    $this->triggerCallback('message', $clientId, $frame['payload'], $client);
                    break;

                case self::OPCODE_CLOSE:
                    $this->disconnect($clientId);
                    return;

                case self::OPCODE_PING:
                    $this->sendFrame($client, $frame['payload'], self::OPCODE_PONG);
                    break;

                case self::OPCODE_PONG:
                    // Pong received
                    break;
            }
        }

        // Client disconnected
        $this->disconnect($clientId);
    }

    /**
     * Read a WebSocket frame
     */
    protected function readFrame(mixed $client): array|false
    {
        $data = fread($client, 2);
        if (strlen($data) < 2) {
            return false;
        }

        $byte1 = ord($data[0]);
        $byte2 = ord($data[1]);

        $fin = ($byte1 & 0x80) !== 0;
        $opcode = $byte1 & 0x0F;
        $masked = ($byte2 & 0x80) !== 0;
        $payloadLength = $byte2 & 0x7F;

        // Read extended payload length
        if ($payloadLength === 126) {
            $data = fread($client, 2);
            $payloadLength = unpack('n', $data)[1];
        } elseif ($payloadLength === 127) {
            $data = fread($client, 8);
            $payloadLength = unpack('J', $data)[1];
        }

        // Read masking key if present
        $maskingKey = '';
        if ($masked) {
            $maskingKey = fread($client, 4);
        }

        // Read payload
        $payload = '';
        if ($payloadLength > 0) {
            $payload = fread($client, $payloadLength);

            // Unmask payload if masked
            if ($masked) {
                for ($i = 0; $i < strlen($payload); $i++) {
                    $payload[$i] = $payload[$i] ^ $maskingKey[$i % 4];
                }
            }
        }

        return [
            'fin' => $fin,
            'opcode' => $opcode,
            'payload' => $payload
        ];
    }

    /**
     * Send a WebSocket frame
     */
    protected function sendFrame(mixed $client, string $payload, int $opcode = self::OPCODE_TEXT): void
    {
        $frame = '';

        // First byte: FIN + opcode
        $frame .= chr(0x80 | $opcode);

        // Second byte: mask flag + payload length
        $payloadLength = strlen($payload);

        if ($payloadLength <= 125) {
            $frame .= chr($payloadLength);
        } elseif ($payloadLength <= 65535) {
            $frame .= chr(126) . pack('n', $payloadLength);
        } else {
            $frame .= chr(127) . pack('J', $payloadLength);
        }

        // Payload
        $frame .= $payload;

        fwrite($client, $frame);
    }

    /**
     * Send message to a client
     */
    public function send(string $clientId, string $message): bool
    {
        if (!isset($this->clients[$clientId])) {
            return false;
        }

        $client = $this->clients[$clientId]['socket'];
        $this->sendFrame($client, $message, self::OPCODE_TEXT);

        return true;
    }

    /**
     * Broadcast message to all clients
     */
    public function broadcast(string $message, ?string $exceptClientId = null): void
    {
        foreach ($this->clients as $clientId => $clientData) {
            if ($exceptClientId && $clientId === $exceptClientId) {
                continue;
            }

            $this->sendFrame($clientData['socket'], $message, self::OPCODE_TEXT);
        }
    }

    /**
     * Disconnect a client
     */
    public function disconnect(string $clientId): void
    {
        if (!isset($this->clients[$clientId])) {
            return;
        }

        $client = $this->clients[$clientId]['socket'];

        // Send close frame
        if (is_resource($client)) {
            $this->sendFrame($client, '', self::OPCODE_CLOSE);
            fclose($client);
        }

        $this->triggerCallback('disconnect', $clientId);

        unset($this->clients[$clientId]);

        echo "WebSocket client disconnected: {$clientId}\n";
    }

    /**
     * Register event callback
     */
    public function on(string $event, Closure $callback): self
    {
        $this->callbacks[$event] = $callback;
        return $this;
    }

    /**
     * Trigger callback
     */
    protected function triggerCallback(string $event, ...$args): void
    {
        if (isset($this->callbacks[$event])) {
            call_user_func_array($this->callbacks[$event], $args);
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
     * Stop the WebSocket server
     */
    public function stop(): void
    {
        $this->running = false;

        // Disconnect all clients
        foreach (array_keys($this->clients) as $clientId) {
            $this->disconnect($clientId);
        }

        $this->server->stop();
    }
}
