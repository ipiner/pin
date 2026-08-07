<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array all(string|null $prefix = null)
 * @method static bool delete(string $key)
 * @method static bool flush()
 * @method static mixed get(string $key, mixed $default = null)
 * @method static mixed remember(string $key, \Closure $callback, int|null $ttl = 86400)
 * @method static mixed rememberForever(string $key, \Closure $callback)
 * @method static \Illuminate\Cache\Repository repo()
 * @method static bool put(array|string $key, mixed $value = null, int|null $ttl = 86400)
 *
 * @see \Pin\Cache\RuntimeCache
 */
class RuntimeCache extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return 'pin.cache.runtime';
    }

    /**
     * Resolve the facade root instance from the container.
     *
     * @param  string  $name
     * @return mixed
     */
    protected static function resolveFacadeInstance($name)
    {
        if (! isset(static::$resolvedInstance[$name], static::$app, static::$app[$name])) {
            static::swap(new \Pin\Cache\RuntimeCache());
        }

        return parent::resolveFacadeInstance($name);
    }
}
