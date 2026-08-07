<?php

declare(strict_types=1);

use Pin\Support\DataBag;

it('creates new data bag', function () {
    expect(DataBag::new(null)->toArray())->toBe([])
        ->and(DataBag::new([])->toArray())->toBe([])
        ->and(DataBag::new(new DataBag())->set('a', 1)->toArray())->toBe(['a' => 1])
        ->and(DataBag::new(['a' => 1])->toArray())->toBe(['a' => 1]);
});

it('returns values in non strict mode', function () {
    $bag = new DataBag([], false);

    expect($bag->value('a'))->toBeNull()
        ->and($bag->value('a', false))->toBeFalse();

    $bag['a'] = null;

    expect($bag->value('a', false))->toBeNull();
});

it('throws exception when accessing missing key in strict mode', function () {
    $bag = new DataBag([]);

    $bag->missing_key;
})->throws(RuntimeException::class);
