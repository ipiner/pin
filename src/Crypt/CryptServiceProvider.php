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
     * 注册加解密服务。
     */
    public function register(): void
    {
        $this->app->singleton('pin.crypt.aes', Aes::class);
        $this->app->singleton('pin.crypt.rsa', Rsa::class);
    }

    /**
     * 获取延迟加载的服务。
     */
    public function provides(): array
    {
        return [
            'pin.crypt.aes',
            'pin.crypt.rsa',
        ];
    }
}
