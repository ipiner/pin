<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pin\Http\ApiResponse;
use Pin\Http\Middleware\LogApiResponse;

it('does not log when response is invalid', function () {
    $middleware = app(LogApiResponse::class);
    $middleware->terminate(
        $this->app['request'],
        new JsonResponse()
    );

    expect($this->invoker($middleware)->shouldLog())->toBeFalse();
});

it('does not log when route is excepted', function () {
    config()->set('pin.logging.response.except', ['api/health']);

    $middleware = app(LogApiResponse::class);
    $request = Request::create('/api/health');
    $middleware->terminate(
        $request,
        ApiResponse::make()->toResponse($request)
    );

    expect($this->invoker($middleware)->shouldLog())->toBeFalse();
});

it('logs when force log is enabled by config', function () {
    config()->set('pin.logging.response.enabled', true);

    $middleware = app(LogApiResponse::class);
    $request = Request::create('/api/health');

    $middleware->terminate(
        $request,
        ApiResponse::make()->toResponse($request)
    );

    expect($this->invoker($middleware)->shouldLog())->toBeTrue();
});

it('logs when response failed', function () {
    config()->set('pin.logging.response.enabled', false);

    $middleware = app(LogApiResponse::class);
    $request = Request::create('/api/health');

    $middleware->terminate(
        $request,
        ApiResponse::make(500)->toResponse($request)
    );

    expect($this->invoker($middleware)->shouldLog())->toBeTrue();
});
