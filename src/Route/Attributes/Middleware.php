<?php

declare(strict_types=1);

namespace Pin\Route\Attributes;

use Attribute;

/**
 * 路由中间件
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Middleware
{
    public function __construct(public readonly string|array $value)
    {
    }
}
