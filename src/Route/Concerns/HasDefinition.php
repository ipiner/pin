<?php

declare(strict_types=1);

namespace Pin\Route\Concerns;

use Pin\Route\Attributes\Title;
use Pin\Route\RouteDefinition;
use Pin\Support\Facades\RuntimeCache;

/**
 * HasDefinition
 *
 * 提供 Route Enum 的路由定义解析能力。
 */
trait HasDefinition
{
    /**
     * 获取当前路由信息
     */
    public function definition(): RouteDefinition
    {
        return RuntimeCache::rememberForever(
            static::class.'.'.$this->name.'.'.__FUNCTION__,
            fn () => new RouteDefinition($this)
        );
    }

    /**
     * 获取路由标题
     */
    public function title(): ?string
    {
        return $this->attribute(Title::class)?->value;
    }

    /**
     * 获取路由方法
     */
    public function method(): string
    {
        return $this->definition()->method;
    }

    /**
     * 获取路由名称
     */
    public function name(): string
    {
        return $this->definition()->name;
    }

    /**
     * 获取路由URI
     */
    public function uri(): string
    {
        return $this->definition()->uri;
    }
}
