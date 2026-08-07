<?php

declare(strict_types=1);

use Pin\Errors\Errors;
use Pin\Exceptions\Exception;

it('retrieves the caller', function () {
    $caller = (new Exception())->getCaller();
    expect($caller['line'])->toBe(__LINE__ - 1)
        ->and($caller['file'])->toBe(__FILE__);

    $caller = (new Exception())->getCaller('file', 123);
    expect($caller['line'])->toBe(123)
        ->and('file')->toBe('file');
});

it('initializes exception', function () {
    $e = new Exception();

    expect($e->getMessage())->toBe(Errors::ServerError->message())
        ->and($e->getCode())->toBe(0)
        ->and($e->getStatusCode())->toBe(500)
        ->and($e->getReport())->toBeTrue();

    $e = new Exception(Errors::BadRequest);

    expect($e->getMessage())->toBe(Errors::BadRequest->message())
        ->and($e->getCode())->toBe(Errors::BadRequest->code())
        ->and($e->getStatusCode())->toBe(400)
        ->and($e->getReport())->toBeFalse();
});

it('applies with* methods', function () {
    $e = (new Exception())
        ->withStatusCode(401)
        ->withHeaders(['headers'])
        ->withLogLevel('debug')
        ->withContext(['name' => 'foo'])
        ->withReport()
        ->withResponseMessage('error')
        ->withReport();

    expect($e->getStatusCode())->toBe(401)
        ->and($e->getLogLevel())->toBe('debug')
        ->and($e->getHeaders())->toBe(['headers'])
        ->and($e->getResponseMessage())->toBe('error')
        ->and($e->getReport())->toBeTrue()
        ->and($e->getContext())->toBe(['name' => 'foo']);
});
