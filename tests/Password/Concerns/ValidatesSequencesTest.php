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
]);
