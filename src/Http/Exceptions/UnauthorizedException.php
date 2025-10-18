<?php

namespace Nexus\Http\Exceptions;

class UnauthorizedException extends HttpException
{
    public function __construct(string $message = 'Unauthorized', \Throwable $previous = null, array $headers = [], int $code = 0)
    {
        parent::__construct(401, $message, $previous, $headers, $code);
    }
}
