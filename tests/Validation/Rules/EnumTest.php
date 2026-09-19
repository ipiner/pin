<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use Pin\Errors\Errors;
use Pin\Models\Cache\CacheType;
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

it('rejects invalid enum input through the validation callback', function (mixed $value) {
    $errors = [];

    (new Enum(Errors::class))->validate('status', $value, function ($message) use (&$errors) {
        $errors[] = $message;
    });

    expect($errors)->toBe([__('validation.enum')]);
})->with([
    'null' => [null],
    'boolean' => [false],
    'integer' => [1],
    'float' => [1.0],
    'array' => [['0|success']],
    'object' => [new stdClass()],
    'enum case' => [Errors::Success],
]);

it('validates integer enum values without coercion', function (mixed $value, bool $valid) {
    $errors = [];

    (new Enum(CacheType::class))->validate('cache', $value, function ($message) use (&$errors) {
        $errors[] = $message;
    });

    expect($errors)->toBe($valid ? [] : [__('validation.enum')]);
})->with([
    'zero' => [0, true],
    'integer' => [1, true],
    'unknown value' => [999, false],
    'numeric string' => ['1', false],
    'float' => [1.0, false],
    'boolean' => [true, false],
]);

it('uses a custom message for invalid enum input', function () {
    $errors = [];
    $rule = new Enum(Errors::class)->message('Invalid status.');

    $rule->validate('status', [], function ($message) use (&$errors) {
        $errors[] = $message;
    });

    expect($errors)->toBe(['Invalid status.']);
});

it('returns field errors when used by the validator', function () {
    $rule = new Enum(CacheType::class);
    $validator = Validator::make([
        'valid' => 0,
        'invalid' => ['1'],
        'optional' => null,
    ], [
        'valid' => [$rule],
        'invalid' => [$rule],
        'optional' => ['nullable', $rule],
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toBe(['invalid'])
        ->and($validator->errors()->first('invalid'))
        ->toBe(__('validation.enum', ['attribute' => 'invalid']));
});
