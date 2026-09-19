<?php

declare(strict_types=1);

namespace Pin\Log;

use Override;
use Pin\Support\ServiceProvider;

/**
 * 日志服务提供者
 */
class LogServiceProvider extends ServiceProvider
{
    /**
     * 注册日志服务
     */
    #[Override]
    public function register(): void
    {
        $this->app->singleton('pin.log.actor', Actor::class);
    }
}
