<?php

declare(strict_types=1);

namespace Pin\Route\Attributes;

use Attribute;

/**
 * 路由处理器
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Handler
{
    public function __construct(public readonly string|array $value)
    {
    }
}
