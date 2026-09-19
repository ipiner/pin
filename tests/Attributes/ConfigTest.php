<?php

declare(strict_types=1);

namespace Pin\Tests\Attributes;

use Pin\Attributes\Config;
use Pin\Route\Attributes\Name;
use Pin\Route\Attributes\Prefix;
use Pin\Route\InteractsWithRoute;
use Pin\Route\Routable;
use Pin\Route\RouteDefinition;
use Pin\Support\Facades\RuntimeCache;

class TestConfig extends Config
{
}

class CustomPrefixConfig extends Config
{
    protected const string CONFIG_PREFIX = 'config://';
}

class UppercaseConfig extends Config
{
    protected function resolveValue(string $value): mixed
    {
        return strtoupper(parent::resolveValue($value));
    }
}

#[Prefix('$config.attributes_test.route.prefix')]
enum ConfiguredRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Name('$config.attributes_test.route.name')]
    case Index = 'GET:/items';
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

it('preserves literal strings that are not config references', function (string $value) {
    expect(new Config($value)->value)->toBe($value);
})->with(['', '0', '$config', '$configx.app.name', 'prefix.$config.app.name', ' app.name ']);

it('preserves the type and value of resolved configuration', function (mixed $value) {
    config(['attributes_test.value' => $value]);

    expect(new Config('$config.attributes_test.value')->value)->toBe($value);
})->with([
    'false' => [false],
    'zero' => [0],
    'float' => [1.5],
    'empty string' => [''],
    'string' => ['configured'],
    'null' => [null],
    'empty array' => [[]],
    'nested array' => [['nested' => ['value' => true]]],
]);

it('resolves values when each instance is constructed', function () {
    config(['attributes_test.value' => 'before']);
    $before = new Config('$config.attributes_test.value');
    config(['attributes_test.value' => 'after']);

    expect($before->value)->toBe('before')
        ->and(new Config('$config.attributes_test.value')->value)->toBe('after');
});

it('allows subclasses to customize the reference prefix and resolution', function () {
    config(['attributes_test.value' => 'configured']);

    expect(new CustomPrefixConfig('config://attributes_test.value')->value)->toBe('configured')
        ->and(new UppercaseConfig('$config.attributes_test.value')->value)->toBe('CONFIGURED')
        ->and(new UppercaseConfig('literal')->value)->toBe('LITERAL');
});

it('resolves route prefixes and names through their config attributes', function () {
    config(['attributes_test.route' => ['prefix' => 'admin', 'name' => 'items.index']]);

    try {
        $route = new RouteDefinition(ConfiguredRoute::Index);

        expect($route->uri)->toBe('/admin/items')
            ->and($route->name)->toBe('items.index');
    } finally {
        RuntimeCache::flush();
    }
});
