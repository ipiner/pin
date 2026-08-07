<?php

declare(strict_types=1);

use Pin\Cache\HashKey;

it('parses hash key', function ($input, $expectedKey, $expectedField) {
    $hashKey = HashKey::parse($input);

    expect($hashKey->key)->toBe($expectedKey);
    expect($hashKey->field)->toBe($expectedField);
})->with([
    'no field' => ['users', 'users', ''],
    'with field' => ['users:all:1', 'users:all', '1'],
]);

it('parses many hash keys', function (array $input, array $expected) {
    expect(HashKey::parseMany($input))->toBe($expected);
})->with([
    'simple case' => [
        ['users:1', 'users:2'],
        ['users', ['1', '2']],
    ],
]);
