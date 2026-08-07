<?php

declare(strict_types=1);

use Pin\Errors\Errors;
use Pin\Validation\Rules\Enum;

it('validates enums', function () {
    $errors = 0;
    $rule = new Enum(Errors::class);
    $fail = function () use (&$errors) {
        $errors++;

        return fn () => $errors;
    };

    // valid enum
    $rule->validate('enum', '0|success', $fail);

    // invalid enums
    $rule->validate('enum', '0|Success', $fail);
    $rule->validate('enum', 'success', $fail);

    expect($errors)->toBe(2);
});
