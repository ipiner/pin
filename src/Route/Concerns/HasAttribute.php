<?php

declare(strict_types=1);

namespace Pin\Route\Concerns;

use Pin\Attributes\Attribute;

/**
 * 提供 Route Enum Attribute 的读取能力
 */
trait HasAttribute
{
    /**
     * 获取当前枚举 case 上指定类型的 Attribute
     */
    public function attribute(string $class): mixed
    {
        return Attribute::get($this, $class);
    }
}
