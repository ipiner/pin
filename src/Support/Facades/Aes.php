<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string decrypt(string $encrypted)
 * @method static string encrypt(string $plain, bool $randomKey = false)
 *
 * @see \Pin\Crypt\Aes
 */
class Aes extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return 'pin.crypt.aes';
    }
}
