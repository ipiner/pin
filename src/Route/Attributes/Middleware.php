<?php

declare(strict_types=1);

namespace Pin\Route\Attributes;

use Attribute;

/**
 * 为路由枚举 Case 声明额外中间件。
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Middleware
{
    public function __construct(public readonly string|array $value)
    {
        //
    }
}
