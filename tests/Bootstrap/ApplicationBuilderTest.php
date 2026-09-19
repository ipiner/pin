<?php

declare(strict_types=1);

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
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

it('preserves explicitly configured routes when creating the application', function () {
    $routes = fn () => null;

    $app = $this->builder->withRouting(using: $routes)->create();

    expect($this->invoker(RouteServiceProvider::class)->alwaysLoadRoutesUsing)->toBe($routes)
        ->and($this->builder->create())->toBe($app)
        ->and($this->invoker(RouteServiceProvider::class)->alwaysLoadRoutesUsing)->toBe($routes);
});

it('registers default routes only once', function () {
    $app = $this->builder->create();
    $routes = $this->invoker(RouteServiceProvider::class)->alwaysLoadRoutesUsing;

    expect($routes)->toBeInstanceOf(Closure::class)
        ->and($this->builder->create())->toBe($app)
        ->and($this->invoker(RouteServiceProvider::class)->alwaysLoadRoutesUsing)->toBe($routes);
});
