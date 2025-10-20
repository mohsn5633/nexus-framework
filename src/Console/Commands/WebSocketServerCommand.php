<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;
use Nexus\Socket\WebSocket;

class WebSocketServerCommand extends Command
{
    protected string $signature = 'websocket:serve {--host=0.0.0.0} {--port=8080}';
    protected string $description = 'Start a WebSocket server';

    public function handle(): int
    {
        $host = $this->option('host') ?? config('socket.websocket.host', '0.0.0.0');
        $port = (int) ($this->option('port') ?? config('socket.websocket.port', 8080));

        $this->info("Starting WebSocket server on {$host}:{$port}");

        $server = new WebSocket($host, $port);

        // Register event handlers
        $server->on('connect', function ($clientId, $client) {
            $this->info("Client connected: {$clientId}");
        });

        $server->on('message', function ($clientId, $message, $client) {
            $this->info("Message from {$clientId}: {$message}");

            // Example: Echo message back to client
            // You can customize this behavior in your application
        });

        $server->on('disconnect', function ($clientId) {
            $this->info("Client disconnected: {$clientId}");
        });

        try {
            $server->listen();
        } catch (\Exception $e) {
            $this->error("WebSocket server error: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}
