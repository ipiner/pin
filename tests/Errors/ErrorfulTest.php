<?php

declare(strict_types=1);

use Pin\Errors\Errors;
use Pin\Errors\IError;
use Pin\Errors\Registry;
use Pin\Tests\Errors\DisabledGroupErrors;
use Pin\Tests\Errors\Errors as TestsErrors;
use Pin\Tests\Errors\NoGroupErrors;
use Pin\Tests\Errors\OverrideErrors;
use Pin\Tests\Errors\UserGroupErrors;

class_exists(TestsErrors::class); // 引入错误定义

beforeEach(function () {
    $this->registeredErrors = Registry::all();
});

afterEach(function () {
    $this->invoker(Registry::class)->errors = $this->registeredErrors;
});

it('creates exceptions', function () {
    $e = Errors::ServerError->exception();

    expect($e->getCode())->toBe(500)
        ->and($e->getMessage())->toBe(Errors::ServerError->message())
        ->and($e->getStatusCode())->toBe(500);

    $e = Errors::ServerError
        ->exception('error', 501)
        ->withStatusCode(200);

    expect($e->getCode())->toBe(501)
        ->and($e->getMessage())->toBe('error')
        ->and($e->getStatusCode())->toBe(200);
});

it('throws exception', function () {
    expect(fn () => Errors::ServerError->throw('error'))
        ->toThrow(Exception::class, 'error');
});

it('returns translation group', function (IError $error, string|false $expected) {

    expect($this->invoker($error)->translationGroup())
        ->toBe($expected);
})->with([
    'group value' => [
        UserGroupErrors::Test,
        'user',
    ],
    'disabled from case' => [
        UserGroupErrors::DisabledFromCase,
        false,
    ],
    'group from case' => [
        UserGroupErrors::GroupFromCase,
        'errors',
    ],
    'group disabled' => [
        DisabledGroupErrors::Test,
        false,
    ],
    'no group' => [
        NoGroupErrors::Test,
        '',
    ],
]);

it('replaces placeholders when translation is disabled', function (IError $error) {
    expect($error->message(['name' => 'Alice']))->toBe('Denied Alice');
})->with([
    DisabledGroupErrors::WithReplacement,
    UserGroupErrors::WithReplacement,
]);

it('uses the translation group of an overridden error', function () {
    Errors::ServerError->message();
    trans()->addLines(['custom_errors.server_error' => 'Custom :name'], 'en');
    Registry::register([OverrideErrors::ServerError]);

    expect(Errors::ServerError->message(['name' => 'Alice']))->toBe('Custom Alice')
        ->and(Errors::ServerError->code())->toBe(500)
        ->and(Errors::ServerError->statusCode())->toBe(503);

    $previous = new RuntimeException('Previous error');
    $exception = Errors::ServerError->exception(previous: $previous);

    expect($exception->getMessage())->toBe('Custom :name')
        ->and($exception->getCode())->toBe(500)
        ->and($exception->getStatusCode())->toBe(503)
        ->and($exception->getPrevious())->toBe($previous);
});

it('honors disabled translation on an overridden error', function () {
    Registry::register([OverrideErrors::Forbidden]);

    expect(Errors::Forbidden->message(['name' => 'Alice']))->toBe('Denied Alice')
        ->and(Errors::Forbidden->statusCode())->toBe(409);
});

it('uses the latest registered error after earlier lookups', function () {
    Registry::register([OverrideErrors::Forbidden]);
    Errors::Forbidden->message();
    Registry::register([Errors::Forbidden]);

    expect(OverrideErrors::Forbidden->message())->toBe(Errors::Forbidden->message())
        ->and(OverrideErrors::Forbidden->statusCode())->toBe(403);
});
