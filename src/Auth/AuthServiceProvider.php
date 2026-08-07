<?php

declare(strict_types=1);

namespace Pin\Auth;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Pin\Support\ServiceProvider;
use Pin\Token\Drivers\SessionDriver;
use Pin\Token\TokenFactory;

/**
 * 认证服务提供者。
 *
 * 负责向应用注册认证 Guard、用户提供器以及认证 Token Driver，
 * 使应用可以通过 Laravel Auth 体系使用 Pin 的 Token 认证能力。
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->configureGuard(Guard::NAME);
        $this->configureUserProvider(UsersProvider::NAME);
        $this->configureTokenDriver(Auth::TOKEN_DRIVER);
    }

    /**
     * 注册 Token 认证 Guard。
     */
    protected function configureGuard(string $name): void
    {
        $this->app['auth']->extend($name, function (Application $app, string $name, array $config) {
            return $app->make(
                Guard::class,
                [
                    'provider' => $app['auth']->createUserProvider($config['provider']),
                    'tokenResolver' => new TokenResolver(),
                ]
            );
        });
    }

    /**
     * 注册认证 Token Driver。
     *
     * 默认使用缓存驱动存储会话 Token，并为认证 Token 设置独立缓存前缀。
     */
    protected function configureTokenDriver(string $name): void
    {
        $this->app['pin.token']->extend($name, function () {
            return new TokenFactory(new SessionDriver(
                Cache::store(),
                ['cache_prefix' => 'auth-token:']
            ));
        });
    }

    /**
     * 注册用户提供器。
     *
     * 用户提供器负责根据认证结果加载应用用户模型。
     */
    protected function configureUserProvider(string $name): void
    {
        $this->app['auth']->provider($name, function (Application $app, array $config) {
            return $app->make(
                UsersProvider::class,
                [
                    'hasher' => $app['hash'],
                    'model' => $config['model'],
                ]
            );
        });
    }
}
