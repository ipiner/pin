<?php

declare(strict_types=1);

use App\Models\Menu;
use Illuminate\Support\Facades\Cache;
use Pin\Models\Cache\CacheStore;
use Pin\Models\Cache\NullPlaceholder;
use Pin\Models\Model;
use Pin\Support\Json;
use Pin\Testing\Concerns\InteractsWithRedis;
use Pin\Tests\Models\Models\Admin;

uses(InteractsWithRedis::class);

afterEach(function () {
    Cache::store('array')->flush();
});

it('forgets cache', function () {
    $store = store();
    $repo = $store->repo();

    $repo->set('admins:1', 1);
    $repo->set('admins-all:2', 2);
    $repo->set('menus-all:2', 2);

    expect($repo->get('admins:1'))->toBe(1)
        ->and($repo->getAll('admins-all'))->toBe([2 => 2])
        ->and($repo->get('menus-all:2'))->toBe(2)
        ->and($repo->getAll('menus-all'))->toBe([2 => 2]);

    $store->forget('admins:1');

    expect($repo->get('admins:1'))->toBeNull()
        ->and($repo->getAll('admins-all'))->toBe([2 => 2])
        ->and($repo->get('menus-all:2'))->toBe(2)
        ->and($repo->getAll('menus-all'))->toBe([2 => 2]);

    store(new Menu())->forget('menus-all:1');

    expect($repo->getAll('admins-all'))->toBe([2 => 2])
        ->and($repo->get('menus-all:2'))->toBeNull()
        ->and($repo->getAll('menus-all'))->toBe([]);
});

it('remembers cache items', function () {
    $store = store();

    Cache::store('array')->put(CacheStore::class.'.foo', NullPlaceholder::make());
    expect($store->remember('testing:item', 10, fn () => null))->toBeNull();

    Cache::store('array')->put(CacheStore::class.'.foo', NullPlaceholder::make(-10));
    expect($store->remember('testing:item', 10, fn () => null))->toBeNull();

    $store->forget('testing:item');
    $store->repo()->put('testing:item', new Admin(['id' => 1]), 10);

    expect($store->remember('testing:item', 10, fn () => null)->id)->toBe(1);
    expect($store->remember('empty-callback:1', 10, fn () => null))->toBeNull();

    expect($store->remember('callback:1', 10, fn () => new Admin(['id' => 1]))->id)->toBe(1);

    Cache::store('array')->put(CacheStore::class.'.model:1', new Admin(['id' => 1]));
    expect($store->remember('model:1', 10, fn () => null)->id)->toBe(1);

    Cache::store('array')->put(CacheStore::class.'.string:1', Json::encode(['id' => 1]));
    expect($store->remember('string:1', 10, fn () => null)->id)->toBe(1);

    Cache::store('array')->put(CacheStore::class.'.array:1', ['id' => 1]);
    expect($store->remember('array:1', 10, fn () => null)->id)->toBe(1);
});

it('remembers all cache items', function () {
    $store = store(new Menu());

    Cache::store('array')->put(CacheStore::class.'.testing-all', new Menu()->newCollection([
        new Menu(['id' => 1]),
    ]));

    expect($store->rememberAll('testing-all', 10, fn () => null)[0]->id)->toBe(1);

    $store->repo()->put('testing-all-L2:1', new Menu(['id' => 1]), 600);

    expect($store->rememberAll('testing-all-L2', 10, fn () => null)[1]->id)->toBe(1);
    expect($store->rememberAll('empty-callback', 10, fn () => new Menu()->newCollection()))->toBeEmpty();
    expect($store->rememberAll('callback', 10, fn () => new Menu()->newCollection([
        new Menu(['id' => 1]),
    ]))[1]->id)->toBe(1);

    expect($store->repo()->has('callback:1'))->toBeTrue();

    $store = store(new Admin());
    expect($store->rememberAll('cache-item', 10, fn () => new Menu()->newCollection([
        new Menu(['id' => 1]),
    ]))[1]->id)->toBe(1);

    expect($store->repo()->has('cache-item:1'))->toBeFalse();
});

function store(?Model $model = null): CacheStore
{
    return new CacheStore(
        $model ?? new Admin(),
        Cache::store('redis-hash')
    );
}
