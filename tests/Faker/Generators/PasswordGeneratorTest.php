<?php

declare(strict_types=1);

use Pin\Faker\Fake;
use Pin\Faker\Generators\PasswordGenerator;
use Pin\Faker\RuleBag;
use Pin\Support\Facades\Password;

it('generates encoded passwords', function () {
    $generator = new PasswordGenerator();

    $value = $generator->generate(
        Fake::password(),
        new RuleBag([])
    );

    expect(Password::decodeFromRequest($value))->toBe(Password::encode('test@123'));

    $value = $generator->generate(
        Fake::password('123456'),
        new RuleBag([])
    );
    expect(Password::decodeFromRequest($value))->toBe(Password::encode('123456'));
});
