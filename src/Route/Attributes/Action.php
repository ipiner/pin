<?php

declare(strict_types=1);

namespace Pin\Route\Attributes;

use Attribute;

/**
 * 指定路由测试 Action
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Action
{
    /**
     * @param  class-string<\Pin\Action\Action>  $value
     */
    public function __construct(public readonly string $value)
    {
    }
}
