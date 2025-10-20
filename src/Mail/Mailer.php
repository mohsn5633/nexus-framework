<?php

namespace Nexus\Mail;

use Exception;

/**
 * Mailer Class
 *
 * Handles sending emails via SMTP with TLS/SSL support
 */
class Mailer
{
    protected array $config;
    protected $smtp = null;
    protected array $to = [];
    protected array $cc = [];
    protected array $bcc = [];
    protected ?string $from = null;
    protected ?string $fromName = null;
    protected ?string $replyTo = null;
    protected ?string $subject = null;
    protected ?string $body = null;
    protected ?string $altBody = null;
    protected array $attachments = [];
    protected bool $isHtml = true;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Set recipient(s)
     */
    public function to(string|array $address, ?string $name = null): self
    {
        if (is_array($address)) {
            foreach ($address as $addr => $n) {
                $this->to[] = [
                    'address' => is_numeric($addr) ? $n : $addr,
                    'name' => is_numeric($addr) ? '' : $n
                ];
            }
        } else {
            $this->to[] = ['address' => $address, 'name' => $name ?? ''];
        }

        return $this;
    }

    /**
     * Set CC recipient(s)
     */
    public function cc(string|array $address, ?string $name = null): self
    {
        if (is_array($address)) {
            foreach ($address as $addr => $n) {
                $this->cc[] = [
                    'address' => is_numeric($addr) ? $n : $addr,
                    'name' => is_numeric($addr) ? '' : $n
                ];
            }
        } else {
            $this->cc[] = ['address' => $address, 'name' => $name ?? ''];
        }

        return $this;
    }

