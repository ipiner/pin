<?php

declare(strict_types=1);

use Pin\Errors\Errors;

it('validates max length', function (int $max, int $expected) {
    $this->rule->max($max);

    expect($this->invoker->validateMaxLength())->toBe($expected);
})->with([
    'valid' => [8, 0],
    'too long' => [3, Errors::PasswordTooLong->code()],
]);

it('validates min length', function (int $min, int $expected) {
    $this->rule->min($min);

    expect($this->invoker->validateMinLength())->toBe($expected);
})->with([
    'valid' => [3, 0],
    'too short' => [8, Errors::PasswordTooShort->code()],
]);
