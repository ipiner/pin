<?php

declare(strict_types=1);

namespace Pin\Errors\Attribute;

use Attribute;

/**
 * 错误消息翻译分组
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_CLASS_CONSTANT)]
readonly class Group
{
    public function __construct(public string|false $value)
    {
    }
}
