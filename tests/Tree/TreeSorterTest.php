<?php

declare(strict_types=1);

use Pin\Support\Facades\Tree;

it('sorts tree items', function () {
    $items = collect([
        ['id' => 3, 'pid' => 1, 'sort' => 2],
        ['id' => 1, 'pid' => 1, 'sort' => 1],
        ['id' => 2, 'pid' => 2, 'sort' => 1],
        ['id' => 4, 'pid' => 1, 'sort' => 1],
    ]);

    $sorted = Tree::sort($items)->values();

    expect($sorted->toArray())->toEqual([
        ['id' => 1, 'pid' => 1, 'sort' => 1],
        ['id' => 4, 'pid' => 1, 'sort' => 1],
        ['id' => 3, 'pid' => 1, 'sort' => 2],
        ['id' => 2, 'pid' => 2, 'sort' => 1],
    ]);
});
