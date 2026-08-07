<?php

declare(strict_types=1);

namespace Pin\Errors\Attribute;

use Attribute;

/**
 * 翻译分组
 *
 * 例如：
 * - pin::errors
 * - errors
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_CLASS_CONSTANT)]
readonly class Group
{
    public function __construct(public string|bool $value)
    {
        //
    }
}
