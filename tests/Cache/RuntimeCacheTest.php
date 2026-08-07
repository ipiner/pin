<?php

declare(strict_types=1);

use Pin\Support\Facades\RuntimeCache;

beforeEach(function () {
    RuntimeCache::flush();
});

it('gets, deletes, and flushes memoized values', function () {
    expect(RuntimeCache::get('key'))->toBeNull();

    RuntimeCache::put('key', 'value');

    expect(RuntimeCache::get('key'))->toBe('value');

    RuntimeCache::delete('key');

    expect(RuntimeCache::get('key'))->toBeNull();

    RuntimeCache::put('key', 'value');
    RuntimeCache::flush();

    expect(RuntimeCache::get('key'))->toBeNull();
});

it('returns all memoized values', function () {
    expect(RuntimeCache::all())->toBe([]);

    RuntimeCache::put(['tests:1' => 1, 'tests:2' => 2]);
    RuntimeCache::put('tests:3', 3, -100);

    expect(RuntimeCache::all())->toBe([
        'tests:1' => 1,
        'tests:2' => 2,
    ])->and(RuntimeCache::all('tests:1'))->toBe([
        'tests:1' => 1,
    ])->and(RuntimeCache::all('tests:3'))->toBe([]);
});

it('remembers values only once', function () {
    $count = 0;

    RuntimeCache::remember('key', function () use (&$count) {
        $count++;

        return 'value';
    });

    RuntimeCache::rememberForever('key', function () use (&$count) {
        $count++;

        return 'not_used';
    });

    expect($count)->toBe(1)
        ->and(RuntimeCache::get('key'))->toBe('value');
});
