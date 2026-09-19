<?php

declare(strict_types=1);

use Pin\Cache\ArrayStore;

it('returns non expired store items', function (?string $prefix, array $expected) {
    $store = new ArrayStore();

    expect($store->getAll())->toBe([]);

    $store->putMany([
        'tests:1' => 1,
        'tests:2' => 2,
    ], 0);

    $store->put('tests:3', 3, 1); // expired

    $this->travel(100)->seconds();

    expect($store->getAll($prefix))->toBe($expected);

})->with([
    'all keys' => [null, [
        'tests:1' => 1,
        'tests:2' => 2,
    ]],

    'filtered keys' => ['tests:1', [
        'tests:1' => 1,
    ]],

    'expired keys' => ['tests:3', []],
]);

it('does not run gc when disabled', function () {
    $store = new ArrayStore(8, 2);
    $store->putMany(range(1, 10), 0);

    expect($store->getAll())->toHaveCount(10);

    $store->gc(false);
    expect($store->getAll())->toHaveCount(10);
});

it('removes overflow items when gc enabled', function () {
    $store = new ArrayStore(8, 2);
    $store->putMany(range(1, 10), 0);

    $store->gc(true);
    expect($store->getAll())->toBe(array_combine(
        range(4, 9),
        range(5, 10),
    ));
});

it('clears overflow when the collection batch reaches the cache limit', function (int $batch) {
    $store = new ArrayStore(3, $batch);
    $store->putMany(range(1, 4), 0);

    $store->gc(true);

    expect($store->getAll())->toBe([]);
})->with([3, 5]);

it('filters numeric cache keys by prefix', function () {
    $store = new ArrayStore();
    $store->putMany(['0' => 'zero', '01' => 'first', 'other' => 'other'], 0);

    expect($store->getAll('0'))->toBe([0 => 'zero', '01' => 'first']);
});
