<?php

declare(strict_types=1);

use Pin\Cache\RuntimeCache as RuntimeCacheStore;
use Pin\Support\Facades\RuntimeCache;

it('resolves the runtime cache bound in the container', function () {
    $cache = new RuntimeCacheStore();
    $this->app->instance('pin.cache.runtime', $cache);
    RuntimeCache::clearResolvedInstance();

    expect(RuntimeCache::getFacadeRoot())->toBe($cache);
});

it('reuses the runtime cache before the application is available', function () {
    RuntimeCache::clearResolvedInstance();
    RuntimeCache::setFacadeApplication(null);

    try {
        $cache = RuntimeCache::getFacadeRoot();

        expect($cache)->toBeInstanceOf(RuntimeCacheStore::class)
            ->and(RuntimeCache::getFacadeRoot())->toBe($cache);
    } finally {
        RuntimeCache::setFacadeApplication($this->app);
        RuntimeCache::clearResolvedInstance();
    }
});
