<?php

declare(strict_types=1);

use Pin\Errors\Errors;
use Pin\Faker\Fake;
use Pin\Faker\Generators\EnumGenerator;

it('generates values from enum', function () {
    $generator = new EnumGenerator();
    $value = $generator->generate(Fake::enum(Errors::class));
    expect(Errors::from($value))->toBeInstanceOf(Errors::class);
});
