<?php

namespace Nexus\Mail;

/**
 * Mail Manager
 *
 * Manages mail configuration and instances
 */
class MailManager
{
    protected array $config;
    protected array $mailers = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get a mailer instance
     */
    public function mailer(?string $name = null): Mailer
    {
        $name = $name ?? $this->config['default'] ?? 'smtp';

        if (isset($this->mailers[$name])) {
            return $this->mailers[$name];
        }

        $config = $this->config['mailers'][$name] ?? [];

        return $this->mailers[$name] = new Mailer($config);
    }

    /**
     * Send a mailable
     */
    public function send(Mailable $mailable): bool
    {
        return $mailable->send();
    }

    /**
     * Queue a mailable
     */
    public function queue(Mailable $mailable, ?string $queue = null): mixed
    {
        return $mailable->queue($queue);
    }

    /**
     * Create a new message instance
     */
    public function to(string|array $address, ?string $name = null): Mailer
    {
        return $this->mailer()->to($address, $name);
    }

    /**
     * Magic method to forward calls to default mailer
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->mailer()->$method(...$parameters);
    }
}
