<?php

declare(strict_types=1);

namespace Pin\Route;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;

/**
 * 路由注册索引器
 */
class RouteRegistry
{
    /**
     * 路由映射表
     *
     * ```
     * [
     *   'user.index' => RouteRegistryItem
     * ]
     * ```
     */
    protected static array $items = [];

    /**
     * 绑定 Routable Enum 与 Laravel Route
     */
    public static function bind(Routable $case, Route $route): void
    {
        static::$items[$route->getName()] = new RouteRegistryItem($case, $route);
    }

    /**
     * 获取所有已注册的路由映射
     *
     * @return Collection<string, RouteRegistryItem>
     */
    public static function items(): Collection
    {
        return collect(static::$items);
    }
}

class RouteRegistryItem
{
    public function __construct(public Routable $case, public Route $route)
    {
    }
}
