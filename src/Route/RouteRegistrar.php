<?php

declare(strict_types=1);

namespace Pin\Route;

/**
 * 统一路由注册器
 */
class RouteRegistrar
{
    /**
     * 批量注册路由 Enum
     *
     * @param  class-string<Routable>|array<class-string<Routable>>  $routes
     */
    public static function register(array|string $routes): void
    {
        foreach ((array) $routes as $enum) {
            /** @var Routable $enum */
            $enum::registerRoutes();
        }
    }
}
