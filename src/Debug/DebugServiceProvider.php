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
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if (! app()->isProduction()) {
            DebugRoute::registerRoutes();
        }
    }
}
