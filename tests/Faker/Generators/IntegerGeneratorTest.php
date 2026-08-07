<?php

declare(strict_types=1);

use Pin\Faker\Fake;
use Pin\Faker\Generators\IntegerGenerator;
use Pin\Faker\RuleBag;

it('generates integer values', function () {
    $generator = new IntegerGenerator();

    for ($i = 0; $i < 100; $i++) {
        $value = $generator->generate(
            Fake::in(),
            new RuleBag([])
        );

        expect($value)
            ->toBeGreaterThanOrEqual(1)
            ->toBeLessThanOrEqual(10000);

        $value = $generator->generate(
            Fake::in(100),
            new RuleBag([])
        );

        expect($value)
            ->toBeGreaterThanOrEqual(100)
            ->toBeLessThanOrEqual(10000);

        $value = $generator->generate(
            Fake::in(100, 200),
            new RuleBag([])
        );

        expect($value)
            ->toBeGreaterThanOrEqual(100)
            ->toBeLessThanOrEqual(200);
    }
});
