<?php

declare(strict_types=1);

use App\Factories\MenuFactory;
use Pin\Exceptions\Exception;
use Pin\Testing\Concerns\InteractsWithRedis;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class, InteractsWithRedis::class);

beforeEach(function () {
    config()->set('pin.tree.max_level', 1);
});

it('cannot create node beyond max level', function () {
    $root = MenuFactory::new()->create();

    expect(fn () => MenuFactory::new()->create([
        'name' => 'fourth',
        'pid' => $root->id,
    ]))->toThrow(Exception::class, '层级不能大于1');
});

it('cannot move node beyond max level', function () {
    $root = MenuFactory::new()->create([
        'name' => 'root',
    ]);

    $a = MenuFactory::new()->create([
        'name' => 'a',
    ]);

    expect(fn () => $root->update([
        'pid' => $a->id,
    ]))->toThrow(Exception::class, '层级不能大于1');
});

it('cannot move subtree beyond max level', function () {
    config()->set('pin.tree.max_level', 2);

    $root = MenuFactory::new()->create([
        'name' => 'root',
    ]);
    MenuFactory::new()->create([
        'name' => 'node',
        'pid' => $root->id,
    ]);

    $target = MenuFactory::new()->create([
        'name' => 'target',
    ]);

    expect(fn () => $root->update([
        'pid' => $target->id,
    ]))->toThrow(Exception::class, '层级不能大于2');
});
