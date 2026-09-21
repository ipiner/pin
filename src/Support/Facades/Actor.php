<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Facade;
use Override;
use Pin\Auth\ConsoleUser;

/**
 * @method static int id()
 * @method static string type()
 * @method static Authenticatable|ConsoleUser|null user()
 * @method static string username()
 *
 * @see \Pin\Log\Actor
 */
class Actor extends Facade
{
    /**
     * 获取服务名称
     */
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return 'pin.log.actor';
    }
}
