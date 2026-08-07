<?php

declare(strict_types=1);

namespace Pin\Attributes;

/**
 * 支持配置引用的属性值
 */
class Config
{
    /**
     * 属性值
     */
    public readonly mixed $value;

    /**
     * @param  string  $key  配置项
     */
    public function __construct(string $key)
    {
        $this->value = str_starts_with($key, '$config.')
            ? config(substr($key, 8))
            : $key;
    }
}
