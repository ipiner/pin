<?php

declare(strict_types=1);

use Pin\Models\Queryable\QueryableType;
use Pin\Validation\QueryableRules;
use Pin\Validation\Rules\Queryable;

it('builds base queryable validation rules', function (): void {
    $queryableRule = QueryableType::Eq->asRule();

    expect($this->invoker(QueryableRules::class)->rule($queryableRule, 'string', 'max:64'))
        ->toBe([
            'nullable',
            'string',
            'max:64',
            $queryableRule,
        ]);
});

it('builds default string queryable rule', function (): void {
    $rules = $this->invoker(QueryableRules::class)->string();

    expect($rules)
        ->toHaveCount(3)
        ->and($rules[0])->toBe('nullable')
        ->and($rules[1])->toBe('string')
        ->and($rules[2])->toBeInstanceOf(Queryable::class)
        ->and($rules[2])->toEqual(QueryableType::Like->asRule());
});

it('builds default numeric queryable rule', function (): void {
    $rules = $this->invoker(QueryableRules::class)->number();

    expect($rules)
        ->toHaveCount(3)
        ->and($rules[0])->toBe('nullable')
        ->and($rules[1])->toBe('numeric')
        ->and($rules[2])->toBeInstanceOf(Queryable::class)
        ->and($rules[2])->toEqual(QueryableType::EqNumeric->asRule());
});

it('appends custom validation rules before queryable metadata', function (): void {
    $rules = QueryableRules::like('max:128');

    expect($rules)
        ->toHaveCount(4)
        ->and($rules[0])->toBe('nullable')
        ->and($rules[1])->toBe('string')
        ->and($rules[2])->toBe('max:128')
        ->and($rules[3])->toEqual(QueryableType::Like->asRule());
});

it('builds string query operators', function (string $method, QueryableType $type): void {
    $rules = QueryableRules::{$method}();

    expect($rules)
        ->toHaveCount(3)
        ->and($rules[0])->toBe('nullable')
        ->and($rules[1])->toBe('string')
        ->and($rules[2])->toBeInstanceOf(Queryable::class)
        ->and($rules[2])->toEqual($type->asRule());
})->with([
    'ends with' => ['endsWith', QueryableType::EndsWith],
    'equals' => ['eq', QueryableType::Eq],
    'greater than' => ['gt', QueryableType::Gt],
    'greater than or equals' => ['gte', QueryableType::Gte],
    'like' => ['like', QueryableType::Like],
    'less than' => ['lt', QueryableType::Lt],
    'less than or equals' => ['lte', QueryableType::Lte],
    'range' => ['range', QueryableType::Range],
    'range numeric' => ['rangeNumeric', QueryableType::RangeNumeric],
    'starts with' => ['startsWith', QueryableType::StartsWith],
]);

it('builds numeric query operators', function (string $method, QueryableType $type): void {
    $rules = QueryableRules::{$method}();

    expect($rules)
        ->toHaveCount(3)
        ->and($rules[0])->toBe('nullable')
        ->and($rules[1])->toBe('numeric')
        ->and($rules[2])->toBeInstanceOf(Queryable::class)
        ->and($rules[2])->toEqual($type->asRule());
})->with([
    'equals' => ['eqNumeric', QueryableType::EqNumeric],
    'greater than' => ['gtNumeric', QueryableType::GtNumeric],
    'greater than or equals' => ['gteNumeric', QueryableType::GteNumeric],
    'less than' => ['ltNumeric', QueryableType::LtNumeric],
    'less than or equals' => ['lteNumeric', QueryableType::LteNumeric],
]);

it('builds string in query rule', function (): void {
    $rules = QueryableRules::in();

    expect($rules)
        ->toHaveCount(3)
        ->and($rules[0])->toBe('nullable')
        ->and($rules[1])->toBe('array')
        ->and($rules[2])->toEqual(QueryableType::In->asRule());
});

it('builds numeric in query rule', function (): void {
    $rules = QueryableRules::inNumeric();

    expect($rules)
        ->toHaveCount(3)
        ->and($rules[0])->toBe('nullable')
        ->and($rules[1])->toBe('array')
        ->and($rules[2])->toEqual(QueryableType::InNumeric->asRule());
});

it('allows overriding in query value type', function (): void {
    $rules = QueryableRules::in('string', 'max:255');

    expect($rules)
        ->toHaveCount(4)
        ->and($rules[0])->toBe('nullable')
        ->and($rules[1])->toBe('string')
        ->and($rules[2])->toBe('max:255')
        ->and($rules[3])->toEqual(QueryableType::In->asRule());
});

it('builds smart search query rule with fields', function (): void {
    $rules = QueryableRules::ns('id,name');

    expect($rules)
        ->toHaveCount(3)
        ->and($rules[0])->toBe('nullable')
        ->and($rules[1])->toBe('string')
        ->and($rules[2])->toEqual(QueryableType::Ns->asRule('id,name'));
});
