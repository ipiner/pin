<?php

declare(strict_types=1);

namespace Pin\Debug;

use Pin\Support\ServiceProvider;

/**
 * 调试服务提供者
 */
class DebugServiceProvider extends ServiceProvider
{
    /**
     * 注册非生产环境的调试路由
     */
    public function boot(): void
    {
        if (! $this->app->isProduction()) {
            DebugRoute::registerRoutes();
        }
    }
}
