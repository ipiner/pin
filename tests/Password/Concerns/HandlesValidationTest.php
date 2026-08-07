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