    /**
     * Set BCC recipient(s)
     */
    public function bcc(string|array $address, ?string $name = null): self
    {
        if (is_array($address)) {
            foreach ($address as $addr => $n) {
                $this->bcc[] = [
                    'address' => is_numeric($addr) ? $n : $addr,
                    'name' => is_numeric($addr) ? '' : $n
                ];
            }
        } else {
            $this->bcc[] = ['address' => $address, 'name' => $name ?? ''];
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
     * Set email subject
     */
    public function subject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set HTML body
     */
    public function html(string $html): self
    {
        $this->body = $html;
        $this->isHtml = true;

        return $this;
    }

    /**
     * Set plain text body
     */
    public function text(string $text): self
    {
        if ($this->isHtml) {
            $this->altBody = $text;
        } else {
            $this->body = $text;
        }

        return $this;
    }

    /**
     * Set email view
     */
    public function view(string $view, array $data = []): self
    {
        $html = view($view, $data);
        return $this->html($html);
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
     * Send the email
     */
    public function send(): bool
    {
        try {
            // Use configured from if not set
            if (!$this->from) {
                $this->from = $this->config['from']['address'] ?? null;
                $this->fromName = $this->config['from']['name'] ?? null;
            }

            // Validate required fields
            if (empty($this->to)) {
                throw new Exception('No recipients specified');
            }

            if (!$this->subject) {
                throw new Exception('No subject specified');
            }

            if (!$this->body) {
                throw new Exception('No message body specified');
            }

            // Send via configured driver
            $driver = $this->config['driver'] ?? 'smtp';

            if ($driver === 'smtp') {
                return $this->sendViaSmtp();
            } elseif ($driver === 'mail') {
                return $this->sendViaMail();
            }

            throw new Exception("Unsupported mail driver: {$driver}");
        } catch (Exception $e) {
            // Log error
            error_log("Mail sending failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Send email via SMTP
     */
    protected function sendViaSmtp(): bool
    {
        $host = $this->config['host'] ?? 'localhost';
        $port = $this->config['port'] ?? 587;
        $username = $this->config['username'] ?? '';
        $password = $this->config['password'] ?? '';
        $encryption = $this->config['encryption'] ?? 'tls';

        // Connect to SMTP server
        $this->smtp = $this->connectSmtp($host, $port, $encryption);

        // Authenticate
        if ($username && $password) {
            $this->smtpCommand("AUTH LOGIN");
            $this->smtpCommand(base64_encode($username));
            $this->smtpCommand(base64_encode($password));
        }

        // Send MAIL FROM
        $this->smtpCommand("MAIL FROM: <{$this->from}>");

        // Send RCPT TO for all recipients
        foreach ($this->to as $recipient) {
            $this->smtpCommand("RCPT TO: <{$recipient['address']}>");
        }

        foreach ($this->cc as $recipient) {
            $this->smtpCommand("RCPT TO: <{$recipient['address']}>");
        }

        foreach ($this->bcc as $recipient) {
            $this->smtpCommand("RCPT TO: <{$recipient['address']}>");
        }

        // Send DATA
        $this->smtpCommand("DATA");

        // Build email headers and body
        $message = $this->buildMessage();
        fwrite($this->smtp, $message . "\r\n.\r\n");
        $this->smtpRead();

        // Quit
        $this->smtpCommand("QUIT");
        fclose($this->smtp);

        return true;
    }

    /**
     * Connect to SMTP server
     */
    protected function connectSmtp(string $host, int $port, string $encryption): mixed
    {
        $timeout = 30;

        if ($encryption === 'ssl') {
            $host = "ssl://{$host}";
        }

        $smtp = fsockopen($host, $port, $errno, $errstr, $timeout);

        if (!$smtp) {
            throw new Exception("Could not connect to SMTP server: {$errstr} ({$errno})");
        }

        // Read greeting
        $this->smtpRead($smtp);

        // Send EHLO
        fwrite($smtp, "EHLO {$_SERVER['SERVER_NAME']}\r\n");
        $this->smtpRead($smtp);

        // Start TLS if needed
        if ($encryption === 'tls') {
            fwrite($smtp, "STARTTLS\r\n");
            $this->smtpRead($smtp);

            if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception("Failed to enable TLS encryption");
            }

            // Send EHLO again after STARTTLS
            fwrite($smtp, "EHLO {$_SERVER['SERVER_NAME']}\r\n");
            $this->smtpRead($smtp);
        }

        return $smtp;
    }

    /**
     * Send SMTP command
     */
    protected function smtpCommand(string $command): void
    {
        fwrite($this->smtp, $command . "\r\n");
        $this->smtpRead();
    }

    /**
     * Read SMTP response
     */
    protected function smtpRead($smtp = null): string
    {
        $smtp = $smtp ?? $this->smtp;
        $response = '';

        while ($line = fgets($smtp, 515)) {
            $response .= $line;

            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }

        // Check for errors
        $code = intval(substr($response, 0, 3));
        if ($code >= 400) {
            throw new Exception("SMTP Error: {$response}");
        }

        return $response;
    }

    /**
     * Build email message
     */
    protected function buildMessage(): string
    {
        $boundary = md5(uniqid(time()));
        $headers = [];

        // From header
        if ($this->fromName) {
            $headers[] = "From: {$this->fromName} <{$this->from}>";
        } else {
            $headers[] = "From: {$this->from}";
        }

        // To header
        $toAddresses = array_map(function($r) {
            return $r['name'] ? "{$r['name']} <{$r['address']}>" : $r['address'];
        }, $this->to);
        $headers[] = "To: " . implode(', ', $toAddresses);

        // CC header
        if (!empty($this->cc)) {
            $ccAddresses = array_map(function($r) {
                return $r['name'] ? "{$r['name']} <{$r['address']}>" : $r['address'];
            }, $this->cc);
            $headers[] = "Cc: " . implode(', ', $ccAddresses);
        }

        // Reply-To header
        if ($this->replyTo) {
            $headers[] = "Reply-To: {$this->replyTo}";
        }

        // Subject
        $headers[] = "Subject: {$this->subject}";

        // Standard headers
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Date: " . date('r');
        $headers[] = "Message-ID: <" . md5(uniqid()) . "@{$_SERVER['SERVER_NAME']}>";

        // Content type
        if (!empty($this->attachments)) {
            $headers[] = "Content-Type: multipart/mixed; boundary=\"{$boundary}\"";
        } elseif ($this->isHtml && $this->altBody) {
            $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
        } elseif ($this->isHtml) {
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
        }

        $message = implode("\r\n", $headers) . "\r\n\r\n";

        // Body
        if (!empty($this->attachments) || ($this->isHtml && $this->altBody)) {
            // Multipart message
            if ($this->isHtml && $this->altBody) {
                $message .= "--{$boundary}\r\n";
                $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
                $message .= $this->altBody . "\r\n\r\n";

                $message .= "--{$boundary}\r\n";
                $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
                $message .= $this->body . "\r\n\r\n";
            } else {
                $message .= "--{$boundary}\r\n";
                $contentType = $this->isHtml ? 'text/html' : 'text/plain';
                $message .= "Content-Type: {$contentType}; charset=UTF-8\r\n\r\n";
                $message .= $this->body . "\r\n\r\n";
            }

            // Attachments
            foreach ($this->attachments as $attachment) {
                $message .= $this->buildAttachment($attachment, $boundary);
            }

            $message .= "--{$boundary}--\r\n";
        } else {
            $message .= $this->body;
        }

        return $message;
    }

    /**
     * Build attachment part
     */
    protected function buildAttachment(array $attachment, string $boundary): string
    {
        $content = base64_encode(file_get_contents($attachment['path']));
        $content = chunk_split($content);

        $message = "--{$boundary}\r\n";
        $message .= "Content-Type: application/octet-stream; name=\"{$attachment['name']}\"\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n";
        $message .= "Content-Disposition: attachment; filename=\"{$attachment['name']}\"\r\n\r\n";
        $message .= $content . "\r\n";

        return $message;
    }

    /**
     * Send via PHP mail() function
     */
    protected function sendViaMail(): bool
    {
        $to = implode(', ', array_column($this->to, 'address'));
        $subject = $this->subject;
        $message = $this->body;

        $headers = [];
        if ($this->fromName) {
            $headers[] = "From: {$this->fromName} <{$this->from}>";
        } else {
            $headers[] = "From: {$this->from}";
        }

        if ($this->isHtml) {
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        }

        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
}
