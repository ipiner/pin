<?php

declare(strict_types=1);

use App\Factories\MenuFactory;
use App\Models\Menu;
use Pin\Testing\Concerns\InteractsWithRedis;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class, InteractsWithRedis::class);

it('builds tree path', function () {
    expect(Menu::buildPath(1, 0))->toBe('1');

    $item = MenuFactory::new()->create();
    expect(Menu::buildPath(1, $item->id))->toBe($item->id.'/1');
});

it('parses paths', function (?string $path, array $expected) {
    $menu = new Menu(['path' => $path]);
    expect($menu->paths)->toBe($expected);
    expect($menu->paths())->toBe($expected);
    expect(json_decode(json_encode($menu))->paths)->toBe($expected);
})->with([
    [null, []],
    ['1/2', [1, 2]],
]);

it('relocates a subtree', function () {
    MenuFactory::new()->create(['id' => 1]);
    MenuFactory::new()->create(['id' => 12, 'pid' => 1]);

    MenuFactory::new()->create(['id' => 22]);
    MenuFactory::new()->create(['id' => 223, 'pid' => 22]);
    MenuFactory::new()->create(['id' => 2234, 'pid' => 223]);

    expect(Menu::find(223)->path)->toBe('22/223')
        ->and(Menu::find(223)->level)->toBe(2)
        ->and(Menu::find(2234)->path)->toBe('22/223/2234');

    $this->invoker(Menu::class)->relocateSubtree('22', '1/12/22');

    expect(Menu::find(223)->path)->toBe('1/12/22/223')
        ->and(Menu::find(223)->level)->toBe(4)
        ->and(Menu::find(2234)->path)->toBe('1/12/22/223/2234');
});
