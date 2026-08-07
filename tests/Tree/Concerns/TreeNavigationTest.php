<?php

declare(strict_types=1);

use App\Factories\MenuFactory;
use App\Models\Menu;
use Pin\Testing\Concerns\InteractsWithRedis;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class, InteractsWithRedis::class);

it('returns correct descendants', function () {
    [$item1, $item2, $item3] = createMenus();
    expect($item1->descendants())->toHaveCount(2);
    expect($item2->descendants())->toHaveCount(1);
    expect($item3->descendants())->toHaveCount(0);
});

it('returns correct ancestors', function () {
    [$item1, $item2, $item3] = createMenus();
    expect($item1->ancestors())->toHaveCount(0);
    expect($item2->ancestors())->toHaveCount(1);
    expect($item3->ancestors())->toHaveCount(2);
})->depends('it returns correct descendants');

/**
 * @return Menu[]
 */
function createMenus(): array
{
    $item1 = MenuFactory::new()->create();
    $item2 = MenuFactory::new()->create(['pid' => $item1->id]);
    $item3 = MenuFactory::new()->create(['pid' => $item2->id]);

    return [$item1, $item2, $item3];
}
