<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static int id()
 * @method static string type()
 * @method static \Illuminate\Contracts\Auth\Authenticatable|\Pin\Auth\ConsoleUser|null user()
 * @method static string username()
 *
 * @see \Pin\Log\Actor
 */
class Actor extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return 'pin.log.actor';
    }
}
