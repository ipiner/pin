<?php

declare(strict_types=1);

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Configuration\Middleware;
use Pin\Application;
use Pin\Exceptions\Handler;
use Pin\Http\Middleware\ThrottleRequestsWithRedis;

beforeEach(function () {
    $this->builder = Application::configure();
});

it('creates application instance', function () {
    expect($this->builder->create())->toBeInstanceOf(Application::class);

    $aliases = (app(Kernel::class)->getMiddlewareAliases());
    expect($aliases['throttle'])->toBe(ThrottleRequestsWithRedis::class);
});

describe('withExceptions', function () {
    it('registers default exception handler', function () {
        $this->builder->withExceptions(fn () => true);

        expect(app(ExceptionHandler::class))->toBeInstanceOf(Handler::class);
    });
    it('registers custom exception handler', function () {
        $this->builder->withExceptions(stdClass::class, fn () => true);

        expect(app(ExceptionHandler::class))->toBeInstanceOf(stdClass::class);
    });
});

it('configures middleware', function () {
    $middleware = null;

    $this->builder->withMiddleware(function (Middleware $config) use (&$middleware) {
        $middleware = $config;
    });

    $aliases = (app(Kernel::class)->getMiddlewareAliases());
    expect($aliases['throttle'])->toBe(ThrottleRequestsWithRedis::class);
});
