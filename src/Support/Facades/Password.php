<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool check(string $encodedPassword, string $salt, string $hashedPassword)
 * @method static string decodeFromRequest(string $requestPassword)
 * @method static string encode(string $plain)
 * @method static string encodeToRequest(string $rawPassword)
 * @method static string hash(string $password, string $salt)
 *
 * @see \Pin\Password\Password
 */
class Password extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return 'pin.password';
    }
}
