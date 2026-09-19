<?php

declare(strict_types=1);

use Pin\Errors\Errors;

it('validates max sequential characters', function (
    int $count,
    string $value,
    int $expected,
) {
    $this->rule
        ->value($value)
        ->maxSequentialCharacters($count);

    expect($this->invoker->validateMaxSequentialCharacters())->toBe($expected);
})->with([
    'ascending numbers' => [
        3,
        '1234',
        Errors::PasswordSequenceTooLong->code(),
    ],

    'descending numbers' => [
        3,
        '4321',
        Errors::PasswordSequenceTooLong->code(),
    ],

    'ascending letters' => [
        3,
        'abcd',
        Errors::PasswordSequenceTooLong->code(),
    ],

    'descending letters' => [
        3,
        'DCBA',
        Errors::PasswordSequenceTooLong->code(),
    ],
    'empty value' => [1, '', 0],
    'single character' => [1, 'a', Errors::PasswordSequenceTooLong->code()],
    'short sequence' => [4, 'abc', 0],
    'direction change' => [3, 'aba', 0],
    'repeated character breaks sequence' => [3, 'abbc', 0],
    'sequence after direction change' => [4, 'abcdcba', Errors::PasswordSequenceTooLong->code()],
    'sequence at the end' => [3, '!xyz', Errors::PasswordSequenceTooLong->code()],
]);
