<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Facade;
use Override;
use Pin\Cache\HashDriver;
use UnitEnum;

/**
 * @method static Repository store()
 * @method static HashDriver getDriver()
 * @method static bool del(array|string $key)
 * @method static bool expire(string $key, int $seconds)
 * @method static int hDel(string $key, string ...$fields)
 * @method static mixed hGet(string $key, string $field)
 * @method static array hGetAll(string $key)
 * @method static array hMGet(string $key, array $fields)
 * @method static bool hMSet(string $key, array $data)
 * @method static bool persist(string $key)
 * @method static int ttl(string $key)
 * @method static array getAll(string $key)
 * @method static mixed get(UnitEnum|array|string $key, mixed $default = null)
 * @method static array many(array $keys)
 * @method static bool put(
 *     UnitEnum|array|string $key,
 *     mixed $value,
 *     DateTimeInterface|DateInterval|int|null $ttl = null
 * )
 * @method static bool putMany(array $values, DateTimeInterface|DateInterval|int|null $ttl = null)
 * @method static int|bool increment(string $key, mixed $value = 1)
 * @method static int|bool decrement(string $key, mixed $value = 1)
 * @method static bool forever(string $key, mixed $value)
 * @method static bool touch(UnitEnum|string $key, DateTimeInterface|DateInterval|int $ttl)
 * @method static bool forget(UnitEnum|array|string $key)
 * @method static bool flush()
 * @method static string getPrefix()
 * @method static mixed pull(UnitEnum|array|string $key, mixed $default = null)
 * @method static bool add(
 *     UnitEnum|string $key,
 *     mixed $value,
 *     DateTimeInterface|DateInterval|int|null $ttl = null
 * )
 * @method static mixed remember(
 *     UnitEnum|string $key,
 *     DateTimeInterface|DateInterval|Closure|int|null $ttl,
 *     Closure $callback
 * )
 * @method static mixed sear(UnitEnum|string $key, Closure $callback)
 * @method static mixed rememberForever(UnitEnum|string $key, Closure $callback)
 * @method static Store getStore()
 * @method static bool set(string $key, mixed $value, DateInterval|int|null $ttl = null)
 * @method static bool delete(string $key)
 * @method static bool clear()
 * @method static iterable getMultiple(iterable $keys, mixed $default = null)
 * @method static bool setMultiple(iterable $values, DateInterval|int|null $ttl = null)
 * @method static bool deleteMultiple(iterable $keys)
 * @method static bool has(UnitEnum|array|string $key)
 *
 * @see \Pin\Cache\HashCache
 */
class HashCache extends Facade
{
    /**
     * 获取服务名称
     */
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return 'pin.cache.hash';
    }
}
