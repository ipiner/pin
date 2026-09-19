<?php

declare(strict_types=1);

namespace Pin\Token;

use Override;
use Pin\Token\Contracts\TokenDriver;
use Pin\Token\Contracts\TokenFactory as FactoryContract;

/**
 * Token 工厂。
 *
 * @mixin TokenDriver
 */
class TokenFactory implements FactoryContract
{
    public function __construct(
        protected TokenDriver $driver,
        protected array $config = []
    ) {
    }

    /**
     * 转发驱动调用。
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->driver->{$method}(...$parameters);
    }

    /**
     * 编码 Token。
     *
     * @param  int|null  $expires  有效期（秒）
     */
    public function encode(array|TokenPayload $payload, ?int $expires = null): string
    {
        return $this->driver->encode(TokenPayload::new($payload), $expires);
    }

    /**
     * 解码 Token。
     */
    public function decode(string $token): Token
    {
        return $this->driver->decode($token);
    }

    /**
     * 获取驱动。
     */
    #[Override]
    public function driver(): TokenDriver
    {
        return $this->driver;
    }
}
