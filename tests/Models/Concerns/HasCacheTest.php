<?php

declare(strict_types=1);

use App\Factories\AdminFactory;
use App\Factories\MenuFactory;
use App\Models\Menu;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Pin\Models\Cache\CacheType;
use Pin\Testing\Concerns\InteractsWithRedis;
use Pin\Tests\InteractsWithDatabase;
use Pin\Tests\Models\Models\Admin;
use Pin\Tests\Models\Models\User;

uses(
    InteractsWithDatabase::class,
    InteractsWithRedis::class
);

it('returns correct cache type', function () {
    expect(User::cacheType())->toBe(CacheType::None)
        ->and(Admin::cacheType())->toBe(CacheType::CacheItem)
        ->and(Menu::cacheType())->toBe(CacheType::CacheAll);
});

it('finds model by id', function () {
    $id = random_int(1, 10000);
    expect(Admin::find($id))->toBeNull();

    AdminFactory::new(['id' => $id])->create();
    expect(Admin::findOrFail($id)->id)->toBe($id);
});

it('throws exception when model not found', function () {
    expect(fn () => Admin::findOrFail(-1))
        ->toThrow(ModelNotFoundException::class);
});

it('finds all models', function () {
    expect(Menu::findAll())->toBeEmpty();

    $id = random_int(1, 10000);
    MenuFactory::new(['id' => $id])->create();
    expect(Menu::findAll())
        ->toHaveKey($id)
        ->and(Menu::findAll()[$id]->id)->toBe($id);
});

it('finds model by field', function () {
    $id = random_int(1, 10000);
    expect(Admin::findBy('id', $id))->toBeNull();

    $item = AdminFactory::new(['id' => $id])->create();
    expect(Admin::findBy('id', $id)->id)->toBe($item->id);

    // cache all
    expect(Menu::findBy('id', $id))->toBeNull();

    $item = MenuFactory::new(['id' => $id])->create();
    expect(Menu::findBy('id', $id)->id)
        ->toBe($item->id)
        ->and(Menu::findBy('name', strtoupper($item->name))->id)
        ->toBe($item->id);
});

it('finds many models', function () {
    expect(Admin::findMany([1, 2]))->toBeEmpty();

    AdminFactory::new(['id' => 2])->create();
    $items = Admin::findMany([1, 2]);

    expect($items)->toHaveCount(1)
        ->and($items[2]->id)->toBe(2);

    // cache all
    expect(Menu::findMany([1, 2]))->toBeEmpty();

    MenuFactory::new(['id' => 2])->create();
    $items = Menu::findMany([1, 2]);

    expect($items)->toHaveCount(1)
        ->and($items[2]->id)->toBe(2);
});
