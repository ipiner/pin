<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Override;

/**
 * @method static bool check(string $encodedPassword, string $salt, string $hashedPassword)
 * @method static string decodeFromRequest(string $requestPassword)
 * @method static string encode(string $plain)
 * @method static string encodeToRequest(string $plain)
 * @method static string hash(string $encoded, string $salt)
 * @method static bool isEmpty(string $encoded)
 *
 * @see \Pin\Password\Password
 */
class Password extends Facade
{
    /**
     * 获取服务名称。
     */
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return 'pin.password';
    }
}
