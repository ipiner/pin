<?php

declare(strict_types=1);

use Pin\Faker\Fake;
use Pin\Faker\Generators\InGenerator;
use Pin\Faker\RuleBag;

it('generates values from in rule', function () {
    $generator = new InGenerator();
    $value = $generator->generate(Fake::in(1, 1), new RuleBag([]));
    expect($value)->toBe(1);

    $value = $generator->generate(Fake::in('1', '1'), new RuleBag([]));
    expect($value)->toBe('1');

    $value = $generator->generate(Fake::in('1', '1'), new RuleBag('integer'));
    expect($value)->toBe(1);

    $value = $generator->generate(Fake::in('a', 'b', 'c'), new RuleBag([]));
    expect(in_array($value, ['a', 'b', 'c'], true))->toBeTrue();
});
