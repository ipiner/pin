<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Pin\Database\QueryMonitor;
use Pin\Http\Middleware\LogApiResponse;
use Psr\Log\LogLevel;

beforeEach(function () {
    // 避免 slow
    config(['pin.logging.response.slow_threshold' => time()]);
});

it('logs failed responses', function () {
    Log::shouldReceive('channel->log')
        ->once()
        ->withArgs(function ($level, $message, $context) {
            return $level === LogLevel::INFO
                && $context['code'] === 500;
        });

    $middleware = makeMiddleware();
    $request = Request::create('/test');
    $response = new JsonResponse([
        'code' => 500,
        'message' => 'error',
        'data' => [],
    ]);

    $middleware->terminate($request, $response);
});

it('handles requests', function () {
    expect(
        makeMiddleware()->handle(
            $this->app['request'],
            fn () => true
        )
    )->toBeTrue();
});

it('does not log invalid responses', function () {
    Log::shouldReceive('channel->log')
        ->never()
        ->withArgs(function ($level, $message, $context) {
            return $level === LogLevel::DEBUG
                && $context['code'] === 0
                && $context['category'] === 'api';
        });

    $response = new Response('html content');
    $middleware = makeMiddleware();
    $middleware->terminate(
        $this->app['request'],
        $response
    );

    expect(true)->toBeTrue();
});

it('resolves log levels', function () {
    $invoker = $this->invoker(makeMiddleware());

    expect($invoker->resolveLogLevel([
        'status' => 500,
    ]))->toBe(LogLevel::ERROR)

        ->and($invoker->resolveLogLevel([
            'status' => 200,
            'success' => false,
        ]))->toBe(LogLevel::INFO)

        ->and($invoker->resolveLogLevel([
            'status' => 200,
            'success' => true,
            'slow' => true,
        ]))->toBe(LogLevel::NOTICE)

        ->and($invoker->resolveLogLevel([
            'status' => 200,
            'success' => true,
            'slow' => false,
        ]))->toBe(LogLevel::DEBUG);
});

it('logs successful responses', function () {
    Log::shouldReceive('channel->log')
        ->once()
        ->withArgs(function ($level, $message, $context) {
            return $level === LogLevel::DEBUG
                && $context['code'] === 0
                && $context['category'] === 'api';
        });

    $response = new JsonResponse([
        'code' => 0,
        'message' => 'ok',
        'data' => ['foo' => 'bar'],
    ]);

    makeMiddleware()->terminate(
        $this->app['request'],
        $response
    );

    expect(true)->toBeTrue();
});

it('handles exceptions during logging', function () {
    Log::shouldReceive('channel->error')
        ->once()
        ->withArgs(function ($message, $context) {
            return $message === 'LogApiResponse error'
                && $context['message'] === 'error';
        });

    $middleware = new class(app(QueryMonitor::class)) extends LogApiResponse
    {
        protected function shouldLog(): bool
        {
            throw new RuntimeException('error');
        }
    };

    $middleware->terminate(
        $this->app['request'],
        new Response('html content')
    );
});

function makeMiddleware(): LogApiResponse
{
    return new LogApiResponse(app(QueryMonitor::class));
}
