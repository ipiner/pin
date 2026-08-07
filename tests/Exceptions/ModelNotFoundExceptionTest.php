<?php

declare(strict_types=1);

use App\Models\User;
use Pin\Errors\Errors;
use Pin\Exceptions\ModelNotFoundException;

beforeEach(function () {
    $this->e = new ModelNotFoundException(
        (new Illuminate\Database\Eloquent\ModelNotFoundException())
            ->setModel(User::class, 1)
    );
});

it('gets caller information', function () {
    $caller = $this->e->getCaller();

    expect($caller['file'])->not()->toBe(__FILE__);
});

it('initializes model not found exception', function () {
    expect($this->e->getStatusCode())->toBe(404)
        ->and($this->e->getCode())->toBe(Errors::ModelNotFound->code())
        ->and($this->e->getMessage())->toBe('User not found')
        ->and($this->e->getContext()['message'])->toContain(User::class);
});

it('resolves model labels', function () {
    expect($this->invoker($this->e)->modelLabel(User::class))
        ->toBe('User')
        ->and($this->invoker($this->e)->modelLabel('UserAddress'))
        ->toBe('User Address');
});
