<?php

declare(strict_types=1);

namespace Pin\Route\Concerns;

use Pin\Attributes\Attribute;

/**
 * 路由属性读取
 */
trait HasAttribute
{
    /**
     * 获取路由属性
     *
     * @template TAttribute of object
     *
     * @param  class-string<TAttribute>  $class
     * @return TAttribute|null
     */
    public function attribute(string $class): mixed
    {
        return Attribute::get($this, $class);
    }
}
