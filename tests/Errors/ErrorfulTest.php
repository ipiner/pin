<?php

declare(strict_types=1);

use Pin\Errors\Errors;
use Pin\Errors\IError;
use Pin\Tests\Errors\DisabledGroupErrors;
use Pin\Tests\Errors\Errors as TestsErrors;
use Pin\Tests\Errors\NoGroupErrors;
use Pin\Tests\Errors\UserGroupErrors;

class_exists(TestsErrors::class); // 引入错误定义

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

    expect($this->invoker($error)->translationGroup($error))
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
