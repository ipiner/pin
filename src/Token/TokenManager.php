<?php

declare(strict_types=1);

namespace Pin\Token;

use Closure;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Pin\Application;
use Pin\Token\Drivers\AesDriver;
use Pin\Token\Drivers\JwtDriver;
use Pin\Token\Drivers\SessionDriver;

/**
 * Token 管理器（Token Manager）
 *
 * @mixin TokenFactory
 */
class TokenManager
{
    /**
     * 自定义 Driver 创建器
     *
     * @var array<string, Closure(Application,array): Contracts\TokenFactory>
     */
    protected array $customCreators = [];

    /**
     * 已解析的 Driver 实例缓存
     *
     * @var array<string, Contracts\TokenFactory>
     */
    protected array $factories = [];

    /**
     * @param  Application  $app  应用实例
     */
    public function __construct(protected Application $app)
    {
    }

    /**
     * 动态调用默认 Driver 方法
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->driver()->{$method}(...$parameters);
    }

    /**
     * 根据配置构建 TokenFactory
     *
     * @param  array  $config  Driver 配置
     */
    public function build(array $config): Contracts\TokenFactory
    {
        $driver = $config['driver'];

        // 优先使用自定义 Driver
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
     * 注册自定义 Driver
     *
     * @param  string  $driver  Driver 名称
     * @param  Closure  $callback  Driver 创建回调
     */
    public function extend(string $driver, Closure $callback): static
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * 获取指定 Driver 实例
     *
     * @param  string|null  $name  Driver 名称
     */
    public function driver(?string $name = null): Contracts\TokenFactory
    {
        $name ??= $this->getDefaultDriver();

        return $this->factories[$name] ??= $this->resolve($name);
    }

    /**
     * 调用自定义 Driver 创建器
     *
     * @param  array  $config  Driver 配置
     */
    protected function callCustomCreator(array $config): Contracts\TokenFactory
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }

    /**
     * 创建 AES Driver
     */
    protected function createAesFactory(): Contracts\TokenFactory
    {
        return new TokenFactory(new AesDriver());
    }

    /**
     * 创建 Session Driver
     */
    protected function createSessionFactory(array $config): Contracts\TokenFactory
    {
        return new TokenFactory(new SessionDriver(
            Cache::store($config['cacheStore'] ?? null),
            $config,
        ));
    }

    /**
     * 创建 JWT Driver
     */
    protected function createJwtFactory(array $config): Contracts\TokenFactory
    {
        return new TokenFactory(new JwtDriver($config));
    }

    /**
     * 获取 Driver 配置
     */
    protected function getConfig(string $name): ?array
    {
        return $this->app['config']["pin.token.drivers.{$name}"];
    }

    /**
     * 获取默认 Driver 名称
     */
    protected function getDefaultDriver(): string
    {
        return $this->app['config']['pin.token.default'] ?? 'default';
    }

    /**
     * 解析 Driver
     */
    protected function resolve(string $name): Contracts\TokenFactory
    {
        $config = $this->getConfig($name);

        // 配置驱动
        if ($config !== null) {
            $config['driver'] ??= $name;

            return $this->build($config);
        }

        // 自定义驱动
        if (isset($this->customCreators[$name])) {
            return $this->callCustomCreator(['driver' => $name]);
        }

        throw new InvalidArgumentException("Token driver [{$name}] is not defined.");
    }
}
