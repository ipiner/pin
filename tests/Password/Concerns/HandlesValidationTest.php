<?php

declare(strict_types=1);

use Pin\Errors\Errors;
use Pin\Password\PasswordRule;

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
    (new PasswordRule())->validate(
        'password',
        '123456',
        function ($message) use (&$errors) {
            $errors[] = $message;
        }
    );

    expect(str_starts_with($errors[0], Errors::PasswordTooShort->code().'|'))->toBeTrue();

    $errors = [];
    (new PasswordRule(false))->letters()->validate(
        'password',
        '123456',
        function ($message) use (&$errors) {
            $errors[] = $message;
        }
    );

    expect(str_starts_with($errors[0], Errors::PasswordTooShort->code().'|'))->toBeFalse();
});

it('clears previous errors when reusing a password rule', function () {
    $rule = new PasswordRule()->min(1)->letters();
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
    $rule = new PasswordRule()->min(1)->requiredCharacterTypes(2);
    $errors = [];
    $fail = function ($message) use (&$errors) {
        $errors[] = $message;
    };

    $rule->validate('password', '1a', $fail);
    expect($errors)->toBe([]);

    $rule->requiredCharacterTypes(1)->validate('password', 'word', $fail);
    expect($errors)->toBe([]);
});
