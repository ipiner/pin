<?php

declare(strict_types=1);

use Pin\Attributes\Config;

class TestConfig extends Config
{
}

it('keeps plain value', function () {
    expect(new TestConfig('cache.stores.array.driver')->value)
        ->toBe('cache.stores.array.driver');
});

it('resolves config value', function () {
    expect(new TestConfig('$config.cache.stores.array.driver')->value)
        ->toBe('array');
});

it('returns null when config does not exist', function () {
    expect(new TestConfig('$config.abc.cache.stores.array.driver')->value)
        ->toBeNull();
});
