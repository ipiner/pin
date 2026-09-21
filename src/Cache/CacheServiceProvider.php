<?php

declare(strict_types=1);

namespace Pin\Cache;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Pin\Support\ServiceProvider;

/**
 * 缓存服务提供者
 */
class CacheServiceProvider extends ServiceProvider
{
    /**
     * 注册缓存服务
     */
    public function register(): void
    {
        $this->app->singleton('pin.cache.hash', HashCache::class);
        $this->app->singleton('pin.cache.runtime', RuntimeCache::class);

        $this->callAfterResolving('cache', function (CacheManager $cache) {
            $cache->extend('redis-hash', fn ($app, array $config): Repository => $cache->repository(
                new RedisStore($config['connection'] ?? 'cache', $config['ttl'] ?? null),
                $config,
            ));
        });
    }
}
