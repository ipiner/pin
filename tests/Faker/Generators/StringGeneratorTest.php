<?php

use Pin\Faker\Fake;
use Pin\Faker\FakeRule;
use Pin\Faker\Generators\StringGenerator;
use Pin\Faker\RuleBag;

it('generates strings with the correct length', function (int $length, FakeRule $rule) {
    $generator = new StringGenerator();
    $value = $generator->generate(
        $rule,
        new RuleBag([])
    );

    expect(strlen($value))->toBe($length);
})->with([
    [16, Fake::string()],
    [32, Fake::string(32)],
]);
