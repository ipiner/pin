<?php

declare(strict_types=1);

namespace Pin\Auth;

use Illuminate\Auth\AuthManager;
use Illuminate\Foundation\Application;
use InvalidArgumentException;
use Pin\Support\ServiceProvider;
use Pin\Token\Drivers\SessionDriver;
use Pin\Token\TokenFactory;
use Pin\Token\TokenManager;

/**
 * 注册 Guard、用户提供器和认证 Token 驱动。
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * 注册认证服务。
     */
    public function register(): void
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
        $this->callAfterResolving('auth', function (AuthManager $auth) use ($name) {
            $auth->extend($name, function (Application $app, string $name, array $config): Guard {
                $provider = $app['auth']->createUserProvider($config['provider'] ?? null)
                    ?? throw new InvalidArgumentException(
                        "User provider for guard [{$name}] is not configured.",
                    );

                $guard = $app->make(Guard::class, [
                    'provider' => $provider,
                    'tokenResolver' => $app->make(TokenResolver::class, [
                        'request' => $app['request'],
                        'tokenKey' => $config['token_key'] ?? 'token',
                    ]),
                ]);

                $app->refresh('request', $guard, 'setRequest');

                return $guard;
            });
        });
    }

    /**
     * 注册认证 Token Driver。
     */
    protected function configureTokenDriver(string $name): void
    {
        $this->callAfterResolving('pin.token', function (TokenManager $tokens) use ($name) {
            $tokens->extend($name, function (Application $app, array $config): TokenFactory {
                $config = array_replace(
                    $app['config']->get('pin.token.drivers.session', []),
                    ['cache_prefix' => 'auth-token:'],
                    $config,
                );

                return new TokenFactory(new SessionDriver(
                    $app['cache']->store($config['cacheStore'] ?? null),
                    $config,
                ));
            });
        });
    }

    /**
     * 注册用户提供器。
     */
    protected function configureUserProvider(string $name): void
    {
        $this->callAfterResolving('auth', function (AuthManager $auth) use ($name) {
            $auth->provider($name, function (Application $app, array $config): UsersProvider {
                return $app->make(UsersProvider::class, [
                    'hasher' => $app['hash'],
                    'model' => $config['model'] ?? null,
                ]);
            });
        });
    }
}
