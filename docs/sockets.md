# Sockets and WebSockets

Nexus Framework provides comprehensive socket programming support including raw TCP/UDP sockets, socket servers, and WebSocket implementation for real-time communication.

## Table of Contents

- [Introduction](#introduction)
- [Configuration](#configuration)
- [TCP/UDP Sockets](#tcpudp-sockets)
- [Socket Servers](#socket-servers)
- [WebSocket Server](#websocket-server)
- [SSL/TLS Support](#ssltls-support)
- [Examples](#examples)

## Introduction

The Socket system provides low-level and high-level networking capabilities:

- **Raw Sockets**: TCP, UDP socket communication
- **Socket Server**: Accept incoming connections
- **WebSocket**: RFC 6455 compliant WebSocket server
- **SSL/TLS**: Secure socket connections
- **Event-Driven**: Event-based WebSocket handling
- **Broadcasting**: Send messages to multiple clients
- **Connection Management**: Track and manage client connections

## Configuration

### Environment Variables

Configure socket settings in `.env`:

```env
# Socket Configuration
SOCKET_TYPE=tcp
SOCKET_TIMEOUT=30

# WebSocket Server
WEBSOCKET_HOST=0.0.0.0
WEBSOCKET_PORT=8080
WEBSOCKET_MAX_CLIENTS=100
WEBSOCKET_PING_INTERVAL=30

# SSL/TLS
SOCKET_SSL_VERIFY_PEER=true
SOCKET_SSL_VERIFY_PEER_NAME=true
SOCKET_SSL_ALLOW_SELF_SIGNED=false
```

### Configuration File

Socket configuration is in `config/socket.php`:

```php
return [
    'default' => env('SOCKET_TYPE', 'tcp'),
    'timeout' => env('SOCKET_TIMEOUT', 30),

    'websocket' => [
        'host' => env('WEBSOCKET_HOST', '0.0.0.0'),
        'port' => env('WEBSOCKET_PORT', 8080),
        'max_clients' => env('WEBSOCKET_MAX_CLIENTS', 100),
    ],

    'ssl' => [
        'verify_peer' => env('SOCKET_SSL_VERIFY_PEER', true),
        'verify_peer_name' => env('SOCKET_SSL_VERIFY_PEER_NAME', true),
    ],
];
```

## TCP/UDP Sockets

### Creating Sockets

```php
use Nexus\Socket\Socket;

// TCP socket
$socket = Socket::tcp();

// UDP socket
$socket = Socket::udp();

// SSL socket
$socket = Socket::ssl();

// TLS socket
$socket = Socket::tls();

// Using helper
$socket = socket('tcp');
```

### Connecting to Server

```php
// Connect to remote host
$socket->connect('example.com', 80);

// With timeout
$socket->timeout(10)->connect('example.com', 80);

// Check if connected
if ($socket->isConnected()) {
    echo "Connected!";
}
```

### Sending and Receiving Data

```php
// Send data
$bytes = $socket->send("GET / HTTP/1.1\r\nHost: example.com\r\n\r\n");

// Receive data
$data = $socket->receive(8192);

// Read line
$line = $socket->readLine();

// Read all available data
$allData = $socket->readAll();
```

### Socket Options

```php
$socket = Socket::tcp()
    ->timeout(30)
    ->setOption('verify_peer', false)
    ->connect('example.com', 443);
```

### Closing Connection

```php
$socket->close();
```

## Socket Servers

### Creating a Server

```php
use Nexus\Socket\SocketServer;

// TCP server
$server = SocketServer::tcp('0.0.0.0', 8080);

// UDP server
$server = SocketServer::udp('0.0.0.0', 8080);
```

### Listening for Connections

```php
$server->listen(function ($client, $peerName, $server) {
    echo "New connection from {$peerName}\n";

    // Receive data from client
    $data = $server->receive($client, 1024);
    echo "Received: {$data}\n";

    // Send response
    $server->send($client, "Hello from server!\n");

    // Close client connection
    fclose($client);
});
```

### Accepting Connections

```php
$server = SocketServer::tcp('0.0.0.0', 8080);
$server->listen();

while (true) {
    $connection = $server->accept(5); // 5 second timeout

    if ($connection) {
        $client = $connection['socket'];
        $peer = $connection['peer'];

        // Handle client
        $data = $server->receive($client, 1024);
        $server->send($client, "Echo: {$data}");

        fclose($client);
    }
}
```

### Managing Clients

```php
$server->listen(function ($client, $peerName, $server) {
    // Add client to tracked clients
    $server->addClient($client);

    // Broadcast to all clients
    $server->broadcast("New client connected: {$peerName}\n");

    // Handle client...

    // Remove client
    $server->removeClient($client);
});
```

### Simple Echo Server

```php
use Nexus\Socket\SocketServer;

$server = SocketServer::tcp('0.0.0.0', 8080);

echo "Echo server listening on port 8080\n";

$server->listen(function ($client, $peerName, $server) {
    echo "Connection from {$peerName}\n";

    while (!feof($client)) {
        $data = fread($client, 1024);

        if ($data === false || $data === '') {
            break;
        }

        // Echo back to client
        fwrite($client, "Echo: {$data}");
    }

    fclose($client);
    echo "Client {$peerName} disconnected\n";
});
```

## WebSocket Server

### Starting WebSocket Server

```php
use Nexus\Socket\WebSocket;

$ws = new WebSocket('0.0.0.0', 8080);

// Using helper
$ws = websocket('0.0.0.0', 8080);
```

### Event Handlers

```php
// Connection event
$ws->on('connect', function ($clientId, $client) {
    echo "Client {$clientId} connected\n";
});

// Message event
$ws->on('message', function ($clientId, $message, $client) {
    echo "Message from {$clientId}: {$message}\n";
});

// Disconnect event
$ws->on('disconnect', function ($clientId) {
    echo "Client {$clientId} disconnected\n";
});

// Start server
$ws->listen();
```

### Sending Messages

```php
$ws->on('message', function ($clientId, $message, $client) use ($ws) {
    // Send to specific client
    $ws->send($clientId, "You said: {$message}");

    // Broadcast to all clients
    $ws->broadcast("User {$clientId}: {$message}");

    // Broadcast to all except sender
    $ws->broadcast("User {$clientId}: {$message}", $clientId);
});
```

### Chat Server Example

```php
use Nexus\Socket\WebSocket;

$ws = new WebSocket('0.0.0.0', 8080);

echo "WebSocket chat server running on ws://0.0.0.0:8080\n";

$ws->on('connect', function ($clientId, $client) use ($ws) {
    echo "[{$clientId}] connected\n";
    $ws->broadcast("{$clientId} joined the chat", $clientId);
});

$ws->on('message', function ($clientId, $message, $client) use ($ws) {
    echo "[{$clientId}] {$message}\n";

    // Broadcast message to all clients
    $ws->broadcast("[{$clientId}] {$message}");
});

$ws->on('disconnect', function ($clientId) use ($ws) {
    echo "[{$clientId}] disconnected\n";
    $ws->broadcast("{$clientId} left the chat");
});

$ws->listen();
```

### Client Management

```php
// Get all connected clients
$clients = $ws->getClients();

foreach ($clients as $clientId => $clientData) {
    echo "Client: {$clientId} from {$clientData['peer']}\n";
}

// Disconnect a client
$ws->disconnect($clientId);

// Stop server
$ws->stop();
```

### CLI Command

Start WebSocket server via command line:

```bash
php nexus websocket:serve --host=0.0.0.0 --port=8080
```

## SSL/TLS Support

### Secure Socket Client

```php
use Nexus\Socket\Socket;

// SSL socket
$socket = Socket::ssl([
    'verify_peer' => true,
    'verify_peer_name' => true,
    'allow_self_signed' => false
]);

$socket->connect('secure.example.com', 443);
$socket->send("GET / HTTP/1.1\r\nHost: secure.example.com\r\n\r\n");
$response = $socket->receive(8192);
```

### TLS Socket

```php
$socket = Socket::tls()
    ->connect('api.example.com', 443);

// Enable crypto manually
$socket->enableCrypto(STREAM_CRYPTO_METHOD_TLS_CLIENT);
```

### Secure Socket Server

```php
use Nexus\Socket\SocketServer;

$server = new SocketServer('0.0.0.0', 8443, Socket::TYPE_SSL, [
    'ssl' => [
        'local_cert' => '/path/to/cert.pem',
        'local_pk' => '/path/to/key.pem',
        'passphrase' => 'your-passphrase',
        'verify_peer' => false,
    ]
]);

$server->listen(function ($client, $peerName, $server) {
    // Handle secure connection
    $data = $server->receive($client, 1024);
    $server->send($client, "Secure response: {$data}");
});
```

## Examples

### HTTP Client using Sockets

```php
use Nexus\Socket\Socket;

class SimpleHttpClient
{
    protected Socket $socket;

    public function get(string $url): string
    {
        $parts = parse_url($url);
        $host = $parts['host'];
        $port = $parts['port'] ?? 80;
        $path = $parts['path'] ?? '/';

        $this->socket = Socket::tcp();
        $this->socket->connect($host, $port);

        $request = "GET {$path} HTTP/1.1\r\n";
        $request .= "Host: {$host}\r\n";
        $request .= "Connection: close\r\n\r\n";

        $this->socket->send($request);
        $response = $this->socket->readAll();
        $this->socket->close();

        return $response;
    }
}

// Usage
$client = new SimpleHttpClient();
$response = $client->get('http://example.com');
echo $response;
```

### Port Scanner

```php
use Nexus\Socket\Socket;

class PortScanner
{
    public function scan(string $host, array $ports, int $timeout = 2): array
    {
        $openPorts = [];

        foreach ($ports as $port) {
            $socket = Socket::tcp()->timeout($timeout);

            try {
                $socket->connect($host, $port, $timeout);
                $openPorts[] = $port;
                $socket->close();
            } catch (Exception $e) {
                // Port is closed
            }
        }

        return $openPorts;
    }
}

// Usage
$scanner = new PortScanner();
$openPorts = $scanner->scan('example.com', [80, 443, 8080, 3306]);
print_r($openPorts);
```

### Real-Time Notifications

```php
use Nexus\Socket\WebSocket;

class NotificationServer
{
    protected WebSocket $ws;
    protected array $subscribers = [];

    public function __construct(string $host, int $port)
    {
        $this->ws = new WebSocket($host, $port);
        $this->setupHandlers();
    }

    protected function setupHandlers(): void
    {
        $this->ws->on('connect', function ($clientId) {
            $this->subscribers[$clientId] = [];
        });

        $this->ws->on('message', function ($clientId, $message) {
            $data = json_decode($message, true);

            if ($data['type'] === 'subscribe') {
                $this->subscribe($clientId, $data['channel']);
            } elseif ($data['type'] === 'unsubscribe') {
                $this->unsubscribe($clientId, $data['channel']);
            }
        });

        $this->ws->on('disconnect', function ($clientId) {
            unset($this->subscribers[$clientId]);
        });
    }

    protected function subscribe(string $clientId, string $channel): void
    {
        $this->subscribers[$clientId][] = $channel;
        $this->ws->send($clientId, json_encode([
            'type' => 'subscribed',
            'channel' => $channel
        ]));
    }

    protected function unsubscribe(string $clientId, string $channel): void
    {
        $key = array_search($channel, $this->subscribers[$clientId]);
        if ($key !== false) {
            unset($this->subscribers[$clientId][$key]);
        }
    }

    public function notify(string $channel, array $data): void
    {
        foreach ($this->subscribers as $clientId => $channels) {
            if (in_array($channel, $channels)) {
                $this->ws->send($clientId, json_encode([
                    'type' => 'notification',
                    'channel' => $channel,
                    'data' => $data
                ]));
            }
        }
    }

    public function start(): void
    {
        $this->ws->listen();
    }
}

// Usage
$server = new NotificationServer('0.0.0.0', 8080);
$server->start();
```

### WebSocket Client (JavaScript)

```javascript
// Connect to WebSocket server
const ws = new WebSocket('ws://localhost:8080');

ws.onopen = function() {
    console.log('Connected to WebSocket server');
    ws.send('Hello server!');
};

ws.onmessage = function(event) {
    console.log('Received:', event.data);
};

ws.onerror = function(error) {
    console.error('WebSocket error:', error);
};

ws.onclose = function() {
    console.log('Disconnected from server');
};

// Send message
function sendMessage(message) {
    ws.send(message);
}

// Close connection
function disconnect() {
    ws.close();
}
```

### Broadcast Server

```php
use Nexus\Socket\WebSocket;

$ws = new WebSocket('0.0.0.0', 8080);

$ws->on('connect', function ($clientId) use ($ws) {
    $clients = count($ws->getClients());
    $ws->broadcast("Total clients: {$clients}");
});

$ws->on('message', function ($clientId, $message) use ($ws) {
    // Broadcast to everyone except sender
    $ws->broadcast($message, $clientId);
});

$ws->on('disconnect', function ($clientId) use ($ws) {
    $clients = count($ws->getClients());
    $ws->broadcast("Client left. Total clients: {$clients}");
});

echo "Broadcast server running on ws://0.0.0.0:8080\n";
$ws->listen();
```

## Best Practices

1. **Error Handling**: Always wrap socket operations in try-catch
2. **Timeouts**: Set appropriate timeouts to prevent hanging
3. **Resource Cleanup**: Close sockets when done
4. **Buffer Size**: Use appropriate buffer sizes for your data
5. **SSL in Production**: Use SSL/TLS for production WebSocket servers
6. **Client Limits**: Limit maximum concurrent connections
7. **Heartbeat**: Implement ping/pong for connection health
8. **Message Validation**: Validate all incoming messages
9. **Rate Limiting**: Protect against message flooding
10. **Graceful Shutdown**: Handle server shutdown properly

## Troubleshooting

### Connection Refused

```php
try {
    $socket->connect('example.com', 80);
} catch (Exception $e) {
    echo "Connection failed: {$e->getMessage()}";
}
```

### Timeout Issues

```php
// Increase timeout
$socket->timeout(60)->connect('slow-server.com', 80);
```

### SSL Certificate Issues

```php
// Disable verification (not recommended for production)
$socket = Socket::ssl([
    'verify_peer' => false,
    'verify_peer_name' => false
]);
```

### Port Already in Use

```bash
# Find process using port
lsof -i :8080

# Kill process
kill -9 <PID>
```

## Next Steps

- Learn about [Process and Workers](process.md)
- Explore [HTTP Client](http-client.md)
- Understand [Queue System](queues.md)
