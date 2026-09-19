<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Pin\Http\ApiResponse;
use Pin\Http\Middleware\LogApiResponse;
use Pin\Support\Str;

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

it('skips scalar JSON responses', function (mixed $data) {
    $logger = Log::getFacadeRoot();
    $middleware = app(LogApiResponse::class);

    try {
        Log::shouldReceive('channel')->never();
        $middleware->terminate($this->app['request'], new JsonResponse($data));
    } finally {
        Log::swap($logger);
    }

    expect($this->invoker($middleware)->extractResponseData())->toBeNull();
})->with(['text', 0, false]);

it('skips masking ignored response data', function () {
    config(['pin.logging.response.ignore_response_data' => ['*']]);
    $response = new JsonResponse([
        'code' => 0,
        'message' => 'ok',
        'data' => ['large' => str_repeat('a', 10000)],
    ]);
    $middleware = app(LogApiResponse::class);
    $middleware->terminate($this->app['request'], $response);

    Str::setSensitiveValueMasker(function ($value, $key) {
        if ($key === 'large') {
            throw new LogicException('Ignored data should not be processed.');
        }

        return $key === 'message' ? 'masked' : $value;
    });

    try {
        expect($this->invoker($middleware)->normalizeResponse())->toBe([
            'code' => 0,
            'message' => 'masked',
            'data' => '...',
        ]);
    } finally {
        Str::setSensitiveValueMasker(null);
    }
});

it('truncates response data by character length', function (int $maxLength, mixed $expected) {
    config(['pin.logging.response.max_length' => $maxLength]);

    expect($this->invoker(app(LogApiResponse::class))->truncateResponseData(['a' => '中文']))
        ->toBe($expected);
})->with([
    [10, ['a' => '中文']],
    [8, '{"a":"中文(...2)'],
]);
