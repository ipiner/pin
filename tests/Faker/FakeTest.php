<?php

use Pin\Faker\Fake;
use Pin\Faker\FakeRule;
use Pin\Faker\InferManager;
use Pin\Faker\RuleBag;

beforeEach(function () {
    Fake::flushMacros();
});

it('generates fake data', function () {
    $rules = [
        'infer' => 'string',
        'missing' => 's',
        'id' => ['string', Fake::make('in', [100, 200])],
        'name' => 'string|fake:name',
        'status' => ['string', Fake::make(fn () => 1)],
    ];

    $data = Fake::generate($rules);

    expect(array_keys($data))->toBe(['infer', 'id', 'name', 'status'])
        ->and(strlen($data['infer']))->toBe(16)
        ->and($data['id'])->toBeGreaterThanOrEqual(100)->toBeLessThanOrEqual(200)
        ->and($data['status'])->toBe(1);
});

it('handles macros', function () {
    Fake::macro(
        'repeat',
        fn (string $char, int $repeats = 3) => Fake::make(
            fn ($rules) => str_repeat($char, $repeats),
        ),
    );

    $rules = [
        '3a' => ['string', Fake::repeat('a')],
        '4b' => ['string', Fake::repeat('b', 4)],
    ];

    $data = Fake::generate($rules);

    expect($data['3a'])->toBe('aaa')
        ->and($data['4b'])->toBe('bbbb');
});

it('registers and uses custom infer rules', function () {
    $inferManager = app(InferManager::class);

    expect($inferManager->infer(new RuleBag('date')))
        ->toBeNull();

    Fake::registerInfer(
        'date',
        fn () => Fake::make(
            fn (RuleBag $rules) => date($rules->parameter('format') ?? 'Y-m-d')
        )
    );

    expect($inferManager->infer(new RuleBag('date')))
        ->toBeInstanceOf(FakeRule::class)
        ->and(Fake::generate(['a' => 'date'])['a'])->toBe(date('Y-m-d'))
        ->and(Fake::generate(['date' => 'date|format:Y/m/d'])['date'])->toBe(date('Y/m/d'));
});

afterEach(function () {
    Fake::flushMacros();
});
