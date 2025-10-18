<?php

namespace Nexus\Http\Exceptions;

class NotFoundException extends HttpException
{
    public function __construct(string $message = 'Not Found', \Throwable $previous = null, array $headers = [], int $code = 0)
    {
        parent::__construct(404, $message, $previous, $headers, $code);
    }
}
