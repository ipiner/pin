<?php

declare(strict_types=1);

namespace Pin\Log;

use Pin\Support\ServiceProvider;

/**
 * 操作日志服务提供者
 */
class LogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->singleton('pin.log.actor', Actor::class);
    }
}
