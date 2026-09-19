<?php

declare(strict_types=1);

namespace Pin\Password;

use Illuminate\Contracts\Support\DeferrableProvider;
use Override;
use Pin\Support\ServiceProvider;

/**
 * 密码服务提供者
 */
class PasswordServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * 注册密码服务
     */
    #[Override]
    public function register(): void
    {
        $this->app->singleton('pin.password', Password::class);
    }

    /**
     * 获取延迟加载的服务
     */
    #[Override]
    public function provides(): array
    {
        return [
            'pin.password',
        ];
    }
}
