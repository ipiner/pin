<?php

use Illuminate\Support\Arr;
use Pin\Faker\FakeRule;
use Pin\Faker\ValueResolver;

beforeEach(function () {
    $this->resolver = app(ValueResolver::class);
});

it('resolves closure generators', function () {
    $rule = new FakeRule(
        fn ($rules, int $min, int $max) => Arr::random([$min, $max]),
        [1, 2]
    );
    $value = $this->resolver->resolve($rule);

    expect($value === 1 || $value === 2)->toBeTrue();
});

it('resolves builtin generators', function () {
    $rule = new FakeRule('integer', [1, 2]);
    $value = $this->resolver->resolve($rule);

    expect($value === 1 || $value === 2)->toBeTrue($value);
});

it('resolves faker generators', function () {
    $rule = new FakeRule('numberBetween', [1, 2]);
    $value = $this->resolver->resolve($rule);

    expect($value === 1 || $value === 2)->toBeTrue();
});

it('throws exception for invalid faker generator', function () {
    $rule = new FakeRule('INVILID', [1, 2]);

    expect(fn () => $this->resolver->resolve($rule))
        ->toThrow(Exception::class, 'INVILID');
});
