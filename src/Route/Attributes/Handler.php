<?php

declare(strict_types=1);

namespace Pin\Route\Attributes;

use Attribute;

/**
 * 为路由枚举 Case 指定路由处理器（Handler）。
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Handler
{
    public function __construct(public readonly string|array $value)
    {
        //
    }
}
