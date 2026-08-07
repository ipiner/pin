<?php

declare(strict_types=1);

use Pin\Errors\Errors;
use Pin\Exceptions\ValidationException;

it('retrieves caller information', function () {
    $p = Illuminate\Validation\ValidationException::withMessages([]);
    $e = new ValidationException($p);

    $caller = $e->getCaller();

    expect($caller['file'])->toBe($p->getFile())
        ->and($caller['line'])->toBe($p->getLine());
});

it('parses code and message', function () {
    expect(ValidationException::resolveCodeMessage((string) Errors::Failed->code()))
        ->toBe([Errors::Failed->code(), Errors::Failed->message()])
        ->and(ValidationException::resolveCodeMessage('invalid username'))
        ->toBe([Errors::ValidationFailed->code(), 'invalid username'])
        ->and(ValidationException::resolveCodeMessage('username| invalid username'))
        ->toBe([Errors::ValidationFailed->code(), 'username| invalid username'])
        ->and(ValidationException::resolveCodeMessage('1| invalid username'))
        ->toBe([1, 'invalid username']);
});

it('returns validation errors', function () {
    $e = new ValidationException(
        Illuminate\Validation\ValidationException::withMessages(['username' => 'invalid username'])
    );

    expect($e->getErrors())->toBe(['username' => ['invalid username']]);
});

it('initializes validation exception', function () {
    $e = new ValidationException(
        Illuminate\Validation\ValidationException::withMessages(['username' => 'invalid username'])
    );

    expect($e->getStatusCode())->toBe(422)
        ->and($e->getCode())->toBe(Errors::ValidationFailed->code())
        ->and($e->getMessage())->toBe('invalid username')
        ->and($e->toResponse($this->app->request)->getStatusCode())->toBe(422);
});
