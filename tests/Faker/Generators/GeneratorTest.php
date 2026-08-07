<?php

declare(strict_types=1);

use Pin\Faker\Fake;
use Pin\Faker\Generators\Generator;
use Pin\Faker\RuleBag;

it('generates values', function () {
    $generator = Generator::make('String');
    $value = $generator->generate(
        Fake::string(),
        new RuleBag('required')
    );

    expect(strlen($value))->toBe(16);
});

it('returns null when nullable rule is triggered', function () {
    $generator = Generator::make('String');

    $value = $generator->generate(
        Fake::string(),
        new RuleBag('nullable:1000')
    );

    expect($value)->toBeNull();
});
