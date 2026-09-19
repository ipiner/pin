<?php

declare(strict_types=1);

namespace Pin\Cache;

use Illuminate\Support\Facades\Redis;

/**
 * Redis Hash 缓存存储。
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
