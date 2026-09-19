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

it('allows overriding validation caller information', function () {
    $previous = Illuminate\Validation\ValidationException::withMessages([]);
    $exception = new ValidationException($previous);

    expect($exception->getCaller('custom.php', 123))
        ->toBe(['file' => 'custom.php', 'line' => 123])
        ->and($exception->getCaller(file: 'custom.php'))
        ->toBe(['file' => 'custom.php', 'line' => $previous->getLine()]);
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

it('returns normalized validation messages', function () {
    $previous = Illuminate\Validation\ValidationException::withMessages([
        'username' => ['1|Invalid username', '422', 'text|detail'],
        'password' => [' 1031 | Invalid password | detail '],
    ]);
    $exception = new ValidationException($previous);
    $response = $exception->toResponse($this->app['request']);

    expect($response->getData(true)['data']['errors'])->toBe([
        'username' => ['Invalid username', Errors::ValidationFailed->message(), 'text|detail'],
        'password' => ['Invalid password | detail'],
    ])
        ->and($response->getData(true)['code'])->toBe(1)
        ->and($response->getData(true)['message'])->toBe('Invalid username')
        ->and($exception->getPrevious())->toBe($previous);
});

it('applies validation response overrides', function () {
    $exception = new ValidationException(
        Illuminate\Validation\ValidationException::withMessages(['name' => 'Name is required'])
    );
    $exception->withResponseMessage('Check the fields')
        ->withStatusCode(400)
        ->withHeaders(['X-Validation' => 'failed']);

    $response = $exception->toResponse($this->app['request']);

    expect($response->getData(true)['message'])->toBe('Check the fields')
        ->and($response->getStatusCode())->toBe(400)
        ->and($response->headers->get('X-Validation'))->toBe('failed');
});
