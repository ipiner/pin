<?php

/** @noinspection PhpParamsInspection */

declare(strict_types=1);

namespace Pin\Cache;

use Illuminate\Support\Facades\Redis;

/**
 * Laravel Cache Store，使用 Redis Hash 保存缓存数据。
 */
class RedisStore implements HashStore
{
    use HashStoreHelper;

    public function __construct(string $connection = 'cache', ?int $ttl = null)
    {
        $this->setDriver(new RedisHashDriver(Redis::connection($connection)));
        $this->setDefaultTTL($ttl);
    }
}
