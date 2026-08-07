<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Pin\Http\ApiResponse;
use Pin\Http\Middleware\LogApiResponse;

it('extracts response data from request attribute', function () {
    $middleware = app(LogApiResponse::class);

    $this->app['request']->attributes->set(
        LogApiResponse::API_RESPONSE,
        new JsonResponse([
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'id' => 1,
            ],
        ])
    );

    $middleware->terminate(
        $this->app['request'],
        ApiResponse::make()->toResponse($this->app['request'])
    );

    expect($this->invoker($middleware)->extractResponseData())
        ->toBe([
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'id' => 1,
            ],
        ]);
});

it('extracts response data', function () {
    $middleware = app(LogApiResponse::class);
    $middleware->terminate(
        $this->app['request'],
        new JsonResponse([
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'id' => 1,
            ],
        ]),
    );

    expect($this->invoker($middleware)->extractResponseData())
        ->toBe([
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'id' => 1,
            ],
        ]);
});

it('returns null when response data cannot be extracted', function () {
    $middleware = app(LogApiResponse::class);
    $middleware->terminate(
        $this->app['request'],
        response('hello')
    );

    expect($this->invoker($middleware)->extractResponseData())->toBeNull();
});

it('truncates response data when max length is exceeded', function () {
    config()->set('pin.logging.response.max_length', 10);

    $data = [
        'content' => str_repeat('a', 100),
    ];

    $middleware = app(LogApiResponse::class);

    $result = $this->invoker($middleware)
        ->truncateResponseData($data);

    expect($result)->toBeString()
        ->and($result)->toContain('(...');
});
