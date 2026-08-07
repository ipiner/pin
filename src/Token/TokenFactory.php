<?php

declare(strict_types=1);

namespace Pin\Token;

use Pin\Token\Contracts\TokenDriver;

/**
 * Token 工厂（Token Factory）
 */
class TokenFactory implements Contracts\TokenFactory
{
    /**
     * @param  TokenDriver  $driver  Token 驱动实例
     * @param  array  $config  Token 配置
     */
    public function __construct(
        protected TokenDriver $driver,
        protected array $config = []
    ) {
    }

    /**
     * 动态调用底层驱动方法
     *
     * @param  string  $method  方法名
     * @param  array  $parameters  方法参数
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->driver->{$method}(...$parameters);
    }

    /**
     * 编码 Token
     *
     * @param  array|TokenPayload  $payload  Token 载荷
     * @param  int|null  $expires  过期时间（秒）
     */
    public function encode(array|TokenPayload $payload, ?int $expires = null): string
    {
        $payload = is_array($payload) ? new TokenPayload($payload) : $payload;

        return $this->driver->encode($payload, $expires);
    }

    /**
     * 解码 Token
     *
     * @param  string  $token  Token 字符串
     */
    public function decode(string $token): Token
    {
        return $this->driver->decode($token);
    }

    /**
     * 获取底层驱动实例
     */
    public function driver(): TokenDriver
    {
        return $this->driver;
    }
}
