<?php

declare(strict_types=1);

namespace Pin\Crypt;

use Illuminate\Contracts\Support\DeferrableProvider;
use Pin\Support\ServiceProvider;

/**
 * 加解密服务提供者。
 */
class CryptServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->singleton('pin.crypt.aes', Aes::class);
        $this->app->singleton('pin.crypt.rsa', Rsa::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'pin.crypt.aes',
            'pin.crypt.rsa',
        ];
    }
}
