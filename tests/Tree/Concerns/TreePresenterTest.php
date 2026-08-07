<?php

declare(strict_types=1);

use App\Factories\MenuFactory;
use Pin\Testing\Concerns\InteractsWithRedis;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class, InteractsWithRedis::class);

it('builds full name', function () {
    // 创建根节点
    $root = MenuFactory::new()->create(['id' => 1, 'name' => 'Foo']);

    // 创建子节点
    $item = MenuFactory::new()->create(['id' => 2, 'name' => 'Bar', 'pid' => 1]);
    expect($item->fullName)->toBe('Foo / Bar');

    $root->delete();
    expect(str_contains($item->fullName, '不存在或已删除'))->toBeTrue();
    expect(in_array('不存在或已删除', $item->namePath(null)))->toBeTrue();
});
