<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Closure;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Facade;
use Override;
use Pin\Cache\RuntimeCache as Cache;

/**
 * @method static array all(?string $prefix = null)
 * @method static bool delete(string $key)
 * @method static bool flush()
 * @method static mixed get(string $key, mixed $default = null)
 * @method static mixed remember(string $key, Closure $callback, ?int $ttl = 86400)
 * @method static mixed rememberForever(string $key, Closure $callback)
 * @method static Repository repo()
 * @method static bool put(array|string $key, mixed $value = null, ?int $ttl = 86400)
 *
 * @see Cache
 */
class RuntimeCache extends Facade
{
    /**
     * 获取服务名称
     */
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return 'pin.cache.runtime';
    }

    /**
     * 获取缓存实例
     *
     * @param  string  $name
     */
    #[Override]
    protected static function resolveFacadeInstance($name): mixed
    {
        if (! isset(static::$resolvedInstance[$name]) && ! isset(static::$app[$name])) {
            static::swap(new Cache());
        }

        return parent::resolveFacadeInstance($name);
    }
}
