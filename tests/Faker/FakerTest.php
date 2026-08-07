<?php

declare(strict_types=1);

use Pin\Faker\Fake;

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
