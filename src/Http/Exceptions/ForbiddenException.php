<?php

namespace Nexus\Http\Exceptions;

class ForbiddenException extends HttpException
{
    public function __construct(string $message = 'Forbidden', \Throwable $previous = null, array $headers = [], int $code = 0)
    {
        parent::__construct(403, $message, $previous, $headers, $code);
    }
}
