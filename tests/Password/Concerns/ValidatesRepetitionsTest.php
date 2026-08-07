<?php

declare(strict_types=1);

use Pin\Errors\Errors;

it('validates max repeated characters', function (
    int $count,
    string $value,
    int $expected,
) {
    $this->rule
        ->value($value)
        ->maxRepeatedCharacters($count);

    expect($this->invoker->validateMaxRepeatedCharacters())->toBe($expected);
})->with([
    'too many repeated letters' => [
        3,
        'aaab',
        Errors::PasswordTooManyRepeats->code(),
    ],

    'allowed repeated letters' => [
        4,
        'aaab',
        0,
    ],

    'too many repeated numbers' => [
        5,
        '11111a',
        Errors::PasswordTooManyRepeats->code(),
    ],
]);

it('returns zero when max repeated characters validation is disabled', function () {
    expect($this->invoker->validateMaxRepeatedCharacters())->toBe(0);
});
