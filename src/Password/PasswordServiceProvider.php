<?php

declare(strict_types=1);

namespace Pin\Password;

use Illuminate\Contracts\Support\DeferrableProvider;
use Pin\Support\ServiceProvider;

/**
 * 密码加解密服务提供者
 */
class PasswordServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->singleton('pin.password', Password::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'pin.password',
        ];
    }
}
