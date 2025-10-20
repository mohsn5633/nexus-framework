<?php

namespace Nexus\Mail;

use Nexus\Queue\Dispatchable;

/**
 * Mailable Base Class
 *
 * Base class for email templates
 */
abstract class Mailable
{
    use Dispatchable;

    protected array $to = [];
    protected array $cc = [];
    protected array $bcc = [];
    protected ?string $from = null;
    protected ?string $fromName = null;
    protected ?string $replyTo = null;
    protected ?string $subject = null;
    protected ?string $view = null;
    protected ?string $textView = null;
    protected array $viewData = [];
    protected array $attachments = [];

    /**
     * Build the message (override in subclass)
     */
    abstract public function build(): void;

    /**
     * Set recipient(s)
     */
    public function to(string|array $address, ?string $name = null): self
    {
        if (is_array($address)) {
            $this->to = array_merge($this->to, $address);
        } else {
            $this->to[] = ['address' => $address, 'name' => $name];
        }

        return $this;
    }

    /**
     * Set CC recipient(s)
     */
    public function cc(string|array $address, ?string $name = null): self
    {
        if (is_array($address)) {
            $this->cc = array_merge($this->cc, $address);
        } else {
            $this->cc[] = ['address' => $address, 'name' => $name];
        }

        return $this;
    }

    /**
     * Set BCC recipient(s)
     */
    public function bcc(string|array $address, ?string $name = null): self
    {
        if (is_array($address)) {
            $this->bcc = array_merge($this->bcc, $address);
        } else {
            $this->bcc[] = ['address' => $address, 'name' => $name];
        }

        return $this;
    }

    /**
     * Set from address
     */
    public function from(string $address, ?string $name = null): self
    {
        $this->from = $address;
        $this->fromName = $name;

        return $this;
    }

    /**
     * Set reply-to address
     */
    public function replyTo(string $address): self
    {
        $this->replyTo = $address;

        return $this;
    }

    /**
     * Set subject
     */
    public function subject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set view
     */
    public function view(string $view, array $data = []): self
    {
        $this->view = $view;
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * Set text view (plain text alternative)
     */
    public function text(string $view, array $data = []): self
    {
        $this->textView = $view;
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * Add data to view
     */
    public function with(string|array $key, mixed $value = null): self
    {
        if (is_array($key)) {
            $this->viewData = array_merge($this->viewData, $key);
        } else {
            $this->viewData[$key] = $value;
        }

        return $this;
    }

    /**
     * Attach a file
     */
    public function attach(string $path, ?string $name = null): self
    {
        $this->attachments[] = [
            'path' => $path,
            'name' => $name ?? basename($path)
        ];

        return $this;
    }

    /**
     * Send the mailable
     */
    public function send(): bool
    {
        // Build the message
        $this->build();

        // Get mailer instance
        $mailer = app(MailManager::class)->mailer();

        // Set recipients
        foreach ($this->to as $recipient) {
            $mailer->to($recipient['address'], $recipient['name'] ?? null);
        }

        foreach ($this->cc as $recipient) {
            $mailer->cc($recipient['address'], $recipient['name'] ?? null);
        }

        foreach ($this->bcc as $recipient) {
            $mailer->bcc($recipient['address'], $recipient['name'] ?? null);
        }

        // Set from
        if ($this->from) {
            $mailer->from($this->from, $this->fromName);
        }

        // Set reply-to
        if ($this->replyTo) {
            $mailer->replyTo($this->replyTo);
        }

        // Set subject
        if ($this->subject) {
            $mailer->subject($this->subject);
        }

        // Set body from view
        if ($this->view) {
            $mailer->view($this->view, $this->viewData);
        }

        // Set text alternative
        if ($this->textView) {
            $textBody = view($this->textView, $this->viewData);
            $mailer->text($textBody);
        }

        // Add attachments
        foreach ($this->attachments as $attachment) {
            $mailer->attach($attachment['path'], $attachment['name']);
        }

        return $mailer->send();
    }

    /**
     * Queue the mailable
     */
    public function queue(?string $queue = null): mixed
    {
        return dispatch(static::class, func_get_args(), $queue);
    }

    /**
     * Queue the mailable with delay
     */
    public function later(int $delay, ?string $queue = null): mixed
    {
        return dispatch_after($delay, static::class, func_get_args(), $queue);
    }

    /**
     * Handle the job (for queued emails)
     */
    public function handle(): void
    {
        $this->send();
    }
}
