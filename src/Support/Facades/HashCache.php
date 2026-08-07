<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Contracts\Cache\Repository store()
 * @method static static HashDriver getDriver()
 * @method static bool del(array|string $key)
 * @method static bool expire(string $key, int $seconds)
 * @method static int hDel(string $key, string ...$fields)
 * @method static mixed hGet(string $key, string $field)
 * @method static array hGetAll(string $key)
 * @method static array hMGet(string $key, array $fields)
 * @method static bool hMSet(string $key, array $data)
 * @method static int ttl(string $key)
 * @method static array getAll(string $key)
 * @method static mixed get(string $key)
 * @method static array many(array $keys)
 * @method static bool put(string $key, mixed $value, int $seconds)
 * @method static bool putMany(array $values, int $seconds)
 * @method static int|bool increment(string $key, mixed $value = 1)
 * @method static int|bool decrement(string $key, mixed $value = 1)
 * @method static bool forever(string $key, mixed $value)
 * @method static bool touch(string $key, int $seconds)
 * @method static bool forget(string $key)
 * @method static bool flush()
 * @method static string getPrefix()
 * @method static mixed|mixed pull(\UnitEnum|array|string $key, mixed|\Closure $default = null)
 * @method static bool add(\UnitEnum|string $key, mixed $value, \DateTimeInterface|\DateInterval|int|null $ttl = null)
 * @method static mixed remember(\UnitEnum|string $key, \DateTimeInterface|\DateInterval|\Closure|int|null $ttl, \Closure $callback)
 * @method static mixed sear(\UnitEnum|string $key, \Closure $callback)
 * @method static mixed rememberForever(\UnitEnum|string $key, \Closure $callback)
 * @method static \Illuminate\Contracts\Cache\Store getStore()
 * @method static bool set(string $key, mixed $value, null|int|\DateInterval $ttl = null)
 * @method static bool delete(string $key)
 * @method static bool clear()
 * @method static iterable getMultiple(iterable $keys, mixed $default = null)
 * @method static bool setMultiple(iterable $values, null|int|\DateInterval $ttl = null)
 * @method static bool deleteMultiple(iterable $keys)
 * @method static bool has(string $key)
 *
 * @see \Pin\Cache\HashCache
 */
class HashCache extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return 'pin.cache.hash';
    }
}
