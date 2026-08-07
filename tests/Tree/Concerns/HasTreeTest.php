<?php

declare(strict_types=1);

use App\Factories\MenuFactory;
use Pin\Testing\Concerns\InteractsWithRedis;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class, InteractsWithRedis::class);

it('bootstraps tree items', function () {
    $item = MenuFactory::new()->create();
    expect($item->sort)->toBe($item->id)
        ->and($item->level)->toBe(1);

    $item = MenuFactory::new()->create(['sort' => time()]);
    expect($item->sort)->not->toBe($item->id);

    $item->update(['sort' => -1]);
    expect($item->sort)->toBe($item->id);
});
