<?php

declare(strict_types=1);

use Pin\Errors\Errors;

it('validates letters', function (string $value, int $expected) {
    $this->rule->letters()->value($value);

    expect($this->invoker->validateLetters())->toBe($expected);
})->with([
    'contains letter' => ['123a', 0],
    'missing letter' => ['1234', Errors::PasswordRequiresLetter->code()],
]);

it('validates lowercase', function (string $value, int $expected) {
    $this->rule->lowers()->value($value);

    expect($this->invoker->validateLowercase())->toBe($expected);
})->with([
    'contains lowercase' => ['123a', 0],
    'missing lowercase' => ['123A', Errors::PasswordRequiresLowercase->code()],
]);

it('validates mixed case', function (string $value, int $expected) {
    $this->rule->mixedCase()->value($value);

    expect($this->invoker->validateMixedCase())->toBe($expected);
})->with([
    'valid' => ['12aB', 0],
    'missing upper' => ['123a', Errors::PasswordRequiresMixedCase->code()],
    'missing lower' => ['123B', Errors::PasswordRequiresMixedCase->code()],
]);

it('validates numbers', function (string $value, int $expected) {
    $this->rule->numbers()->value($value);

    expect($this->invoker->validateNumbers())->toBe($expected);
})->with([
    'contains number' => ['1234', 0],
    'missing number' => ['test@', Errors::PasswordRequiresNumber->code()],
]);

it('validates required character types', function (
    int $count,
    string $value,
    int $expected,
) {
    $this->rule
        ->value($value)
        ->requiredCharacterTypes($count);

    expect(
        $this->invoker->validateRequiredCharacterTypes()
    )->toBe($expected);
})->with([
    '2 types lowercase' => [2, '123a', 0],
    '2 types uppercase' => [2, '123A', 0],
    '2 types symbol' => [2, '123#', 0],
    '2 types letters and symbol' => [2, 'abc#', 0],
    '3 types valid' => [3, '1abc#', 0],

    'insufficient numeric only' => [
        2,
        '123456',
        Errors::PasswordInsufficientTypes->code(),
    ],

    'insufficient alpha only' => [
        2,
        'abcd',
        Errors::PasswordInsufficientTypes->code(),
    ],

    'requires all types mixed' => [
        3,
        '123aB',
        Errors::PasswordRequiresAllTypes->code(),
    ],

    'requires all types numeric symbol' => [
        3,
        '123#',
        Errors::PasswordRequiresAllTypes->code(),
    ],

    'requires all types alpha symbol' => [
        3,
        'abc#',
        Errors::PasswordRequiresAllTypes->code(),
    ],
]);

it('validates symbols', function (string $value, int $expected) {
    $this->rule->symbols()->value($value);

    expect($this->invoker->validateSymbols())->toBe($expected);
})->with([
    'contains symbol' => ['1234@', 0],
    'missing symbol' => ['123test', Errors::PasswordRequiresSymbol->code()],
]);

it('validates uppercase', function (string $value, int $expected) {
    $this->rule->uppers()->value($value);

    expect($this->invoker->validateUppercase())->toBe($expected);
})->with([
    'contains uppercase' => ['123A', 0],
    'missing uppercase' => ['123a', Errors::PasswordRequiresUppercase->code()],
]);

it('returns zero when validation is disabled', function () {
    expect($this->invoker->validateLetters())->toBe(0)
        ->and($this->invoker->validateLowercase())->toBe(0)
        ->and($this->invoker->validateMixedCase())->toBe(0)
        ->and($this->invoker->validateNumbers())->toBe(0)
        ->and($this->invoker->validateRequiredCharacterTypes())->toBe(0)
        ->and($this->invoker->validateSymbols())->toBe(0)
        ->and($this->invoker->validateUppercase())->toBe(0);
});
