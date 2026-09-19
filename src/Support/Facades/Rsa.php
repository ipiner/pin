<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Override;

/**
 * @method static string decrypt(string $str, ?string $privateKey = null)
 * @method static string encrypt(string $str, ?string $publicKey = null)
 * @method static string sign(
 *     string $str,
 *     ?string $privateKey = null,
 *     int $algorithm = OPENSSL_ALGO_SHA256
 * )
 * @method static bool verify(
 *     string $str,
 *     string $signature,
 *     ?string $publicKey = null,
 *     int $algorithm = OPENSSL_ALGO_SHA256
 * )
 *
 * @see \Pin\Crypt\Rsa
 */
class Rsa extends Facade
{
    /**
     * 获取服务名称。
     */
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return 'pin.crypt.rsa';
    }
}
