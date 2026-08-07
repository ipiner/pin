<?php

/** @noinspection PhpParamsInspection */

declare(strict_types=1);

namespace Pin\Cache;

use Illuminate\Support\Facades\Cache;
use Pin\Support\ServiceProvider;

/**
 * 缓存服务提供者
 */
class CacheServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->singleton('pin.cache.hash', HashCache::class);
        $this->app->singleton('pin.cache.runtime', RuntimeCache::class);

        Cache::extend('redis-hash', function () {
            $config = config('cache.stores.redis-hash');

            return Cache::repository(new RedisStore($config['connection'], $config['ttl']), $config);
        });
    }
}
