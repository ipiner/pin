<?php

declare(strict_types=1);

namespace Pin\Token;

use Closure;
use InvalidArgumentException;
use Pin\Application;
use Pin\Token\Contracts\TokenFactory as FactoryContract;
use Pin\Token\Drivers\AesDriver;
use Pin\Token\Drivers\JwtDriver;
use Pin\Token\Drivers\SessionDriver;

/**
 * Token 管理器。
 *
 * @mixin TokenFactory
 */
class TokenManager
{
    /**
     * 自定义驱动工厂。
     *
     * @var array<string, Closure(Application, array): FactoryContract>
     */
    protected array $customCreators = [];

    /**
     * 已解析的工厂。
     *
     * @var array<string, FactoryContract>
     */
    protected array $factories = [];

    public function __construct(protected Application $app)
    {
    }

    /**
     * 转发默认工厂调用。
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->driver()->{$method}(...$parameters);
    }

    /**
     * 按配置创建工厂。
     *
     * @param  array{driver: string, ...}  $config
     */
    public function build(array $config): FactoryContract
    {
        $driver = $config['driver'];

        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($config);
        }

        return match ($driver) {
            'aes' => $this->createAesFactory(),
            'session' => $this->createSessionFactory($config),
            'jwt' => $this->createJwtFactory($config),
            default => throw new InvalidArgumentException(
                "Token driver [{$driver}] is not supported."
            )
        };
    }

    /**
     * 注册自定义驱动。
     *
     * @param  Closure(Application, array): FactoryContract  $callback
     */
    public function extend(string $driver, Closure $callback): static
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * 获取指定工厂。
     */
    public function driver(?string $name = null): FactoryContract
    {
        $name ??= $this->getDefaultDriver();

        return $this->factories[$name] ??= $this->resolve($name);
    }

    /**
     * 创建自定义工厂。
     */
    protected function callCustomCreator(array $config): FactoryContract
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }

    /**
     * 创建 AES 工厂。
     */
    protected function createAesFactory(): FactoryContract
    {
        return new TokenFactory(new AesDriver());
    }

    /**
     * 创建 Session 工厂。
     */
    protected function createSessionFactory(array $config): FactoryContract
    {
        return new TokenFactory(new SessionDriver(
            $this->app['cache']->store($config['cacheStore'] ?? null),
            $config,
        ));
    }

    /**
     * 创建 JWT 工厂。
     */
    protected function createJwtFactory(array $config): FactoryContract
    {
        return new TokenFactory(new JwtDriver($config));
    }

    /**
     * 获取驱动配置。
     */
    protected function getConfig(string $name): ?array
    {
        return $this->app['config']["pin.token.drivers.{$name}"];
    }

    /**
     * 获取默认驱动名称。
     */
    protected function getDefaultDriver(): string
    {
        return $this->app['config']['pin.token.default'] ?? 'default';
    }

    /**
     * 解析驱动工厂。
     */
    protected function resolve(string $name): FactoryContract
    {
        $config = $this->getConfig($name);

        if ($config !== null) {
            $config['driver'] ??= $name;

            return $this->build($config);
        }

        if (isset($this->customCreators[$name])) {
            return $this->callCustomCreator(['driver' => $name]);
        }

        throw new InvalidArgumentException("Token driver [{$name}] is not defined.");
    }
}
