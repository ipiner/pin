<?php

declare(strict_types=1);

use Pin\Support\Arr;

it('masks sensitive values', function () {
    $data = [
        'password' => 'abc',
        'database' => [
            'default' => [
                'host' => 'localhost',
                'Password' => 'test',
            ],
        ],
    ];

    $data = Arr::maskSensitive($data);

    expect($data['password'])->toBe('abc')
        ->and($data['database']['default']['host'])->toBe('localhost')
        ->and($data['database']['default']['Password'])->toBe('tes******');
});

it('merges arrays', function () {
    $a = [
        'db' => [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'username' => 'root',
                    'password' => '',
                ],
            ],
        ],
        1 => [
            'one',
            'two',
        ],
    ];

    $b = [
        'db' => [
            'connections' => [
                'default' => [
                    'host' => '127.0.0.1',
                    'password' => 'root',
                ],
                'slave' => [
                    'host' => 'slave host',
                ],
            ],
        ],
        1 => [
            'three',
        ],
    ];

    $array = Arr::merge([], $a);

    expect($array)->toBe($a);

    $array = Arr::merge($a, $b, ['more' => 'more']);

    expect($array['db']['connections']['default']['host'])->toBe('127.0.0.1')
        ->and($array['db']['connections']['default']['password'])->toBe('root')
        ->and($array['db']['connections']['slave']['host'])->toBe('slave host')
        ->and($array['more'])->toBe('more')
        ->and($array[2][0])->toBe('three');

    $array = Arr::merge(true, $a, $b);

    expect($array[1][0])->toBe('three')
        ->and(isset($array[2]))->toBeFalse();
});

it('converts null values to empty strings recursively', function () {
    $data = [
        'a' => null,
        'b' => [
            'a' => null,
            'c' => [
                'a' => null,
            ],
        ],
    ];

    $data = Arr::nullToEmptyString($data);

    expect($data['a'])->toBe('')
        ->and($data['b']['a'])->toBe('')
        ->and($data['b']['c']['a'])->toBe('');
});

it('converts flat arrays to tree structure', function () {
    $array2tree = [
        1 => [
            'id' => 1,
            'pid' => 0,
            'name' => 'item1',
        ],
        2 => [
            'id' => 2,
            'pid' => 0,
            'name' => 'item2',
        ],
        3 => [
            'id' => 3,
            'pid' => 0,
            'name' => 'item3',
        ],
        4 => [
            'id' => 4,
            'pid' => 2,
            'name' => 'item2-4',
        ],
        6 => [
            'id' => 6,
            'pid' => 5,
            'name' => 'item2-5-6',
        ],
        5 => [
            'id' => 5,
            'pid' => 2,
            'name' => 'item2-5',
        ],
    ];

    $data = Arr::toTree($array2tree);

    expect($data)->toHaveCount(3)
        ->and(isset($data[0], $data[1], $data[2]))->toBeTrue()
        ->and(isset($data[0]['children']))->toBeFalse()
        ->and(isset($data[1]['children']))->toBeTrue();
});
