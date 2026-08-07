<?php

declare(strict_types=1);

namespace Pin\Route\Attributes;

use Attribute;

/**
 * 指定 Route Testing 默认使用的 Action 类。
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Action
{
    /**
     * @param  string  $value  Action名称
     */
    public function __construct(public readonly string $value)
    {
    }
}
