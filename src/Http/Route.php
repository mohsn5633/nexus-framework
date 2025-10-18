<?php

namespace Nexus\Http;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    public function __construct(
        public string $method,
        public string $path,
        public ?string $name = null
    ) {
    }
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Get extends Route
{
    public function __construct(string $path, ?string $name = null)
    {
        parent::__construct('GET', $path, $name);
    }
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Post extends Route
{
    public function __construct(string $path, ?string $name = null)
    {
        parent::__construct('POST', $path, $name);
    }
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Put extends Route
{
    public function __construct(string $path, ?string $name = null)
    {
        parent::__construct('PUT', $path, $name);
    }
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Patch extends Route
{
    public function __construct(string $path, ?string $name = null)
    {
        parent::__construct('PATCH', $path, $name);
    }
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Delete extends Route
{
    public function __construct(string $path, ?string $name = null)
    {
        parent::__construct('DELETE', $path, $name);
    }
}

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Any extends Route
{
    public function __construct(string $path, ?string $name = null)
    {
        parent::__construct('ANY', $path, $name);
    }
}
