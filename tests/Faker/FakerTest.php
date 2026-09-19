<?php

declare(strict_types=1);

use Pin\Faker\Fake;
use Pin\Faker\MissingValue;

it('generates fake data', function () {
    $rules = [
        'infer' => 'string',
        'missing' => 's',
        'id' => ['string', Fake::make('in', [100, 200])],
        'name' => 'string|fake:name',
        'status' => ['string', Fake::make(fn () => 1)],
        'roles.*' => 'integer',
    ];

    $data = Fake::generate($rules);

    expect(array_keys($data))->toBe(['infer', 'id', 'name', 'status']);

    expect(strlen($data['infer']))->toBe(16);

    expect($data['id'])
        ->toBeGreaterThanOrEqual(100)
        ->toBeLessThanOrEqual(200);

    expect($data['status'])->toBe(1);
});

it('generates integers from string rules', function () {
    $data = Fake::generate(['id' => 'integer|fake:integer,0,0']);

    expect($data)->toBe(['id' => 0]);
});

it('skips wildcard fields before inferring rules', function () {
    Fake::registerInfer('wildcard', function () {
        throw new LogicException('Wildcard fields should be skipped.');
    });

    expect(Fake::generate(['users.*.id' => 'wildcard']))->toBe([]);
});

it('supports explicit rule inference', function (array|string $rules, array $expected) {
    expect(Fake::generate(['id' => $rules]))->toBe($expected);
})->with([
    'object rule' => [['integer', 'min:0', 'max:0', Fake::infer()], ['id' => 0]],
    'string rule' => ['integer|min:0|max:0|fake:infer', ['id' => 0]],
    'missing inference' => ['required|fake:infer', []],
]);

it('keeps empty values and skips missing values', function () {
    $data = Fake::generate([
        'null' => [Fake::make(fn () => null)],
        'zero' => [Fake::make(fn () => 0)],
        'false' => [Fake::make(fn () => false)],
        'empty' => [Fake::make(fn () => '')],
        'missing' => [Fake::make(fn () => new MissingValue())],
    ]);

    expect($data)->toBe(['null' => null, 'zero' => 0, 'false' => false, 'empty' => '']);
});
