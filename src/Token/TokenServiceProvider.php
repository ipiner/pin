<?php

declare(strict_types=1);

namespace Pin\Token;

use Pin\Application;
use Pin\Support\ServiceProvider;

/**
 * Token服务提供者
 */
class TokenServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton(
            'pin.token',
            fn (Application $app) => new TokenManager($app)
        );
    }
}
