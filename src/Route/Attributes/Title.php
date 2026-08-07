<?php

declare(strict_types=1);

namespace Pin\Route\Attributes;

use Attribute;

/**
 * 路由标题。
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Title
{
    /**
     * 标题
     */
    public function __construct(public readonly string $value)
    {
    }
}
