<?php

declare(strict_types=1);

namespace Pin\Route;

use Illuminate\Routing\Route;

/**
 * 路由枚举与注册路由的映射
 */
class RouteRegistryItem
{
    public function __construct(public Routable $case, public Route $route)
    {
    }
}
