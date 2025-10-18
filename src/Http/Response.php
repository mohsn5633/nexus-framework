<?php

namespace Nexus\Http;

class Response
{
    protected array $headers = [];

    public function __construct(
        protected mixed $content = '',
        protected int $status = 200
    ) {
    }

    /**
     * Set the response content
     */
    public function setContent(mixed $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get the response content
     */
    public function getContent(): mixed
    {
        return $this->content;
    }

    /**
     * Set the response status code
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get the response status code
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Set a header
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set multiple headers
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Get all headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Create a JSON response
     */
    public static function json(mixed $data, int $status = 200): self
    {
        return (new self(json_encode($data), $status))
            ->header('Content-Type', 'application/json');
    }

    /**
     * Create a redirect response
     */
    public static function redirect(string $url, int $status = 302): self
    {
        return (new self('', $status))
            ->header('Location', $url);
    }

    /**
     * Create a view response
     */
    public static function view(string $view, array $data = [], int $status = 200): self
    {
        $content = \Nexus\View\View::make($view, $data);
        return new self($content, $status);
    }

    /**
     * Send the response to the browser
     */
    public function send(): void
    {
        // Send status code
        http_response_code($this->status);

        // Send headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Send content
        if (is_array($this->content) || is_object($this->content)) {
            echo json_encode($this->content);
        } else {
            echo $this->content;
        }
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return (string) $this->content;
    }
}
