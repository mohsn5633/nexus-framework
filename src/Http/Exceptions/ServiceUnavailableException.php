<?php

namespace Nexus\Http\Exceptions;

class ServiceUnavailableException extends HttpException
{
    public function __construct(string $message = 'Service Unavailable', \Throwable $previous = null, array $headers = [], int $code = 0)
    {
        parent::__construct(503, $message, $previous, $headers, $code);
    }
}
