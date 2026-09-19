<?php

declare(strict_types=1);

namespace Pin\Token;

use Override;
use Pin\Application;
use Pin\Support\ServiceProvider;

/**
 * Token 服务提供者。
 */
class TokenServiceProvider extends ServiceProvider
{
    /**
     * 注册 Token 管理器。
     */
    #[Override]
    public function register(): void
    {
        $this->app->singleton(
            'pin.token',
            fn (Application $app) => new TokenManager($app)
        );
    }
}
