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
    'empty value' => [1, '', 0],
    'single character' => [1, 'a', Errors::PasswordTooManyRepeats->code()],
    'separated repeats' => [3, 'aabaa', 0],
    'repeats at the end' => [3, 'baaa', Errors::PasswordTooManyRepeats->code()],
    'repeated symbols' => [3, 'a!!!', Errors::PasswordTooManyRepeats->code()],
]);

it('returns zero when max repeated characters validation is disabled', function () {
    expect($this->invoker->validateMaxRepeatedCharacters())->toBe(0);
});
