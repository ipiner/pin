<?php

declare(strict_types=1);

use Pin\Support\Traits\HasContext;

it('can set and retrieve context using key and value', function () {
    $action = new class
    {
        use HasContext;
    };

    expect($action->context('id', 1)->context()->toArray())
        ->toBe(['id' => 1]);
});

it('can set multiple values at once', function () {
    $action = new class
    {
        use HasContext;
    };

    expect($action->context(['id' => 2, 'name' => 'foo'])->context()->toArray())
        ->toBe(['id' => 2, 'name' => 'foo']);
});

it('can replace context entirely', function () {
    $action = new class
    {
        use HasContext;
    };

    expect($action->context(null, ['id' => 1])->context()->toArray())
        ->toBe(['id' => 1]);
});

it('can retrieve nested context values by key', function () {
    $action = new class
    {
        use HasContext;
    };

    $action->context(['user' => ['id' => 1]]);
    expect($action->context('user.id'))->toBe(1);
});
