<?php

declare(strict_types=1);

use Pin\Models\Model;
use Pin\Support\Facades\Tree;

it('checks tree path errors', function () {
    $models = [
        'paths empty' => [
            new Model(['id' => 1, 'paths' => []]),
        ],
        'level [999] not equal paths length [1]' => [
            new Model(['id' => 2, 'level' => 999, 'paths' => [2]]),
        ],
        'paths last segment [999] not self id [3]' => [
            new Model(['id' => 3, 'level' => 1, 'paths' => [999]]),
        ],
        'paths invalid' => [
            new Model(['id' => 4, 'level' => 2, 'paths' => [99, 4]]),
        ],
        'parent [99] not exist' => [
            new Model(['id' => 5, 'level' => 1, 'pid' => 99, 'paths' => [5]]),
        ],
        'expect=[1,2] got=[3,2]' => [
            new Model(['id' => 1, 'level' => 1, 'pid' => 0, 'paths' => [1]]),
            new Model(['id' => 2, 'level' => 2, 'pid' => 1, 'paths' => [3, 2]]),
        ],
        'expect=[1,2] got=[99,100,2]' => [
            new Model(['id' => 1, 'level' => 1, 'pid' => 0, 'paths' => [1]]),
            new Model(['id' => 2, 'level' => 3, 'pid' => 1, 'paths' => [99, 100, 2]]),
        ],
    ];

    foreach ($models as $str => $items) {
        $errs = Tree::check(collect($items));
        expect($errs)->toHaveCount(1);
        expect($errs[0]['message'])->toContain($str);
    }

    $validItems = collect([
        new Model(['id' => 1, 'level' => 1, 'pid' => 0, 'paths' => [1]]),
        new Model(['id' => 2, 'level' => 2, 'pid' => 1, 'paths' => [1, 2]]),
    ]);
    expect(Tree::check($validItems))->toHaveCount(0);
});
