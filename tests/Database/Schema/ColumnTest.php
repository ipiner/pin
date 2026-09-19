<?php

declare(strict_types=1);

use Pin\Database\Schema\Column;

it('resolves column label', function (array $attributes, string $expected) {
    $column = new Column($attributes);

    expect($column->label)->toBe($expected);
})->with([
    'missing comment' => [['name' => 'created_at'], 'Created At'],
    'default generated label' => [
        [
            'name' => 'created_at',
            'comment' => '',
        ],
        'Created At',
    ],

    'comment label' => [
        [
            'name' => 'created_at',
            'comment' => '创建时间|sss',
        ],
        '创建时间',
    ],
]);
