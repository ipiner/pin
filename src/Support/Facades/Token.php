<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Pin\Token\Contracts\TokenFactory;
use Pin\Token\TokenManager;
use Pin\Token\TokenPayload;

/**
 * @method static TokenFactory build(array $config)
 * @method static TokenManager extend(string $driver, \Closure $callback)
 * @method static TokenFactory driver(string|null $name = null)
 * @method static string encode(array|TokenPayload $payload, int|null $expires = null)
 * @method static \Pin\Token\Token decode(string $token)
 *
 * @see TokenManager
 */
class Token extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return 'pin.token';
    }
}
