<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use Override;
use Pin\Token\Contracts\TokenFactory;
use Pin\Token\TokenManager;
use Pin\Token\TokenPayload;

/**
 * @method static TokenFactory build(array $config)
 * @method static TokenManager extend(string $driver, Closure $callback)
 * @method static TokenFactory driver(?string $name = null)
 * @method static string encode(array|TokenPayload $payload, ?int $expires = null)
 * @method static \Pin\Token\Token decode(string $token)
 *
 * @see TokenManager
 */
class Token extends Facade
{
    /**
     * 获取服务名称。
     */
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return 'pin.token';
    }
}
