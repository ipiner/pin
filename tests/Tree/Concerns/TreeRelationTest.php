<?php

declare(strict_types=1);

use App\Factories\MenuFactory;
use App\Models\Menu;
use Pin\Testing\Concerns\InteractsWithRedis;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class, InteractsWithRedis::class);

it('loads children relation', function () {
    MenuFactory::new()->create(['id' => 1, 'name' => 'Foo']);
    MenuFactory::new()->create(['id' => 2, 'pid' => 1, 'name' => 'Bar']);

    $item = Menu::with('children')->find(1);

    expect($item->children[0]->name)->toBe('Bar');
});
