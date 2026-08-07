<?php

declare(strict_types=1);

use Pin\Faker\Fake;
use Pin\Support\Facades\Password;

it('generates encoded passwords', function () {
    $value = Fake::generate(['password' => [Fake::password()]])['password'];
    expect(Password::decodeFromRequest($value))->toBe(Password::encode('test@123'));

    $value = Fake::generate(['password' => [Fake::password('123456')]])['password'];
    expect(Password::decodeFromRequest($value))->toBe(Password::encode('123456'));
});
