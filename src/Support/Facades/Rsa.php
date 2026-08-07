<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string decrypt(string $str, string|null $privateKey = null)
 * @method static string encrypt(string $str, string|null $publicKey = null)
 * @method static string sign(string $str, string|null $privateKey = null, int $algorithm = 7)
 * @method static bool verify(string $str, string $signature, string|null $publicKey = null, int $algorithm = 7)
 *
 * @see \Pin\Crypt\Rsa
 */
class Rsa extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return 'pin.crypt.rsa';
    }
}
