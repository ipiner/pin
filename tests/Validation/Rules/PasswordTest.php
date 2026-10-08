<?php

declare(strict_types=1);

use Pin\Errors\Errors;
use Pin\Validation\Rules\Password;

beforeEach(function () {
    $this->rule = new TestPassword();
    $this->invoker = $this->invoker($this->rule);
    $this->invoker->value = '123456';
});

it('checks passes method', function () {
    $this->rule->lowers();
    $this->invoker->errors = [];

    expect($this->invoker->passes())->toBeFalse()
        ->and(isset($this->invoker->errors[Errors::PasswordRequiresLowercase->code()]))->toBeTrue()
        ->and(isset($this->invoker->errors[Errors::PasswordRequiresSymbol->code()]))->toBeFalse();

    $this->invoker->errors = [];
    $this->rule->value('test123A')->requiredCharacterTypes(3);

    expect($this->invoker->passes())->toBeFalse()
        ->and(isset($this->invoker->errors[Errors::PasswordRequiresLowercase->code()]))->toBeFalse()
        ->and(isset($this->invoker->errors[Errors::PasswordRequiresAllTypes->code()]))->toBeTrue();
});

it('validates password', function () {
    $errors = [];
    (new Password())->validate(
        'password',
        '123456',
        function ($message) use (&$errors) {
            $errors[] = $message;
        }
    );

    expect(str_starts_with($errors[0], Errors::PasswordTooShort->code().'|'))->toBeTrue();

    $errors = [];
    (new Password(false))->letters()->validate(
        'password',
        '123456',
        function ($message) use (&$errors) {
            $errors[] = $message;
        }
    );

    expect(str_starts_with($errors[0], Errors::PasswordTooShort->code().'|'))->toBeFalse();
});

it('clears previous errors when reusing a password rule', function () {
    $rule = new Password()->min(1)->letters();
    $errors = [];
    $fail = function ($message) use (&$errors) {
        $errors[] = $message;
    };

    $rule->validate('password', '12', $fail);
    expect($errors)->toHaveCount(1)
        ->and($errors[0])->toStartWith(Errors::PasswordRequiresLetter->code().'|');

    $errors = [];
    $rule->validate('password', 'a b', $fail);
    expect($errors)->toHaveCount(1)
        ->and($errors[0])->toStartWith(Errors::PasswordContainsWhitespace->code().'|');

    $errors = [];
    $rule->validate('password', 'aB7!', $fail);
    expect($errors)->toBe([]);
});

it('keeps composition settings unchanged when counting character types', function () {
    $rule = new Password()->min(1)->requiredCharacterTypes(2);
    $errors = [];
    $fail = function ($message) use (&$errors) {
        $errors[] = $message;
    };

    $rule->validate('password', '1a', $fail);
    expect($errors)->toBe([]);

    $rule->requiredCharacterTypes(1)->validate('password', 'word', $fail);
    expect($errors)->toBe([]);
});

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
    'newline between cases' => ["a\nB", 0],
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

    expect($this->invoker->validateRequiredCharacterTypes())->toBe($expected);
})->with([
    '2 types lowercase' => [2, '123a', 0],
    '2 types uppercase' => [2, '123A', 0],
    '2 types symbol' => [2, '123#', 0],
    '2 types letters and symbol' => [2, 'abc#', 0],
    '3 types valid' => [3, '1abc#', 0],
    'insufficient numeric only' => [2, '123456', Errors::PasswordInsufficientTypes->code()],
    'insufficient alpha only' => [2, 'abcd', Errors::PasswordInsufficientTypes->code()],
    'requires all types mixed' => [3, '123aB', Errors::PasswordRequiresAllTypes->code()],
    'requires all types numeric symbol' => [3, '123#', Errors::PasswordRequiresAllTypes->code()],
    'requires all types alpha symbol' => [3, 'abc#', Errors::PasswordRequiresAllTypes->code()],
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

it('returns zero when composition validation is disabled', function () {
    expect($this->invoker->validateLetters())->toBe(0)
        ->and($this->invoker->validateLowercase())->toBe(0)
        ->and($this->invoker->validateMixedCase())->toBe(0)
        ->and($this->invoker->validateNumbers())->toBe(0)
        ->and($this->invoker->validateRequiredCharacterTypes())->toBe(0)
        ->and($this->invoker->validateSymbols())->toBe(0)
        ->and($this->invoker->validateUppercase())->toBe(0);
});

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
    'too many repeated letters' => [3, 'aaab', Errors::PasswordTooManyRepeats->code()],
    'allowed repeated letters' => [4, 'aaab', 0],
    'too many repeated numbers' => [5, '11111a', Errors::PasswordTooManyRepeats->code()],
    'empty value' => [1, '', 0],
    'single character' => [1, 'a', Errors::PasswordTooManyRepeats->code()],
    'separated repeats' => [3, 'aabaa', 0],
    'repeats at the end' => [3, 'baaa', Errors::PasswordTooManyRepeats->code()],
    'repeated symbols' => [3, 'a!!!', Errors::PasswordTooManyRepeats->code()],
]);

it('returns zero when max repeated characters validation is disabled', function () {
    expect($this->invoker->validateMaxRepeatedCharacters())->toBe(0);
});

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
    'ascending numbers' => [3, '1234', Errors::PasswordSequenceTooLong->code()],
    'descending numbers' => [3, '4321', Errors::PasswordSequenceTooLong->code()],
    'ascending letters' => [3, 'abcd', Errors::PasswordSequenceTooLong->code()],
    'descending letters' => [3, 'DCBA', Errors::PasswordSequenceTooLong->code()],
    'empty value' => [1, '', 0],
    'single character' => [1, 'a', Errors::PasswordSequenceTooLong->code()],
    'short sequence' => [4, 'abc', 0],
    'direction change' => [3, 'aba', 0],
    'repeated character breaks sequence' => [3, 'abbc', 0],
    'sequence after direction change' => [4, 'abcdcba', Errors::PasswordSequenceTooLong->code()],
    'sequence at the end' => [3, '!xyz', Errors::PasswordSequenceTooLong->code()],
]);

it('validates whitespace', function () {
    $this->rule->value('1 2');
    expect($this->invoker->validateWhitespace())->toBe(Errors::PasswordContainsWhitespace->code());

    $this->rule->allowWhitespace();
    expect($this->invoker->validateWhitespace())->toBe(0);
});

it('rejects trailing newlines', function () {
    $this->rule->value("password\n");

    expect($this->invoker->validateWhitespace())->toBe(Errors::PasswordContainsWhitespace->code());
});

class TestPassword extends Password
{
    public function value(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
