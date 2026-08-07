<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pin\Database\QueryMonitor;
use Pin\Http\Middleware\LogApiResponse;

it('builds log context', function () {
    config(['pin.logging.response.include_request_payload' => false]);

    $middleware = new LogApiResponse(app(QueryMonitor::class));
    $request = Request::create('/test', 'POST', [
        'username' => 'admin',
        'password' => '123456',
    ]);

    $response = new JsonResponse([
        'code' => 0,
        'message' => 'ok',
        'data' => ['foo' => 'bar'],
    ]);

    $middleware->terminate($request, $response);
    $invoker = $this->invoker($middleware);

    expect($invoker->buildLogContext())->not->toHaveKey('payload');

    config(['pin.logging.response.include_request_payload' => true]);
    expect($invoker->buildLogContext()['payload']['username'])->toBe('admin');
});

it('resolves slow threshold', function () {
    $middleware = new LogApiResponse(app(QueryMonitor::class));

    $invoker = $this->invoker($middleware);
    foreach ([
        1000 => 1000,
        1 => 1000,
    ] as $time => $expected) {
        config(['pin.logging.response.slow_threshold' => $time]);

        expect($invoker->slowThreshold())->toBe($expected);
    }
});
