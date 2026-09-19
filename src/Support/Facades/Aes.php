<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Override;

/**
 * @method static string decrypt(string $encrypted)
 * @method static string encrypt(string $plain, bool $randomKey = false)
 *
 * @see \Pin\Crypt\Aes
 */
class Aes extends Facade
{
    /**
     * 获取服务名称。
     */
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return 'pin.crypt.aes';
    }
}
