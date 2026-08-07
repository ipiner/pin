<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Pin\Http\ApiResponse;

it('matches api response', function ($response, bool $expected) {
    expect(ApiResponse::matches($response))->toBe($expected);
})->with([
    'array' => [
        [
            'code' => 0,
            'message' => 'success',
            'data' => null,
        ],
        true,
    ],

    'json response' => [
        new JsonResponse([
            'code' => 0,
            'message' => 'success',
            'data' => [],
        ]),
        true,
    ],

    'missing code' => [
        [
            'message' => 'success',
            'data' => null,
        ],
        false,
    ],

    'missing message' => [
        [
            'code' => 0,
            'data' => null,
        ],
        false,
    ],

    'missing data' => [
        [
            'code' => 0,
            'message' => 'success',
        ],
        false,
    ],

    'string code' => [
        [
            'code' => '0',
            'message' => 'success',
            'data' => null,
        ],
        false,
    ],
    'invalid data' => [
        'string',
        false,
    ],
]);

it('makes a JsonpResponse', function () {
    expect(JsonpResponse::make(0))->toBeInstanceOf(JsonpResponse::class);
});

it('applies status code and headers', function () {
    $resp = ApiResponse::make()
        ->withStatusCode(404)
        ->withHeaders(['foo' => 'foo'])
        ->withHeaders('bar', 'bar')
        ->toResponse(request());

    expect($resp->getStatusCode())->toBe(404)
        ->and($resp->headers->get('foo'))->toBe('foo')
        ->and($resp->headers->get('bar'))->toBe('bar');
});

it('converts to array', function () {
    $result = ApiResponse::make(message: 'Success');

    expect($result->toArray()['code'])->toBe(0)
        ->and($result->toArray()['message'])->toBe('Success');
});

it('encodes to json', function () {
    $result = ApiResponse::make(message: 'Success');
    $result = json_decode(json_encode($result));

    expect($result)
        ->code->toBe(0)
        ->message->toBe('Success');
});

it('converts to response with debug info', function () {
    $response = ApiResponse::make(message: 'Success');

    expect($response->toResponse(request())->getData()->message)
        ->toBe('Success');

    expect(
        $response->withStatusCode(404)->toResponse(request())->status()
    )->toBe(404);

    $data = $response->toResponse(request())->getOriginalContent();
    expect(array_key_exists('meta', $data))->toBeFalse()
        ->and(array_key_exists('debug', $data))->toBeFalse();

    config(['app.debug' => true]);
    $response = ApiResponse::make(message: 'Success', meta: ['s' => true]);
    $data = $response->toResponse(request())->getOriginalContent();

    expect(array_key_exists('meta', $data))->toBeTrue()
        ->and(array_key_exists('debug', $data))->toBeTrue();
});

class JsonpResponse extends ApiResponse
{
}
