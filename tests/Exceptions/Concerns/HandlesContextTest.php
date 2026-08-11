<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Pin\Exceptions\Exception;
use Pin\Exceptions\Handler;

beforeEach(function () {
    $this->handler = new Handler($this->app);
    $this->invoker = $this->invoker($this->handler);
});

it('resolves caller', function () {
    $caller = $this->invoker->resolveCaller(new RuntimeException());

    expect($caller)->toHaveCount(2);
});

it('builds exception context', function () {
    $context = $this->invoker->buildExceptionContext(new Exception());

    expect($context)->toHaveKey('file')
        ->and($context)->toHaveKey('line')
        ->and($context)->not()->toHaveKey('post')
        ->and($context)->not()->toHaveKey('foo');

    app()->instance('request', Request::create('/', 'POST', ['foo' => 'bar']));
    $context = $this->invoker->buildExceptionContext(
        (new Exception())->withContext(['foo' => 'bar'])
    );

    expect($context)->toHaveKey('post')
        ->and($context)->toHaveKey('foo');
});
