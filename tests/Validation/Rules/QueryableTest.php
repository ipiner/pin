<?php

declare(strict_types=1);

use Pin\Models\Queryable\QueryableType;
use Pin\Validation\Rules\Queryable;

it('accepts string operator', function () {
    $rule = new Queryable('eq');

    expect($rule->type)->toBe('eq');
});

it('accepts enum operator', function () {
    $rule = new Queryable(QueryableType::Eq);

    expect($rule->type)
        ->toBe(QueryableType::Eq->value);
});

it('can be converted to string', function () {
    $rule = new Queryable('like');

    expect((string) $rule)
        ->toBe('q:like');
});

it('can be converted to string from enum', function () {
    $rule = new Queryable(QueryableType::Like);

    expect((string) $rule)
        ->toBe('q:'.QueryableType::Like->value);
});

it('does not perform validation', function () {
    $rule = new Queryable('eq');

    $failed = false;

    $rule->validate('name', 'Taylor', function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();
});
